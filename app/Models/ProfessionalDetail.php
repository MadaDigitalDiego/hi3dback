<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProfessionalDetail extends Model
{
    use HasFactory;

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';
    public const AVAILABILITY_BUSY = 'busy';

    protected $fillable = [
        'profile_id',
        'title',
        'profession',
        'expertise',
        'years_of_experience',
        'hourly_rate',
        'description',
        'skills',
        'portfolio',
        'availability_status',
        'languages',
        'services_offered',
        'rating',
    ];

    protected $casts = [
        'expertise' => 'array',
        'skills' => 'array',
        'portfolio' => 'array',
        'languages' => 'array',
        'services_offered' => 'array',
        'rating' => 'decimal:1',
        'hourly_rate' => 'decimal:2',
        'years_of_experience' => 'integer',
    ];

    /**
     * Get the profile that owns the professional details.
     */
    public function profile(): BelongsTo
    {
        return $this->belongsTo(Profile::class);
    }

    /**
     * Get the user through the profile.
     */
    public function user()
    {
        return $this->profile->user;
    }
}
