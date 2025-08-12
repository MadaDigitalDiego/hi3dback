<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $fillable = [
        'experience_id',
        'name',
        'description',
        'image_path',
        'project_url',
    ];

    /**
     * Get the experience that owns the project.
     */
    public function experience(): BelongsTo
    {
        return $this->belongsTo(Experience::class);
    }
}
