<?php

namespace App\Models;

use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ProfessionalProfile extends Model
{
    use HasFactory;

    public const AVAILABILITY_AVAILABLE = 'available';
    public const AVAILABILITY_UNAVAILABLE = 'unavailable';
    public const AVAILABILITY_BUSY = 'busy';

    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'address',
        'city',
        'country',
        'bio',
        'avatar',
        'cover_photo',
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
        'social_links',
        'completion_percentage'
    ];

    protected $casts = [
        'expertise' => 'array',
        'years_of_experience' => 'integer',
        'hourly_rate' => 'decimal:2',
        'skills' => 'array',
        'portfolio' => 'array',
        'languages' => 'array',
        'services_offered' => 'array',
        'social_links' => 'array',
        'rating' => 'decimal:1',
        'completion_percentage' => 'integer'
    ];

    /**
     * Accesseur pour s'assurer que skills est toujours un tableau
     */
    public function getSkillsAttribute($value)
    {
        return $value ? json_decode($value, true) : [];
    }

    /**
     * Accesseur pour s'assurer que languages est toujours un tableau
     */
    public function getLanguagesAttribute($value)
    {
        Log::info('getLanguagesAttribute appelé avec: ' . (is_string($value) ? $value : json_encode($value)) . ' (type: ' . gettype($value) . ')');

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Erreur JSON lors du décodage de languages dans l\'accesseur: ' . json_last_error_msg());
                    return [];
                }
                return $decoded;
            } catch (\Exception $e) {
                Log::error('Exception lors du traitement de languages dans l\'accesseur: ' . $e->getMessage());
                return [];
            }
        } else if (is_array($value)) {
            return $value;
        } else if (is_null($value)) {
            return [];
        }

        Log::warning('Type de languages inattendu dans l\'accesseur: ' . gettype($value));
        return [];
    }

    /**
     * Accesseur pour s'assurer que services_offered est toujours un tableau
     */
    public function getServicesOfferedAttribute($value)
    {
        Log::info('getServicesOfferedAttribute appelé avec: ' . (is_string($value) ? $value : json_encode($value)) . ' (type: ' . gettype($value) . ')');

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Erreur JSON lors du décodage de services_offered dans l\'accesseur: ' . json_last_error_msg());
                    return [];
                }
                return $decoded;
            } catch (\Exception $e) {
                Log::error('Exception lors du traitement de services_offered dans l\'accesseur: ' . $e->getMessage());
                return [];
            }
        } else if (is_array($value)) {
            return $value;
        } else if (is_null($value)) {
            return [];
        }

        Log::warning('Type de services_offered inattendu dans l\'accesseur: ' . gettype($value));
        return [];
    }

    /**
     * Mutator pour s'assurer que skills est toujours un tableau
     */
    public function setSkillsAttribute($value)
    {
        Log::info('setSkillsAttribute appelé avec: ' . json_encode($value) . ' (type: ' . gettype($value) . ')');

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Erreur JSON lors du décodage de skills: ' . json_last_error_msg());
                    $decoded = [];
                }
                $this->attributes['skills'] = json_encode($decoded);
                Log::info('Skills encodé après décodage: ' . $this->attributes['skills']);
            } catch (\Exception $e) {
                Log::error('Exception lors du traitement de skills: ' . $e->getMessage());
                $this->attributes['skills'] = json_encode([]);
            }
        } else if (is_array($value)) {
            $this->attributes['skills'] = json_encode($value);
            Log::info('Skills encodé directement: ' . $this->attributes['skills']);
        } else {
            Log::warning('Skills n\'est ni une chaîne ni un tableau, utilisation d\'un tableau vide');
            $this->attributes['skills'] = json_encode([]);
        }
    }

    /**
     * Mutator pour s'assurer que languages est toujours un tableau
     */
    public function setLanguagesAttribute($value)
    {
        Log::info('setLanguagesAttribute appelé avec: ' . json_encode($value) . ' (type: ' . gettype($value) . ')');

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Erreur JSON lors du décodage de languages: ' . json_last_error_msg());
                    $decoded = [];
                }
                $this->attributes['languages'] = json_encode($decoded);
                Log::info('Languages encodé après décodage: ' . $this->attributes['languages']);
            } catch (\Exception $e) {
                Log::error('Exception lors du traitement de languages: ' . $e->getMessage());
                $this->attributes['languages'] = json_encode([]);
            }
        } else if (is_array($value)) {
            $this->attributes['languages'] = json_encode($value);
            Log::info('Languages encodé directement: ' . $this->attributes['languages']);
        } else {
            Log::warning('Languages n\'est ni une chaîne ni un tableau, utilisation d\'un tableau vide');
            $this->attributes['languages'] = json_encode([]);
        }
    }

    /**
     * Mutator pour s'assurer que services_offered est toujours un tableau
     */
    public function setServicesOfferedAttribute($value)
    {
        Log::info('setServicesOfferedAttribute appelé avec: ' . json_encode($value) . ' (type: ' . gettype($value) . ')');

        if (is_string($value)) {
            try {
                $decoded = json_decode($value, true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    Log::warning('Erreur JSON lors du décodage de services_offered: ' . json_last_error_msg());
                    $decoded = [];
                }
                $this->attributes['services_offered'] = json_encode($decoded);
                Log::info('Services_offered encodé après décodage: ' . $this->attributes['services_offered']);
            } catch (\Exception $e) {
                Log::error('Exception lors du traitement de services_offered: ' . $e->getMessage());
                $this->attributes['services_offered'] = json_encode([]);
            }
        } else if (is_array($value)) {
            $this->attributes['services_offered'] = json_encode($value);
            Log::info('Services_offered encodé directement: ' . $this->attributes['services_offered']);
        } else {
            Log::warning('Services_offered n\'est ni une chaîne ni un tableau, utilisation d\'un tableau vide');
            $this->attributes['services_offered'] = json_encode([]);
        }
    }

    /**
     * Mutator pour s'assurer que social_links est toujours un tableau
     */
    public function setSocialLinksAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['social_links'] = json_encode(json_decode($value, true) ?: []);
        } else {
            $this->attributes['social_links'] = json_encode($value ?: []);
        }
    }

    /**
     * Mutator pour s'assurer que portfolio est toujours un tableau
     */
    public function setPortfolioAttribute($value)
    {
        if (is_string($value)) {
            $this->attributes['portfolio'] = json_encode(json_decode($value, true) ?: []);
        } else {
            $this->attributes['portfolio'] = json_encode($value ?: []);
        }
    }

    /**
     * Get the user that owns the profile.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the experiences for the professional profile.
     */
    public function experiences(): HasMany
    {
        return $this->hasMany(Experience::class);
    }

    /**
     * Get the achievements for the professional profile.
     */
    public function achievements(): HasMany
    {
        return $this->hasMany(Achievement::class);
    }

    public function service_offer(): HasMany
    {
        return $this->hasMany(ServiceOffer::class);
    }

    public function emailLogs()
    {
        return $this->hasManyThrough(
            OfferEmailLog::class,
            User::class,
            'id', // Clé étrangère sur users
            'user_id', // Clé étrangère sur offer_email_logs
            'user_id', // Clé locale sur professional_profiles
            'id' // Clé locale sur users
        );
    }
}
