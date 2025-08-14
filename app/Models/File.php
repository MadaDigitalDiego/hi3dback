<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class File extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'original_name',
        'filename',
        'mime_type',
        'size',
        'extension',
        'storage_type',
        'local_path',
        'swisstransfer_url',
        'swisstransfer_download_url',
        'swisstransfer_delete_url',
        'swisstransfer_expires_at',
        'status',
        'error_message',
        'metadata',
        'user_id',
        'fileable_type',
        'fileable_id',
        'message_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'swisstransfer_expires_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Storage type constants
     */
    const STORAGE_LOCAL = 'local';
    const STORAGE_SWISSTRANSFER = 'swisstransfer';

    /**
     * Status constants
     */
    const STATUS_UPLOADING = 'uploading';
    const STATUS_COMPLETED = 'completed';
    const STATUS_FAILED = 'failed';
    const STATUS_EXPIRED = 'expired';

    /**
     * Get the user that owns the file.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the parent fileable model (Achievement, ServiceOffer, etc.).
     */
    public function fileable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the message that owns the file.
     */
    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    /**
     * Get the file URL for download.
     */
    public function getDownloadUrlAttribute(): ?string
    {
        if ($this->storage_type === self::STORAGE_LOCAL && $this->local_path) {
            return Storage::disk('public')->url($this->local_path);
        }

        if ($this->storage_type === self::STORAGE_SWISSTRANSFER && $this->swisstransfer_download_url) {
            return $this->swisstransfer_download_url;
        }

        return null;
    }

    /**
     * Check if file is stored locally.
     */
    public function isLocal(): bool
    {
        return $this->storage_type === self::STORAGE_LOCAL;
    }

    /**
     * Check if file is stored on SwissTransfer.
     */
    public function isSwissTransfer(): bool
    {
        return $this->storage_type === self::STORAGE_SWISSTRANSFER;
    }

    /**
     * Check if file is expired (for SwissTransfer files).
     */
    public function isExpired(): bool
    {
        return $this->swisstransfer_expires_at && $this->swisstransfer_expires_at->isPast();
    }

    /**
     * Get human readable file size.
     */
    public function getHumanSizeAttribute(): string
    {
        $bytes = $this->size;
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    /**
     * Scope for completed files only.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', self::STATUS_COMPLETED);
    }

    /**
     * Scope for files by storage type.
     */
    public function scopeByStorageType($query, string $type)
    {
        return $query->where('storage_type', $type);
    }
}
