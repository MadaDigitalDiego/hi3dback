<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Laravel\Scout\Searchable;

class Achievement extends Model
{
    use HasFactory, Searchable;

    protected $fillable = [
        'professional_profile_id',
        'title',
        'description',
        'category',
        'cover_photo',
        'gallery_photos',
        'youtube_link',
        'status',
        'date_obtained',
    ];

    protected $casts = [
        'gallery_photos' => 'array',
    ];

    /**
     * Get the freelance profile that owns the achievement.
     */
    public function professionalProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class);
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
            'category' => $this->category,
            'cover_photo' => $this->cover_photo,
            'gallery_photos' => $this->gallery_photos,
            'youtube_link' => $this->youtube_link,
            'status' => $this->status,
            'professional_profile_id' => $this->professional_profile_id,
            'professional_name' => $this->professionalProfile ?
                $this->professionalProfile->first_name . ' ' . $this->professionalProfile->last_name : null,
            'type' => 'achievement',
        ];
    }

    /**
     * Get the name of the index associated with the model.
     */
    public function searchableAs(): string
    {
        return 'achievements_index';
    }

    /**
     * Determine if the model should be searchable.
     */
    public function shouldBeSearchable(): bool
    {
        return !empty($this->title) && !empty($this->category);
    }
}
