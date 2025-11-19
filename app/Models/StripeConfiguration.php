<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class StripeConfiguration extends Model
{
    use SoftDeletes;

    protected $table = 'stripe_configurations';

    protected $fillable = [
        'public_key',
        'secret_key',
        'webhook_secret',
        'mode',
        'is_active',
        'description',
        'metadata',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Note: Les champs sensibles ne sont pas cachés ici car Filament en a besoin pour l'édition
    // Ils sont cachés dans les réponses API via le contrôleur
    protected $hidden = [];

    /**
     * Récupère la configuration Stripe active
     */
    public static function getActive(): ?self
    {
        return self::where('is_active', true)
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Récupère la configuration Stripe active avec les clés sensibles
     */
    public static function getActiveWithSecrets(): ?self
    {
        return self::where('is_active', true)
            ->where('deleted_at', null)
            ->first();
    }

    /**
     * Récupère la clé publique Stripe
     */
    public static function getPublicKey(): ?string
    {
        $config = self::getActive();
        return $config?->public_key;
    }

    /**
     * Récupère la clé secrète Stripe
     */
    public static function getSecretKey(): ?string
    {
        $config = self::getActiveWithSecrets();
        return $config?->secret_key;
    }

    /**
     * Récupère le secret webhook Stripe
     */
    public static function getWebhookSecret(): ?string
    {
        $config = self::getActiveWithSecrets();
        return $config?->webhook_secret;
    }
}

