<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = [
        'professional_profile_id',
        'title',
        'organization',
        'date_obtained',
        'description',
        'file_path', // Maintenu pour la rétrocompatibilité
        'files', // Nouveau champ pour plusieurs fichiers
        'achievement_url',
    ];

    protected $casts = [
        'date_obtained' => 'date',
        'files' => 'array', // Cast pour le nouveau champ files
    ];

    /**
     * Get the freelance profile that owns the achievement.
     */
    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
    }
}
