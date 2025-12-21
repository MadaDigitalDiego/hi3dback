<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

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
     * Create a new subscription.
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
            $plan = Plan::findOrFail($validated['plan_id']);
            $billingPeriod = $validated['billing_period'] ?? null;
            $subscription = $this->stripeService->createSubscription(
                auth()->user(),
                $plan,
                $validated['coupon_code'] ?? null,
                $billingPeriod,
                $validated['payment_method_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription created successfully',
                'data' => $subscription->load('plan'),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating subscription: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Cancel a subscription.
     */
    public function cancelSubscription(): JsonResponse
    {
        $subscription = auth()->user()->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription to cancel',
            ], 404);
        }

        try {
            $this->stripeService->cancelSubscription($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Subscription canceled successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error canceling subscription: ' . $e->getMessage());
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
     * Change subscription to a different plan.
     */
    public function changeSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'billing_period' => 'nullable|string|in:monthly,yearly',
            'payment_method_id' => 'nullable|string',
        ]);

        try {
            $currentSubscription = auth()->user()->currentSubscription();

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

            $subscription = $this->stripeService->changeSubscription(
                $currentSubscription,
                $newPlan,
                $billingPeriod,
                $validated['payment_method_id'] ?? null
            );

            return response()->json([
                'success' => true,
                'message' => 'Subscription changed successfully',
                'data' => $subscription->load('plan'),
            ]);
        } catch (\Exception $e) {
            Log::error('Error changing subscription: ' . $e->getMessage());
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
}
