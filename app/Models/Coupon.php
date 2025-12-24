<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Services\StripeService;
use Illuminate\Support\Facades\Log;

class Coupon extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'stripe_coupon_id',
        'description',
        'type',
        'value',
        'max_discount',
        'max_uses',
        'used_count',
        'is_active',
        'starts_at',
        'expires_at',
        'applicable_plans',
        'metadata',
    ];

    protected $casts = [
        'value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'is_active' => 'boolean',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_plans' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the users that have used this coupon.
     */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'coupon_user')
            ->withPivot('subscription_id', 'discount_amount', 'used_at')
            ->withTimestamps();
    }

    /**
     * Check if the coupon is valid.
     */
    public function isValid(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    /**
     * Check if the coupon is applicable to a plan.
     */
    public function isApplicableToPlan(?int $planId = null): bool
    {
        if (!$this->applicable_plans) {
            return true; // Applicable to all plans
        }

        if ($planId === null) {
            return true;
        }

        return in_array($planId, $this->applicable_plans);
    }

    /**
     * Calculate the discount amount for a given price.
     */
    public function calculateDiscount(float $price): float
    {
        if ($this->type === 'percentage') {
            $discount = ($price * $this->value) / 100;
        } else {
            $discount = $this->value;
        }

        if ($this->max_discount) {
            $discount = min($discount, $this->max_discount);
        }

        return min($discount, $price);
    }

    /**
     * Increment the used count.
     */
    public function incrementUsedCount(): void
    {
        $this->increment('used_count');
    }

    /**
     * Check if the coupon can be used by a user.
     */
    public function canBeUsedByUser(User $user): bool
    {
        if (!$this->isValid()) {
            return false;
        }

        // Check if user has already used this coupon
        return !$this->users()->where('user_id', $user->id)->exists();
    }

    /**
     * Automatically sync newly created coupons with Stripe.
     */
    protected static function booted(): void
    {
        static::created(function (self $coupon) {
            try {
                /** @var StripeService $stripeService */
                $stripeService = app(StripeService::class);
                $stripeService->syncCoupon($coupon);
            } catch (\Throwable $e) {
                Log::error('Failed to sync coupon with Stripe on create', [
                    'coupon_id' => $coupon->id,
                    'code' => $coupon->code,
                    'error' => $e->getMessage(),
                ]);
            }
        });
    }
}

