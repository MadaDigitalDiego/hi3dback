<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'description',
        'price',
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
     * Méthode de recherche pour les offres de service
     *
     * @param string $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public static function search($query)
    {
        return self::where('title', 'like', "%{$query}%")
            ->orWhere('description', 'like', "%{$query}%");
    }
}
