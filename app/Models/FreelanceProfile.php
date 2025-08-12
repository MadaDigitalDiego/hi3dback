<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany; // <-- Vérifiez cette ligne
use App\Models\User; // Assurez-vous que User est correctement importé si vous l'utilisez
use App\Models\Experience; // Assurez-vous que Experience est correctement importé
use App\Models\Achievement; // Assurez-vous que Achievement est correctement importé

class FreelanceProfile extends Model
{
    use HasFactory;

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'phone',
        'address',
        'city',
        'country',
        'identity_document_path',
        'identity_document_number',
        'experience',
        'portfolio_url',
        'education',
        'diplomas',
        'skills',
        'languages',
        'availability_status',
        'services_offered',
        'hourly_rate',
        'completion_percentage',
        'availability_details',
        'estimated_response_time',
        'bio',
        'avatar',
        'portfolio',
        'rating',
        'title',
    ];

    protected $casts = [
        'skills' => 'array', // Cast les champs JSON en array pour faciliter la manipulation
        'languages' => 'array',
        'services_offered' => 'array',
        'estimated_response_time' => 'datetime', // Caster en datetime pour manipulation facile
        'portfolio' => 'array', // Pour stocker les fichiers du portfolio
    ];

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }


}
