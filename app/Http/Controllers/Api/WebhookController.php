<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
    use App\Models\Subscription;
    use App\Models\Invoice;
    use App\Models\StripeConfiguration;
    use App\Models\User;
    use App\Notifications\InvoicePaidNotification;
    use App\Notifications\InvoicePaymentFailedNotification;
    use App\Services\InvoicePdfService;
    use App\Services\StripeService;
    use App\Mail\SubscriptionCancellation;
    use App\Models\BillingSetting;
    use Carbon\Carbon;
    use Illuminate\Http\Request;
    use Illuminate\Http\Response;
    use Illuminate\Support\Facades\Log;
    use Illuminate\Support\Facades\Mail;
use Stripe\Event;
use Stripe\StripeClient;

class WebhookController extends Controller
{
    protected StripeClient $stripe;
    protected InvoicePdfService $invoicePdfService;

    public function __construct(InvoicePdfService $invoicePdfService)
    {
        $secretKey = StripeConfiguration::getSecretKey() ?? config('services.stripe.secret');
        $this->stripe = new StripeClient($secretKey);
        $this->invoicePdfService = $invoicePdfService;
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = StripeConfiguration::getWebhookSecret() ?? config('services.stripe.webhook_secret');

        try {
            $event = Event::constructFrom(
                json_decode($payload, true)
            );
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid webhook payload: ' . $e->getMessage());
            return response('Invalid payload', 400);
        }

        // Verify signature
        try {
            \Stripe\Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid webhook signature: ' . $e->getMessage());
            return response('Invalid signature', 403);
        }

        // Handle different event types
        match ($event->type) {
            'customer.subscription.created' => $this->handleSubscriptionCreated($event),
            'customer.subscription.updated' => $this->handleSubscriptionUpdated($event),
            'customer.subscription.deleted' => $this->handleSubscriptionDeleted($event),
            'invoice.payment_succeeded' => $this->handleInvoicePaymentSucceeded($event),
            'invoice.payment_failed' => $this->handleInvoicePaymentFailed($event),
            default => Log::info('Unhandled webhook event: ' . $event->type),
        };

        return response('Webhook received', 200);
    }

    /**
     * Handle subscription created event.
     */
    private function handleSubscriptionCreated(Event $event): void
    {
        $stripeSubscription = $event->data->object;
        Log::info('Subscription created: ' . $stripeSubscription->id);
    }

    /**
     * Handle subscription updated event.
     */
    private function handleSubscriptionUpdated(Event $event): void
    {
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            $oldStatus = $subscription->stripe_status;
            
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);

            Log::info('Subscription updated: ' . $stripeSubscription->id);

