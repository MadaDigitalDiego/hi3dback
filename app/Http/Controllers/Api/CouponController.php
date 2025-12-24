<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use App\Models\Plan;
use App\Models\Subscription;
use App\Services\CouponService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CouponController extends Controller
{
    protected CouponService $couponService;

    public function __construct(CouponService $couponService)
    {
        $this->couponService = $couponService;
        $this->middleware('auth:sanctum');
    }

    /**
     * Get available coupons for the user.
     */
    public function getAvailableCoupons(Request $request): JsonResponse
    {
        $planId = $request->query('plan_id');
        $coupons = $this->couponService->getAvailableCoupons(auth()->user(), $planId);

        return response()->json([
            'success' => true,
            'data' => $coupons,
        ]);
    }

    /**
     * Apply a coupon to a subscription.
     */
    public function applyCoupon(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string',
        ]);

        $subscription = auth()->user()->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
            ], 404);
        }

        try {
            $result = $this->couponService->applyCoupon(
                auth()->user(),
                $subscription,
                $validated['coupon_code']
            );

            if (!$result['success']) {
                return response()->json($result, 400);
            }

            return response()->json($result);
        } catch (\Exception $e) {
            Log::error('Error applying coupon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to apply coupon',
            ], 500);
        }
    }

    /**
     * Remove a coupon from a subscription.
     */
    public function removeCoupon(): JsonResponse
    {
        $subscription = auth()->user()->currentSubscription();

        if (!$subscription) {
            return response()->json([
                'success' => false,
                'message' => 'No active subscription found',
            ], 404);
        }

        if (!$subscription->coupon_id) {
            return response()->json([
                'success' => false,
                'message' => 'No coupon applied to this subscription',
            ], 404);
        }

        try {
            $this->couponService->removeCoupon($subscription);

            return response()->json([
                'success' => true,
                'message' => 'Coupon removed successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Error removing coupon: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove coupon',
            ], 500);
        }
    }

    /**
     * Get coupon details.
     */
    public function getCoupon(string $code): JsonResponse
    {
        $coupon = Coupon::where('code', $code)->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is no longer valid',
            ], 400);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'description' => $coupon->description,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'max_discount' => $coupon->max_discount,
                'is_valid' => $coupon->isValid(),
            ],
        ]);
    }

    /**
     * Validate a coupon for a given plan (used by subscription payment flow).
     */
    public function validateForSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'coupon_code' => 'required|string',
            'plan_id' => 'required|exists:plans,id',
        ]);

        $user = auth()->user();

        $coupon = Coupon::where('code', $validated['coupon_code'])->first();

        if (!$coupon) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon not found',
            ], 404);
        }

        if (!$coupon->isValid()) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is no longer valid',
            ], 422);
        }

        // Check that the coupon can be applied to the requested plan
        if (!$coupon->isApplicableToPlan((int) $validated['plan_id'])) {
            return response()->json([
                'success' => false,
                'message' => 'Coupon is not applicable to this plan',
            ], 422);
        }

        // Ensure the current user can still use this coupon
        if (!$coupon->canBeUsedByUser($user)) {
            return response()->json([
                'success' => false,
                'message' => 'You have already used this coupon',
            ], 422);
        }

        $plan = Plan::findOrFail($validated['plan_id']);
        $baseAmount = (float) $plan->price;
        $discount = $coupon->calculateDiscount($baseAmount);
        $finalAmount = max(0, $baseAmount - $discount);

        return response()->json([
            'success' => true,
            'data' => [
                'code' => $coupon->code,
                'type' => $coupon->type,
                'value' => $coupon->value,
                'discount_amount' => $discount,
                'final_amount' => $finalAmount,
            ],
        ]);
    }
}

