<?php

namespace App\Models;

use App\Models\User;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class UserFavorite extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'favoritable_type',
        'favoritable_id',
    ];

    /**
     * Get the user that owns the favorite.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the favoritable model (polymorphic relation).
     */
    public function favoritable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Scope to get favorites for a specific user.
     */
    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to get favorites of a specific type.
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('favoritable_type', $type);
    }

    /**
     * Scope to get professional profile favorites.
     */
    public function scopeProfessionalProfiles($query)
    {
        return $query->where('favoritable_type', ProfessionalProfile::class);
    }
}
