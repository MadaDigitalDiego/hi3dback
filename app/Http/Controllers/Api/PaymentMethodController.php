<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class PaymentMethodController extends Controller
{
    protected StripeService $stripeService;

    public function __construct(StripeService $stripeService)
    {
        $this->stripeService = $stripeService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get user's payment methods.
     */
    public function getPaymentMethods(): JsonResponse
    {
        try {
            $paymentMethods = auth()->user()->paymentMethods()
                ->where('is_deleted', false)
                ->get()
                ->map(function ($method) {
                    return [
                        'id' => $method->id,
                        'provider_id' => $method->stripe_payment_method_id, // IMPORTANT: stripe_payment_method_id comme provider_id
                        'stripe_payment_method_id' => $method->stripe_payment_method_id,
                        'type' => $method->type,
                        'brand' => $method->brand,
                        'last_four' => $method->last_four,
                        'exp_month' => $method->exp_month,
                        'exp_year' => $method->exp_year,
                        'is_default' => (bool) $method->is_default,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $paymentMethods,
                'message' => 'Payment methods retrieved successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching payment methods: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve payment methods',
            ], 500);
        }
    }

    /**
     * Add a new payment method.
     */
    public function addPaymentMethod(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'stripe_payment_method_id' => 'required|string',
            'is_default' => 'boolean',
        ]);

        try {
            $user = auth()->user();
            
            // Vérifier que l'ID commence bien par 'pm_'
            if (!str_starts_with($validated['stripe_payment_method_id'], 'pm_')) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid Stripe payment method ID format',
                ], 400);
            }
            
            // Vérifier si la méthode existe déjà
            $existingMethod = PaymentMethod::where('user_id', $user->id)
                ->where('stripe_payment_method_id', $validated['stripe_payment_method_id'])
                ->where('is_deleted', false)
                ->first();
                
            if ($existingMethod) {
                return response()->json([
                    'success' => false,
                    'message' => 'Payment method already exists',
                ], 400);
            }

            // Récupérer ou créer le customer Stripe
            $customerId = $this->stripeService->getOrCreateCustomer($user);

            // Attacher la méthode de paiement au customer
            $this->stripeService->attachPaymentMethod(
                $customerId,
                $validated['stripe_payment_method_id']
            );

            // Récupérer les détails depuis Stripe
            $stripePaymentMethod = $this->stripeService->getPaymentMethod($validated['stripe_payment_method_id']);

            // Extraire les détails de la carte
            $cardDetails = $stripePaymentMethod->card ?? null;
            
            if (!$cardDetails) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid card details',
                ], 400);
            }
            
            $brand = $cardDetails->brand ?? 'unknown';
            $lastFour = $cardDetails->last4 ?? null;
            $expMonth = $cardDetails->exp_month ?? null;
            $expYear = $cardDetails->exp_year ?? null;

            // Si c'est la première carte ou si demandé comme défaut
            $isFirstCard = !PaymentMethod::where('user_id', $user->id)
                ->where('is_deleted', false)
                ->exists();
                
            $shouldBeDefault = $validated['is_default'] ?? $isFirstCard;

            // Définir comme méthode de paiement par défaut dans Stripe si nécessaire
            if ($shouldBeDefault) {
                $this->stripeService->setDefaultPaymentMethod($customerId, $validated['stripe_payment_method_id']);
            }

            // Sauvegarder dans la base de données
            $paymentMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'stripe_payment_method_id' => $validated['stripe_payment_method_id'],
                'type' => 'card',
                'brand' => $brand,
                'last_four' => $lastFour,
                'exp_month' => $expMonth,
                'exp_year' => $expYear,
                'is_default' => $shouldBeDefault,
            ]);

            // Si c'est la méthode par défaut, désactiver les autres
            if ($shouldBeDefault) {
                PaymentMethod::where('user_id', $user->id)
                    ->where('id', '!=', $paymentMethod->id)
                    ->where('is_deleted', false)
                    ->update(['is_default' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'data' => [
                    'id' => $paymentMethod->id,
                    'provider_id' => $paymentMethod->stripe_payment_method_id,
                    'stripe_payment_method_id' => $paymentMethod->stripe_payment_method_id,
                    'brand' => $paymentMethod->brand,
                    'last_four' => $paymentMethod->last_four,
                    'exp_month' => $paymentMethod->exp_month,
                    'exp_year' => $paymentMethod->exp_year,
                    'is_default' => (bool) $paymentMethod->is_default,
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding payment method: ' . $e->getMessage());
            
            $errorMessage = 'Failed to add payment method';
            if (str_contains($e->getMessage(), 'No such payment_method')) {
                $errorMessage = 'Invalid payment method ID';
            } elseif (str_contains($e->getMessage(), 'already attached')) {
                $errorMessage = 'Payment method already attached to another customer';
            }
            
            return response()->json([
                'success' => false,
                'message' => $errorMessage,
                'debug' => env('APP_DEBUG') ? $e->getMessage() : null,
            ], 400);
        }
    }

    /**
     * Update a payment method.
     */
    public function updatePaymentMethod(Request $request, int $id): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('is_deleted', false)
                ->firstOrFail();

            // Vérifier l'autorisation
            if ($paymentMethod->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            $validated = $request->validate([
                'is_default' => 'boolean',
            ]);

            if ($validated['is_default'] ?? false) {
                // Désactiver les autres méthodes par défaut
                PaymentMethod::where('user_id', auth()->id())
                    ->where('id', '!=', $id)
                    ->where('is_deleted', false)
                    ->update(['is_default' => false]);

                // Définir comme défaut dans Stripe
                $user = auth()->user();
                $customerId = $this->stripeService->getOrCreateCustomer($user);
                $this->stripeService->setDefaultPaymentMethod(
                    $customerId, 
                    $paymentMethod->stripe_payment_method_id
                );
                
                $paymentMethod->update(['is_default' => true]);
            } else {
                $paymentMethod->update(['is_default' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method updated successfully',
                'data' => $paymentMethod,
            ]);
        } catch (\Exception $e) {
            Log::error('Error updating payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update payment method',
            ], 400);
        }
    }

    /**
     * Delete a payment method.
     */
    public function deletePaymentMethod(int $id): JsonResponse
    {
        try {
            $paymentMethod = PaymentMethod::where('id', $id)
                ->where('is_deleted', false)
                ->firstOrFail();

            // Vérifier l'autorisation
            if ($paymentMethod->user_id !== auth()->id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthorized',
                ], 403);
            }

            // Détacher de Stripe
            try {
                $this->stripeService->detachPaymentMethod(
                    $paymentMethod->stripe_payment_method_id
                );
            } catch (\Exception $e) {
                // Log mais continuer même si le détachement échoue
                Log::warning('Failed to detach payment method from Stripe: ' . $e->getMessage());
            }

            // Soft delete
            $paymentMethod->update(['is_deleted' => true]);

            // Si c'était la méthode par défaut, en définir une nouvelle
            if ($paymentMethod->is_default) {
                $newDefault = PaymentMethod::where('user_id', auth()->id())
                    ->where('is_deleted', false)
                    ->first();
                    
                if ($newDefault) {
                    $newDefault->update(['is_default' => true]);
                    
                    // Définir dans Stripe aussi
                    try {
                        $user = auth()->user();
                        $customerId = $this->stripeService->getOrCreateCustomer($user);
                        $this->stripeService->setDefaultPaymentMethod(
                            $customerId, 
                            $newDefault->stripe_payment_method_id
                        );
                    } catch (\Exception $e) {
                        Log::warning('Failed to set new default in Stripe: ' . $e->getMessage());
                    }
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error deleting payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete payment method',
            ], 400);
        }
    }
}