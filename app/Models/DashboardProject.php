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
        'budget',
        'deadline',
        'skills',
        'attachments',
        'status',
    ];

    protected $casts = [
        'skills' => 'array',
        'attachments' => 'array',
        'deadline' => 'date',
    ];

    /**
     * Get the user that owns the project.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
