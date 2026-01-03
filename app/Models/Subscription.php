<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Subscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'plan_id',
        'stripe_id',
        'stripe_subscription_id',
        'stripe_status',
        'quantity',
        'trial_ends_at',
        'ends_at',
        'current_period_start',
        'current_period_end',
        'coupon_id',
        'discount_amount',
        'notes',
    ];

    protected $appends = [
        'latest_payment_intent_client_secret',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'ends_at' => 'datetime',
        'current_period_start' => 'datetime',
        'current_period_end' => 'datetime',
        'discount_amount' => 'decimal:2',
    ];

    /**
     * Get the user that owns the subscription.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the plan for this subscription.
     */
    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    /**
     * Get the coupon applied to this subscription.
     */
    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the invoices for this subscription.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Check if the subscription is active.
     */
    public function isActive(): bool
    {
        return $this->stripe_status === 'active' &&
               (!$this->ends_at || $this->ends_at->isFuture());
    }

    /**
     * Check if the subscription is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->stripe_status === 'trialing' &&
               $this->trial_ends_at &&
               $this->trial_ends_at->isFuture();
    }

    /**
     * Check if the subscription is past due.
     */
    public function isPastDue(): bool
    {
        return $this->stripe_status === 'past_due';
    }

    /**
     * Check if the subscription is canceled.
     */
    public function isCanceled(): bool
    {
        return $this->stripe_status === 'canceled';
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(): void
    {
        $this->update([
            'stripe_status' => 'canceled',
            'ends_at' => now(),
        ]);
    }

    /**
     * Resume the subscription.
     */
    public function resume(): void
    {
        $this->update([
            'stripe_status' => 'active',
            'ends_at' => null,
        ]);
    }

    /**
     * Get the latest payment intent client secret (virtual attribute).
     */
    public function getLatestPaymentIntentClientSecretAttribute()
    {
        return $this->attributes['latest_payment_intent_client_secret'] ?? null;
    }
}
