<?php

namespace App\Models;

use App\Models\User;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfessionalProfileView extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_profile_id',
        'user_id',
        'session_id',
        'ip_address',
        'user_agent',
    ];

    /**
     * Get the professional profile that owns the view.
     */
    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }

    /**
     * Get the user that owns the view.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope to get views for a specific profile.
     */
    public function scopeForProfile($query, $profileId)
    {
        return $query->where('professional_profile_id', $profileId);
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
        return $query->distinct(['professional_profile_id', 'user_id', 'session_id']);
    }
}
