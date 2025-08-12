<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientProfile extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type', // 'particulier' ou 'entreprise'
        'company_name', // Nullable si particulier
        'industry',     // Nullable si particulier
        'description',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'avatar',
        'birth_date',
        'position',
        'company_size',
        'website',
        'social_links',
        'preferences',
        'completion_percentage'
    ];

    protected $casts = [
        'social_links' => 'array',
        'preferences' => 'array',
        'birth_date' => 'date',
        'completion_percentage' => 'integer'
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
