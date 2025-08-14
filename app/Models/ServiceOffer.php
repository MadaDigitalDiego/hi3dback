<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class ServiceOffer extends Model
{
    use HasFactory, Searchable;

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
