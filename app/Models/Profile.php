<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'avatar',
        'social_links',
        'completion_percentage',
    ];

    protected $casts = [
        'social_links' => 'array',
        'completion_percentage' => 'integer',
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the professional details associated with the profile.
     */
    public function professionalDetails(): HasOne
    {
        return $this->hasOne(ProfessionalDetail::class);
    }

    /**
     * Get the client details associated with the profile.
     */
    public function clientDetails(): HasOne
    {
        return $this->hasOne(ClientDetail::class);
    }

    /**
     * Determine if this profile belongs to a professional user.
     */
    public function isProfessional(): bool
    {
        return $this->user->is_professional;
    }

    /**
     * Get the appropriate details based on user type.
     */
    public function details()
    {
        return $this->isProfessional() ? $this->professionalDetails : $this->clientDetails;
    }
}
