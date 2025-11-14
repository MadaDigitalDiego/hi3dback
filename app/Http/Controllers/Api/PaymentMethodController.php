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
        $paymentMethods = auth()->user()->paymentMethods()
            ->where('is_deleted', false)
            ->get();

        return response()->json([
            'success' => true,
            'data' => $paymentMethods,
        ]);
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
            $stripeCustomer = $this->stripeService->getOrCreateCustomer($user);

            // Attach payment method to customer
            $this->stripeService->attachPaymentMethod(
                $stripeCustomer->id,
                $validated['stripe_payment_method_id']
            );

            // Save to database
            $paymentMethod = PaymentMethod::create([
                'user_id' => $user->id,
                'stripe_payment_method_id' => $validated['stripe_payment_method_id'],
                'is_default' => $validated['is_default'] ?? false,
            ]);

            // Set as default if requested
            if ($validated['is_default'] ?? false) {
                auth()->user()->paymentMethods()
                    ->where('id', '!=', $paymentMethod->id)
                    ->update(['is_default' => false]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Payment method added successfully',
                'data' => $paymentMethod,
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error adding payment method: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add payment method',
            ], 400);
        }
    }

    /**
     * Update a payment method.
     */
    public function updatePaymentMethod(Request $request, int $id): JsonResponse
    {
        $paymentMethod = PaymentMethod::findOrFail($id);

        // Check authorization
        if ($paymentMethod->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        $validated = $request->validate([
            'is_default' => 'boolean',
        ]);

        try {
            if ($validated['is_default'] ?? false) {
                // Unset other defaults
                auth()->user()->paymentMethods()
                    ->where('id', '!=', $id)
                    ->update(['is_default' => false]);
            }

            $paymentMethod->update($validated);

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
        $paymentMethod = PaymentMethod::findOrFail($id);

        // Check authorization
        if ($paymentMethod->user_id !== auth()->id()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 403);
        }

        try {
            $this->stripeService->detachPaymentMethod(
                $paymentMethod->stripe_payment_method_id
            );

            $paymentMethod->update(['is_deleted' => true]);

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

