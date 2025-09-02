<?php

namespace App\Models;

use App\Models\ServiceOfferView;
use App\Models\UserFavorite;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Scout\Searchable;
use Overtrue\LaravelLike\Traits\Likeable;

class ServiceOffer extends Model
{
    use HasFactory, Searchable, Likeable;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
        'price_unit',
        'execution_time',
        'concepts',
        'revisions',
        'is_private',
        'status',
        'categories',
        'files',
        'views',
        'likes',
        'rating',
        'image',
        'associated_project',
    ];

    protected $casts = [
        'categories' => 'array', // Cast 'categories' to array
        'files' => 'array',      // Cast 'files' to array
        'is_private' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the views for the service offer.
     */
    public function views(): HasMany
    {
        return $this->hasMany(ServiceOfferView::class);
    }

    /**
     * Get the favorites for the service offer.
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
     * Check if the service offer is viewed by a specific user/session.
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
     * Record a view for this service offer.
     */
    public function recordView($userId = null, $sessionId = null, $ipAddress = null, $userAgent = null): ?ServiceOfferView
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
     * Check if this service offer is liked by a specific user.
     */
    public function isLikedByUser($userId): bool
    {
        if (!$userId) {
            return false;
        }

        return $this->likers()->where('user_id', $userId)->exists();
    }

    /**
     * Get the service offer's popularity score based on likes and views.
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
     * Get the indexable data array for the model.
     */
    public function toSearchableArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'price' => (float) $this->price,
            'execution_time' => $this->execution_time,
            'concepts' => $this->concepts,
            'revisions' => $this->revisions,
            'status' => $this->status,
            'categories' => $this->categories ?? [],
            'user_id' => $this->user_id,
            'user_name' => $this->user ? $this->user->first_name . ' ' . $this->user->last_name : null,
            'views' => (int) $this->views,
            'likes' => (int) $this->likes,
            'rating' => (float) $this->rating,
            'type' => 'service_offer',
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'service_offers_index';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return $this->status === 'active' && !$this->is_private;
    }
}
