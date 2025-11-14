<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Stripe\Event;
use Stripe\StripeClient;

class WebhookController extends Controller
{
    protected StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('services.stripe.secret'));
    }

    /**
     * Handle Stripe webhook events.
     */
    public function handleWebhook(Request $request): Response
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('services.stripe.webhook_secret');

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
            $subscription->update([
                'stripe_status' => $stripeSubscription->status,
                'current_period_start' => $stripeSubscription->current_period_start,
                'current_period_end' => $stripeSubscription->current_period_end,
            ]);

            Log::info('Subscription updated: ' . $stripeSubscription->id);
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
            $subscription->update(['stripe_status' => 'canceled']);
            Log::info('Subscription canceled: ' . $stripeSubscription->id);
        }
    }

    /**
     * Handle invoice payment succeeded event.
     */
    private function handleInvoicePaymentSucceeded(Event $event): void
    {
        $stripeInvoice = $event->data->object;

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($invoice) {
            $invoice->markAsPaid();
            Log::info('Invoice paid: ' . $stripeInvoice->id);
        }
    }

    /**
     * Handle invoice payment failed event.
     */
    private function handleInvoicePaymentFailed(Event $event): void
    {
        $stripeInvoice = $event->data->object;

        $invoice = Invoice::where('stripe_invoice_id', $stripeInvoice->id)->first();

        if ($invoice) {
            $invoice->update(['status' => 'failed']);
            Log::info('Invoice payment failed: ' . $stripeInvoice->id);
        }
    }
}

