<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Import BelongsToMany

class OpenOffer extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'title',
        'categories',
        'filters',
        'budget',
        'deadline',
        'company',
        'website',
        'description',
        'files',
        'recruitment_type',
        'open_to_applications',
        'auto_invite',
        'status',
        'views_count',
    ];

    protected $casts = [
        'categories' => 'array', // Cast 'categories' to array
        'files' => 'array',      // Cast 'files' to array
        'deadline' => 'date',
        'open_to_applications' => 'boolean',
        'auto_invite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(OfferApplication::class);
    }
    // Define the many-to-many relationship with User (professionals)
    public function professionals(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'open_offer_user'); // Second argument is the pivot table name
    }

    public function emailLogs()
    {
        return $this->hasMany(OfferEmailLog::class);
    }
}
//**************************************************** */

