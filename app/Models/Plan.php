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
     */
    public function getLimit(string $feature): ?int
    {
        // Vérifier d'abord les colonnes spécifiques
        $columnMap = [
            'services' => 'max_services',
            'open_offers' => 'max_open_offers',
            'applications' => 'max_applications',
            'messages' => 'max_messages',
        ];

        if (isset($columnMap[$feature]) && $this->{$columnMap[$feature]} !== null) {
            return $this->{$columnMap[$feature]};
        }

        // Sinon, chercher dans le JSON limits
        if ($this->limits && isset($this->limits[$feature])) {
            return $this->limits[$feature];
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
}
