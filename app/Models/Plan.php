<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'name',
        'user_type',
        'description',
        'stripe_product_id',
        'stripe_price_id',
        'stripe_price_id_monthly',
        'stripe_price_id_yearly',
        'price',
        'yearly_price',
        'interval',
        'interval_count',
        'is_active',
        'features',
        'limits',
        'sort_order',
        'max_services',
        'max_open_offers',
        'max_applications',
        'max_messages',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'yearly_price' => 'decimal:2',
        'is_active' => 'boolean',
        'features' => 'array',
        'limits' => 'array',
        'user_type' => 'string',
    ];

    /**
     * Get the subscriptions for this plan.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the active subscriptions for this plan.
     */
    public function activeSubscriptions(): HasMany
    {
        return $this->subscriptions()->where('stripe_status', 'active');
    }

    /**
     * Get the limit for a specific feature.
     *
     * Supported feature keys (aliases are accepted):
     *  - services / service_offers  -> max_services
     *  - open_offers                -> max_open_offers
     *  - applications               -> max_applications
     *  - messages                   -> max_messages
     */
    public function getLimit(string $feature): ?int
    {
        // Normalise some common aliases so calling code can use either
        $normalized = match ($feature) {
            'service_offers' => 'services',
            default => $feature,
        };

        // 1) Check strongly-typed max_* columns first
        $columnMap = [
            'services' => 'max_services',
            'open_offers' => 'max_open_offers',
            'applications' => 'max_applications',
            'messages' => 'max_messages',
        ];

        if (isset($columnMap[$normalized]) && $this->{$columnMap[$normalized]} !== null) {
            return $this->{$columnMap[$normalized]};
        }

        // 2) Fallback to legacy JSON limits structure for backward compatibility
        if ($this->limits && isset($this->limits[$feature])) {
            return $this->limits[$feature];
        }

        if ($this->limits && isset($this->limits[$normalized])) {
            return $this->limits[$normalized];
        }

        return null;
    }

    /**
     * Check if a feature is limited for this plan.
     */
    public function hasLimit(string $feature): bool
    {
        return $this->getLimit($feature) !== null;
    }



    /**
     * Check if the plan has a specific feature.
     */
    public function hasFeature(string $feature): bool
    {
        return isset($this->features[$feature]) && $this->features[$feature] === true;
    }

    /**
     * Get the display price.
     */
    public function getDisplayPrice(): string
    {
        return number_format($this->price, 2) . ' ' . config('app.currency', 'USD');
    }

    /**
     * Check if this is a free plan (price = 0).
     */
    public function isFree(): bool
    {
        return $this->price == 0;
    }

    /**
     * Find the free plan for a specific user type.
     */
    public static function findFreePlanForUserType(string $userType): ?self
    {
        return static::where('user_type', $userType)
                    ->where('price', 0)
                    ->where('is_active', true)
                    ->first();
    }

    /**
     * Check if a free plan already exists for the given user type (excluding current plan if updating).
     */
    public static function hasExistingFreePlanForUserType(string $userType, ?int $excludePlanId = null): bool
    {
        $query = static::where('user_type', $userType)
                      ->where('price', 0)
                      ->where('is_active', true);

        if ($excludePlanId) {
            $query->where('id', '!=', $excludePlanId);
        }

        return $query->exists();
    }

    /**
     * Boot method to add model event listeners.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function (Plan $plan) {
            // Validate unique free plan constraint before creating
            if ($plan->isFree() && static::hasExistingFreePlanForUserType($plan->user_type)) {
                throw new \Exception('Un plan gratuit existe déjà pour ce type d\'utilisateur (' . $plan->user_type . ').');
            }
        });

        static::updating(function (Plan $plan) {
            // Validate unique free plan constraint before updating
            if ($plan->isFree() && static::hasExistingFreePlanForUserType($plan->user_type, $plan->id)) {
                throw new \Exception('Un plan gratuit existe déjà pour ce type d\'utilisateur (' . $plan->user_type . ').');
            }
        });
    }
}
