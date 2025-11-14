<?php

namespace App\Services;

use App\Models\Coupon;
use App\Models\User;
use App\Models\Subscription;

class CouponService
{
    /**
     * Validate and apply a coupon to a subscription.
     */
    public function applyCoupon(User $user, Subscription $subscription, string $couponCode): array
    {
        $coupon = Coupon::where('code', $couponCode)->first();

        if (!$coupon) {
            return [
                'success' => false,
                'message' => 'Coupon not found',
            ];
        }

        if (!$coupon->isValid()) {
            return [
                'success' => false,
                'message' => 'Coupon is no longer valid',
            ];
        }

        if (!$coupon->isApplicableToPlan($subscription->plan_id)) {
            return [
                'success' => false,
                'message' => 'Coupon is not applicable to this plan',
            ];
        }

        if (!$coupon->canBeUsedByUser($user)) {
            return [
                'success' => false,
                'message' => 'You have already used this coupon',
            ];
        }

        // Calculate discount
        $discountAmount = $coupon->calculateDiscount($subscription->plan->price);

        // Apply coupon to subscription
        $subscription->update([
            'coupon_id' => $coupon->id,
            'discount_amount' => $discountAmount,
        ]);

        // Record coupon usage
        $coupon->users()->attach($user->id, [
            'subscription_id' => $subscription->id,
            'discount_amount' => $discountAmount,
            'used_at' => now(),
        ]);

        // Increment used count
        $coupon->incrementUsedCount();

        return [
            'success' => true,
            'message' => 'Coupon applied successfully',
            'discount_amount' => $discountAmount,
        ];
    }

    /**
     * Remove a coupon from a subscription.
     */
    public function removeCoupon(Subscription $subscription): void
    {
        $subscription->update([
            'coupon_id' => null,
            'discount_amount' => 0,
        ]);
    }

    /**
     * Get available coupons for a user and plan.
     */
    public function getAvailableCoupons(User $user, ?int $planId = null): array
    {
        $query = Coupon::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>=', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                    ->orWhereRaw('used_count < max_uses');
            });

        if ($planId) {
            $query->where(function ($q) use ($planId) {
                $q->whereNull('applicable_plans')
                    ->orWhereJsonContains('applicable_plans', $planId);
            });
        }

        return $query->get()
            ->filter(fn($coupon) => $coupon->canBeUsedByUser($user))
            ->values()
            ->toArray();
    }

    /**
     * Create a coupon.
     */
    public function createCoupon(array $data): Coupon
    {
        return Coupon::create($data);
    }

    /**
     * Update a coupon.
     */
    public function updateCoupon(Coupon $coupon, array $data): Coupon
    {
        $coupon->update($data);
        return $coupon;
    }

    /**
     * Delete a coupon.
     */
    public function deleteCoupon(Coupon $coupon): bool
    {
        return $coupon->delete();
    }

    /**
     * Get coupon statistics.
     */
    public function getCouponStats(Coupon $coupon): array
    {
        return [
            'total_uses' => $coupon->used_count,
            'remaining_uses' => $coupon->max_uses ? $coupon->max_uses - $coupon->used_count : null,
            'is_valid' => $coupon->isValid(),
            'users_count' => $coupon->users()->count(),
        ];
    }
}

