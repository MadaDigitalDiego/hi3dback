<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClientDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'profile_id',
        'type',
        'company_name',
        'company_size',
        'industry',
        'position',
        'website',
        'registration_number',
        'birth_date',
        'preferences',
    ];

    protected $casts = [
        'preferences' => 'array',
        'birth_date' => 'date',
    ];

    /**
     * Get the profile that owns the client details.
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

    /**
     * Determine if this client is a company.
     */
    public function isCompany(): bool
    {
        return $this->type === 'entreprise';
    }
}
