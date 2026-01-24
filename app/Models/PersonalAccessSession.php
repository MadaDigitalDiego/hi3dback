<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonalAccessSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'token_id',
        'last_activity_at',
        'ip_address',
        'user_agent',
        'is_active',
    ];

    protected $casts = [
        'last_activity_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Get the user that owns the session.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the token associated with the session.
     */
    public function token(): BelongsTo
    {
        return $this->belongsTo(\Laravel\Sanctum\PersonalAccessToken::class, 'token_id');
    }

    /**
     * Check if the session has expired based on inactivity timeout.
     */
    public function isExpired(int $timeoutMinutes = 30): bool
    {
        return $this->last_activity_at->addMinutes($timeoutMinutes)->isPast();
    }

    /**
     * Update the last activity timestamp.
     */
    public function updateActivity(): void
    {
        $this->update(['last_activity_at' => now()]);
    }

    /**
     * Deactivate the session.
     */
    public function deactivate(): void
    {
        $this->update(['is_active' => false]);
    }

    /**
     * Get active sessions for a user.
     */
    public static function getActiveSessionsForUser(int $userId)
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->with('token')
            ->orderByDesc('last_activity_at')
            ->get();
    }

    /**
     * Deactivate expired sessions for a user.
     */
    public static function deactivateExpiredSessionsForUser(int $userId, int $timeoutMinutes = 30): int
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->where('last_activity_at', '<', now()->subMinutes($timeoutMinutes))
            ->update(['is_active' => false]);
    }
}

