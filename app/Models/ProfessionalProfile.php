<?php

namespace App\Models;

use App\Models\User;
use App\Models\Experience;
use App\Models\Achievement;
use App\Models\ServiceOffer;
use App\Models\UserFavorite;
use App\Models\OfferEmailLog;
use Illuminate\Support\Facades\Log;
use App\Models\ProfessionalProfileView;
use Illuminate\Database\Eloquent\Model;
use Overtrue\LaravelLike\Traits\Likeable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Scout\Searchable;

class ProfessionalProfile extends Model
{
    use HasFactory, Likeable, Searchable;

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

    /**
     * Get the views for the professional profile.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ProfessionalProfileView::class);
    }

    /**
     * Get the favorites for the professional profile.
     */
    public function favorites()
    {
        return $this->morphMany(UserFavorite::class, 'favoritable');
    }

    /**
     * Get the total number of views.
     */
    public function getTotalViewsAttribute(): int
    {
        return $this->views()->count();
    }

    /**
     * Get the total number of likes.
     */
    public function getTotalLikesAttribute(): int
    {
        return $this->likers()->count();
    }

    /**
     * Check if the profile is viewed by a specific user/session.
     */
    public function isViewedBy($userId = null, $sessionId = null): bool
    {
        $query = $this->views();

        if ($userId) {
            $query->where('user_id', $userId);
        } elseif ($sessionId) {
            $query->where('session_id', $sessionId);
        }

        return $query->exists();
    }

    /**
     * Record a view for this profile.
     */
    public function recordView($userId = null, $sessionId = null, $ipAddress = null, $userAgent = null): ?ProfessionalProfileView
    {
        // Éviter les doublons
        if ($this->isViewedBy($userId, $sessionId)) {
            return null;
        }

        return $this->views()->create([
            'user_id' => $userId,
            'session_id' => $sessionId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
        ]);
    }

    /**
     * Get the most recent views.
     */
    public function getRecentViews($limit = 10)
    {
        return $this->views()
            ->with('user')
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get unique viewers count.
     */
    public function getUniqueViewersCount(): int
    {
        return $this->views()
            ->whereNotNull('user_id')
            ->distinct('user_id')
            ->count();
    }

    /**
     * Get views count for a specific period.
     */
    public function getViewsCountForPeriod($days = 30): int
    {
        return $this->views()
            ->where('created_at', '>=', now()->subDays($days))
            ->count();
    }

    /**
     * Check if this profile is liked by a specific user.
     */
    public function isLikedByUser($userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->likers()->where('user_id', $userId)->exists();
    }

    /**
     * Get the profile's popularity score based on likes and views.
     */
    public function getPopularityScore(): float
    {
        $likesWeight = 3; // Les likes ont plus de poids que les vues
        $viewsWeight = 1;

        $totalLikes = $this->getTotalLikesAttribute();
        $totalViews = $this->getTotalViewsAttribute();

        return ($totalLikes * $likesWeight) + ($totalViews * $viewsWeight);
    }

    /**
     * Scope to order by popularity (likes + views).
     */
    public function scopeOrderByPopularity($query, $direction = 'desc')
    {
        return $query->withCount(['likers', 'views'])
            ->orderByRaw('(likers_count * 3 + views_count) ' . $direction);
    }

    /**
     * Scope to get most liked profiles.
     */
    public function scopeMostLiked($query, $limit = 10)
    {
        return $query->withCount('likers')
            ->orderBy('likers_count', 'desc')
            ->limit($limit);
    }

    /**
     * Scope to get most viewed profiles.
     */
    public function scopeMostViewed($query, $limit = 10)
    {
        return $query->withCount('views')
            ->orderBy('views_count', 'desc')
            ->limit($limit);
    }

    /**
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->first_name . ' ' . $this->last_name,
            'email' => $this->email,
            'title' => $this->title,
            'profession' => $this->profession,
            'bio' => $this->bio,
            'description' => $this->description,
            'city' => $this->city,
            'country' => $this->country,
            'skills' => $this->skills ?? [],
            'languages' => $this->languages ?? [],
            'services_offered' => $this->services_offered ?? [],
            'expertise' => $this->expertise ?? [],
            'years_of_experience' => (int) $this->years_of_experience,
            'hourly_rate' => (float) $this->hourly_rate,
            'availability_status' => $this->availability_status,
            'rating' => (float) $this->rating,
            'completion_percentage' => (int) $this->completion_percentage,
            'type' => 'professional_profile',
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'professional_profiles_index';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->completion_percentage >= 50; // Only index profiles that are at least 50% complete
    }
}
