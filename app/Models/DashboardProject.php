<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DashboardProject extends Model
{
    use HasFactory;

    protected $table = 'dashboard_projects';

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'category',
        'cover_photo',
        'gallery_photos',
        'youtube_link',
        'status',
    ];

    protected $casts = [
        'gallery_photos' => 'array',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