            // Si l'abonnement passe de incomplete à active, envoyer l'email de confirmation
            if ($oldStatus === 'incomplete' && $stripeSubscription->status === 'active') {
                $user = $subscription->user;
                if ($user) {
                    try {
                        Mail::to($user->email)
                            ->queue(new \App\Mail\SubscriptionConfirmation($user, $subscription));
                        Log::info('Subscription confirmation email sent after successful 3DS', [
                            'user_id' => $user->id,
                            'subscription_id' => $subscription->id,
                        ]);
                    } catch (\Throwable $e) {
                        Log::error('Failed to send confirmation email after 3DS: ' . $e->getMessage());
                    }
                }
            }
        }
    }

    /**
     * Handle subscription deleted event.
     */
    private function handleSubscriptionDeleted(Event $event): void
    {
        $stripeSubscription = $event->data->object;

        $subscription = Subscription::where('stripe_subscription_id', $stripeSubscription->id)->first();

        if ($subscription) {
            // Déterminer la date de fin à partir des informations Stripe si possible
            if (!empty($stripeSubscription->ended_at)) {
                $endsAt = Carbon::createFromTimestamp($stripeSubscription->ended_at);
            } elseif (!empty($stripeSubscription->canceled_at)) {
                $endsAt = Carbon::createFromTimestamp($stripeSubscription->canceled_at);
            } else {
                $endsAt = now();
            }

            $subscription->update([
                'stripe_status' => 'canceled',
                'ends_at' => $endsAt,
            ]);

            Log::info('Subscription canceled from Stripe webhook', [
                'stripe_subscription_id' => $stripeSubscription->id ?? null,
                'subscription_id' => $subscription->id,
                'ends_at' => $endsAt,
            ]);

            // Envoyer l'email d'annulation à l'utilisateur
            $user = $subscription->user;

            if ($user) {
                try {
                    // Queue the cancellation email to avoid blocking the webhook handler
                    Mail::to($user->email)
                        ->queue(new SubscriptionCancellation($user, $subscription, $endsAt));

                    Log::info('Cancellation confirmation email queued from Stripe webhook', [
                        'user_id' => $user->id,
                        'subscription_id' => $subscription->id,
                        'email' => $user->email,
                    ]);
                } catch (\Throwable $e) {
                    Log::error('Failed to queue cancellation email from Stripe webhook', [
                        'subscription_id' => $subscription->id,
                        'user_id' => $user->id ?? null,
                        'error' => $e->getMessage(),
                    ]);
                }
            }
        } else {
            Log::warning('Subscription deleted webhook received but no local subscription found', [
                'stripe_subscription_id' => $stripeSubscription->id ?? null,
            ]);
        }
    }

    /**
     * Handle invoice payment succeeded event.
     */
    private function handleInvoicePaymentSucceeded(Event $event): void
    {
        $stripeInvoice = $event->data->object;

        [$user, $subscription] = $this->resolveInvoiceOwner($stripeInvoice);

        if (!$user) {
            Log::warning('invoice.payment_succeeded received but user could not be resolved', [
                'stripe_invoice_id' => $stripeInvoice->id ?? null,
                'customer' => $stripeInvoice->customer ?? null,
                'subscription' => $stripeInvoice->subscription ?? null,
            ]);

            return;
        }

        // Update customer info on Stripe with our latest billing settings
        try {
            $stripeService = app(StripeService::class);
            $stripeService->updateCustomerWithBillingSettings($user);
        } catch (\Throwable $e) {
            Log::error('Failed to update Stripe customer info on payment_succeeded: ' . $e->getMessage());
        }

        $attributes = $this->mapStripeInvoiceToAttributes($stripeInvoice, $user, $subscription);

        // Add current billing settings to invoice metadata for history
        $settings = BillingSetting::first();
        if ($settings) {
            $attributes['metadata'] = array_merge($attributes['metadata'] ?? [], [
                'billing_settings_at_time_of_payment' => $settings->toArray(),
            ]);
        }

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        	if ($invoice) {
	            $invoice->fill($attributes);
	            $invoice->markAsPaid();
	            $invoice->save();
	        } else {
	            $invoice = Invoice::create(array_merge($attributes, [
	                'invoice_number' => Invoice::generateInvoiceNumber(),
	                'status' => 'paid',
	                'paid_at' => now(),
	            ]));
	        }

	        // Generate internal PDF invoice (errors are logged but do not break the webhook)
	        try {
	            $this->invoicePdfService->generateAndStore($invoice);
	        } catch (\Throwable $e) {
	            Log::error('Failed to generate internal invoice PDF on payment_succeeded', [
	                'stripe_invoice_id' => $stripeInvoice->id ?? null,
	                'invoice_id' => $invoice->id ?? null,
	                'error' => $e->getMessage(),
	            ]);
	        }

        // Notify user by email
        $user->notify(new InvoicePaidNotification($invoice));

        Log::info('Invoice paid and recorded locally', [
            'stripe_invoice_id' => $stripeInvoice->id ?? null,
            'local_invoice_id' => $invoice->id,
        ]);
    }

    /**
     * Handle invoice payment failed event.
     */
    private function handleInvoicePaymentFailed(Event $event): void
    {
        $stripeInvoice = $event->data->object;
        
        // Vérifier si l'échec est dû à une action requise (3DS)
        $paymentIntentStatus = null;
        if (!empty($stripeInvoice->payment_intent)) {
            try {
                $paymentIntent = $this->stripe->paymentIntents->retrieve($stripeInvoice->payment_intent);
                $paymentIntentStatus = $paymentIntent->status;
            } catch (\Exception $e) {
                Log::error('Failed to retrieve payment intent for failed invoice: ' . $e->getMessage());
            }
        }

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        // Si 3DS est requis, on ne marque pas comme échec définitif et on n'envoie pas de mail d'erreur
        $is3DS = $paymentIntentStatus === 'requires_action';
        $status = $is3DS ? 'open' : 'failed';

        if (!$invoice) {
            [$user, $subscription] = $this->resolveInvoiceOwner($stripeInvoice);

            if ($user) {
                $attributes = $this->mapStripeInvoiceToAttributes($stripeInvoice, $user, $subscription);

                $invoice = Invoice::create(array_merge($attributes, [
                    'invoice_number' => Invoice::generateInvoiceNumber(),
                    'status' => $status,
                ]));
            }
        } else {
            $invoice->update(['status' => $status]);
        }

        // Envoyer la notification d'échec seulement si ce n'est pas un 3DS en attente
        if (!$is3DS && $invoice && $invoice->user) {
            $invoice->user->notify(new InvoicePaymentFailedNotification($invoice));
        }

        Log::info($is3DS ? 'Invoice payment requires action (3DS)' : 'Invoice payment failed handled', [
            'stripe_invoice_id' => $stripeInvoice->id ?? null,
            'local_invoice_id' => $invoice->id ?? null,
            'payment_intent_status' => $paymentIntentStatus,
        ]);
    }

    /**
     * Resolve the local User and Subscription associated with a Stripe invoice object.
     *
     * @param  object  $stripeInvoice
     * @return array{0: User|null, 1: Subscription|null}
     */
    private function resolveInvoiceOwner(object $stripeInvoice): array
    {
        $subscription = null;
        if (!empty($stripeInvoice->subscription)) {
            $subscription = Subscription::where('stripe_subscription_id', $stripeInvoice->subscription)->first();
        }

        $user = null;

        if ($subscription && $subscription->user) {
            $user = $subscription->user;
        } elseif (!empty($stripeInvoice->customer)) {
            $user = User::where('stripe_customer_id', $stripeInvoice->customer)->first();
        }

        return [$user, $subscription];
    }

    /**
     * Map a Stripe invoice object to local Invoice attributes.
     */
    private function mapStripeInvoiceToAttributes(object $stripeInvoice, User $user, ?Subscription $subscription): array
    {
        $amountBase = $stripeInvoice->subtotal
            ?? $stripeInvoice->amount_due
            ?? $stripeInvoice->amount_paid
            ?? $stripeInvoice->total
            ?? 0;

        $totalBase = $stripeInvoice->total
            ?? $stripeInvoice->amount_paid
            ?? $stripeInvoice->amount_due
            ?? $stripeInvoice->subtotal
            ?? 0;

        $amount = $this->convertStripeAmountToDecimal($amountBase);
        $total = $this->convertStripeAmountToDecimal($totalBase);

        $currency = $stripeInvoice->currency ?? config('subscription.currency', 'EUR');

        return [
            'user_id' => $user->id,
            'subscription_id' => $subscription?->id,
            'stripe_invoice_id' => $stripeInvoice->id,
            'amount' => $amount,
            'total' => $total,
            'tax' => 0,
            'discount' => 0,
            'currency' => strtoupper($currency),
            'description' => $stripeInvoice->description ?? null,
            'due_date' => isset($stripeInvoice->due_date)
                ? Carbon::createFromTimestamp($stripeInvoice->due_date)
                : null,
            'metadata' => [
                'stripe_customer_id' => $stripeInvoice->customer ?? null,
                'stripe_subscription_id' => $stripeInvoice->subscription ?? null,
                'stripe_invoice_number' => $stripeInvoice->number ?? null,
            ],
            'pdf_url' => $stripeInvoice->invoice_pdf ?? null,
        ];
    }

    /**
     * Convert an integer Stripe amount (in the smallest currency unit) to decimal.
     */
    private function convertStripeAmountToDecimal($amount): float
    {
        if ($amount === null) {
            return 0.0;
        }

        return ((float) $amount) / 100;
    }
}

