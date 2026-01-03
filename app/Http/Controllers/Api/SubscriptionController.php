<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\StripeService;
use App\Mail\SubscriptionConfirmation;
use App\Mail\SubscriptionCancellation;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SubscriptionController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        $this->middleware('auth:sanctum')->except(['getPublicPlans']);
    }

    /**
     * Get all available plans (public - no authentication required).
     * Returns all active plans for both user types.
     */
    public function getPublicPlans(): JsonResponse
    {
        $plans = Plan::where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
            'message' => 'All available plans',
        ]);
    }

    /**
     * Get all available plans filtered by user type (authenticated users).
     */
    public function getPlans(): JsonResponse
    {
        $user = auth()->user();
        $userType = $user->is_professional ? 'professional' : 'client';

        $plans = Plan::where('is_active', true)
            ->where('user_type', $userType)
            ->orderBy('sort_order')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $plans,
            'user_type' => $userType,
        ]);
    }

    /**
     * Get the appropriate free plan for the authenticated user.
     */
    public function getFreePlan(): JsonResponse
    {
        try {
            $user = auth()->user();
            $userType = $user->is_professional ? 'professional' : 'client';

            $freePlan = Plan::findFreePlanForUserType($userType);

            if (!$freePlan) {
                return response()->json([
                    'success' => false,
                    'message' => 'Aucun plan gratuit disponible pour ce type d\'utilisateur.',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $freePlan,
                'user_type' => $userType,
            ]);

        } catch (\Exception $e) {
            Log::error('Error getting free plan: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erreur lors de la récupération du plan gratuit.',
            ], 500);
        }
    }

    /**
     * Get user's current subscription.
     */
    public function getCurrentSubscription(): JsonResponse
    {
        $subscription = auth()->user()->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $subscription->load('plan', 'coupon'),
        ]);
    }

    /**
     * Get user's subscription history.
     */
    public function getSubscriptionHistory(): JsonResponse
    {
        $subscriptions = auth()->user()->subscriptions()
            ->with('plan', 'coupon')
            ->latest()
            ->get();

        return response()->json([
            'success' => true,
            'data' => $subscriptions,
        ]);
    }

    /**
     * Create a new subscription with email notification.
     */
    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'coupon_code' => 'nullable|string',
            'billing_period' => 'nullable|string|in:monthly,yearly',
            'payment_method_id' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $plan = Plan::findOrFail($validated['plan_id']);
            $billingPeriod = $validated['billing_period'] ?? null;
            
            // Vérifier si l'utilisateur est déjà abonné à ce plan
            $existingSubscription = $user->subscriptions()
                ->where('plan_id', $validated['plan_id'])
                ->whereIn('stripe_status', ['active', 'trialing'])
                ->first();
            
            if ($existingSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'Vous êtes déjà abonné à ce plan.',
                ], 400);
            }
            
            Log::info('Creating subscription for user', [
                'user_id' => $user->id,
                'plan_id' => $plan->id,
                'billing_period' => $billingPeriod,
            ]);

            // Créer l'abonnement Stripe
            $subscription = $this->stripeService->createSubscription(
                $user,
                $plan,
                $validated['coupon_code'] ?? null,
                $billingPeriod,
                $validated['payment_method_id'] ?? null
            );

            // Récupérer l'abonnement avec toutes les relations
            $subscription->load('plan', 'user');

            // ENVOYER LES EMAILS EN ARRIÈRE-PLAN (QUEUE)

            // 1. Email de confirmation d'abonnement (uniquement si actif ou en essai)
            if (in_array($subscription->stripe_status, ['active', 'trialing'])) {
                $this->sendSubscriptionConfirmationEmail($user, $subscription);
            }

            // 2. L'email de facture sera envoyé automatiquement via le webhook Stripe
            //    "invoice.payment_succeeded" (voir WebhookController).

            Log::info('Subscription created and emails queued', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            $isActionRequired = $subscription->getAttribute('latest_payment_intent_client_secret') !== null;
            $message = $isActionRequired 
                ? 'Subscription initiated. Additional action required to complete payment.' 
                : 'Subscription created successfully. Confirmation email sent.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $subscription,
                'action_required' => $isActionRequired,
            ], 201);
            
        } catch (\Exception $e) {
            Log::error('Error creating subscription: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'plan_id' => $validated['plan_id'] ?? null,
                'error_trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a subscription with email notification.
     */
    public function cancelSubscription(): JsonResponse
    {
        $user = auth()->user();
        $subscription = $user->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription to cancel',
            ], 404);
        }

        try {
            Log::info('Canceling subscription', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            // Annuler l'abonnement Stripe
            $this->stripeService->cancelSubscription($subscription);
            
            // Recharger les relations
            $subscription->load('plan', 'user');
            
            // ENVOYER L'EMAIL DE CONFIRMATION D'ANNULATION
            $this->sendCancellationEmail($user, $subscription);

            Log::info('Subscription canceled and email queued', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Subscription canceled successfully. Confirmation email sent.',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error canceling subscription: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id ?? null,
                'error_trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Resume a subscription.
     */
    public function resumeSubscription(): JsonResponse
    {
        $subscription = auth()->user()->subscriptions()
            ->where('stripe_status', 'canceled')
            ->latest()
            ->first();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No canceled subscription to resume',
            ], 404);
        }

        try {
            $this->stripeService->resumeSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription resumed successfully',
                'data' => $subscription->load('plan'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error resuming subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Change subscription to a different plan with email notification.
     */
    public function changeSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_period' => 'nullable|string|in:monthly,yearly',
            'payment_method_id' => 'nullable|string',
        ]);

        try {
            $user = auth()->user();
            $currentSubscription = $user->currentSubscription();

            if (!$currentSubscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription to change',
                ], 404);
            }

            $newPlan = Plan::findOrFail($validated['plan_id']);
            $billingPeriod = $validated['billing_period'] ?? null;

            // Check if trying to change to the same plan
            if ($currentSubscription->plan_id == $newPlan->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are already subscribed to this plan',
                ], 400);
            }

            Log::info('Changing subscription', [
                'current_subscription_id' => $currentSubscription->id,
                'new_plan_id' => $newPlan->id,
                'user_id' => $user->id,
            ]);

            // Changer l'abonnement Stripe
            $subscription = $this->stripeService->changeSubscription(
                $currentSubscription,
                $newPlan,
                $billingPeriod,
                $validated['payment_method_id'] ?? null
            );

            // Recharger les relations
            $subscription->load('plan', 'user');

            // La facture de proration et son email seront geres par le webhook Stripe
            // "invoice.payment_succeeded" une fois le paiement confirme.

            Log::info('Subscription changed, waiting for invoice webhook', [
                'subscription_id' => $subscription->id,
                'user_id' => $user->id,
            ]);

            $isActionRequired = $subscription->getAttribute('latest_payment_intent_client_secret') !== null;
            $message = $isActionRequired 
                ? 'Subscription change initiated. Additional action required to complete payment.' 
                : 'Subscription changed successfully. An invoice email will be sent once the payment is confirmed.';

            return response()->json([
                'success' => true,
                'message' => $message,
                'data' => $subscription,
                'action_required' => $isActionRequired,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error changing subscription: ' . $e->getMessage(), [
                'user_id' => auth()->id(),
                'plan_id' => $validated['plan_id'] ?? null,
                'error_trace' => $e->getTraceAsString(),
            ]);
            
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update subscription payment method.
     */
    public function updateSubscriptionPaymentMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'payment_method_id' => 'required|string',
        ]);

        try {
            $subscription = auth()->user()->currentSubscription();

            if (!$subscription) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active subscription found',
                ], 404);
            }

            $this->stripeService->updateSubscriptionPaymentMethod(
                $subscription,
                $validated['payment_method_id']
            );

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => $subscription->fresh()->load('plan'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating subscription payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Méthodes privées pour l'envoi d'emails
     */

    /**
     * Envoyer l'email de confirmation d'abonnement.
     */
    private function sendSubscriptionConfirmationEmail($user, $subscription): void
    {
        try {
            Mail::to($user->email)
	                ->send(new SubscriptionConfirmation($user, $subscription));
            
            Log::info('Subscription confirmation email queued', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'email' => $user->email,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to queue subscription confirmation email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Envoyer l'email de facture pour un abonnement.
     */
    private function sendInvoiceEmailForSubscription($user, $subscription, $type = 'new'): void
    {
        try {
            // Récupérer la facture Stripe la plus récente pour cet abonnement
            $invoice = $this->getLatestInvoiceForSubscription($subscription);
            
            if (!$invoice) {
                Log::warning('No invoice found for subscription', [
                    'subscription_id' => $subscription->id,
                    'type' => $type,
                ]);
                return;
            }
            
            // Générer le PDF de la facture
            $pdfPath = $this->generateInvoicePdf($invoice, $user, $subscription);
            
            // Envoyer l'email avec pièce jointe
            Mail::to($user->email)
	                ->send(new SubscriptionInvoice($user, $invoice, $subscription, $pdfPath));
            
            Log::info('Invoice email queued', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'invoice_id' => $invoice->id,
                'type' => $type,
                'pdf_path' => $pdfPath,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to queue invoice email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'type' => $type,
                'error_trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Envoyer l'email de confirmation d'annulation.
     */
    private function sendCancellationEmail($user, $subscription): void
    {
        try {
            Mail::to($user->email)
	                ->send(new SubscriptionCancellation($user, $subscription, now()));
            
            Log::info('Cancellation confirmation email queued', [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
                'email' => $user->email,
            ]);
            
        } catch (\Exception $e) {
            Log::error('Failed to queue cancellation email: ' . $e->getMessage(), [
                'user_id' => $user->id,
                'subscription_id' => $subscription->id,
            ]);
        }
    }

    /**
     * Récupérer la dernière facture pour un abonnement.
     */
    private function getLatestInvoiceForSubscription($subscription)
    {
        try {
            // Vous devrez créer ce modèle Invoice ou adapter selon votre structure
            if (class_exists('App\Models\Invoice')) {
                return \App\Models\Invoice::where('subscription_id', $subscription->id)
                    ->latest()
                    ->first();
            }
            
            // Fallback: créer une facture factice si le modèle n'existe pas
            return $this->createMockInvoice($subscription);
            
        } catch (\Exception $e) {
            Log::error('Failed to get latest invoice: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Créer une facture factice pour les tests (à remplacer par votre modèle réel).
     */
    private function createMockInvoice($subscription)
    {
        return (object) [
            'id' => uniqid(),
            'invoice_number' => 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6)),
            'amount' => $subscription->plan->price,
            'tax' => 0,
            'total' => $subscription->plan->price,
            'currency' => 'EUR',
            'status' => 'paid',
            'description' => $subscription->plan->title . ' - Abonnement',
            'created_at' => now(),
            'due_date' => now()->addDays(30),
        ];
    }

    /**
     * Générer le PDF de la facture.
     */
    private function generateInvoicePdf($invoice, $user, $subscription): string
    {
        try {
            // Créer le contenu HTML de la facture
            $html = view('pdf.invoice', [
                'invoice' => $invoice,
                'user' => $user,
                'subscription' => $subscription,
            ])->render();
            
            // Utiliser DomPDF pour générer le PDF
            $dompdf = new \Dompdf\Dompdf();
            $dompdf->loadHtml($html);
            $dompdf->setPaper('A4', 'portrait');
            $dompdf->render();
            
            // Chemin de sauvegarde
            $fileName = 'invoices/invoice-' . $invoice->invoice_number . '-' . time() . '.pdf';
            $filePath = storage_path('app/' . $fileName);
            
            // Créer le dossier si nécessaire
            $directory = storage_path('app/invoices');
            if (!file_exists($directory)) {
                mkdir($directory, 0775, true);
            }
            
            // Sauvegarder le PDF
            file_put_contents($filePath, $dompdf->output());
            
            return $fileName;
            
        } catch (\Exception $e) {
            Log::error('Failed to generate invoice PDF: ' . $e->getMessage());
            throw new \Exception('Failed to generate invoice PDF');
        }
    }
}