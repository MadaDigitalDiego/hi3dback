<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Experience extends Model
{
    use HasFactory;

    protected $fillable = [
        'freelance_profile_id',
        'title',
        'company_name',
        'start_date',
        'end_date',
        'description',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the freelance profile that owns the experience.
     */
    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(FreelanceProfile::class);
    }

    /**
     * Get the projects associated with the experience.
     */
    public function projects(): HasMany
    {
        return $this->hasMany(Project::class);
    }
}
