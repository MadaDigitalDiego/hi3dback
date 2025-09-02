<?php

namespace App\Models;

use App\Models\User;
use App\Models\ServiceOffer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ServiceOfferView extends Model
{
    use HasFactory;

    protected $fillable = [
        'service_offer_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the service offer that owns the view.
     */
    public function serviceOffer(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class);
    }

    /**
     * Get the user that owns the view.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get views for a specific service offer.
     */
    public function scopeForServiceOffer($query, $serviceOfferId)
    {
        return $query->where('service_offer_id', $serviceOfferId);
    }

    /**
     * Scope to get views by a specific user.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get views by a specific session.
     */
    public function scopeBySession($query, $sessionId)
    {
        return $query->where('session_id', $sessionId);
    }

    /**
     * Scope to get unique views (avoiding duplicates).
     */
    public function scopeUnique($query)
    {
        return $query->distinct(['service_offer_id', 'user_id', 'session_id']);
    }
}
