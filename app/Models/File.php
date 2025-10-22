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
        'receiver_id',
        'fileable_type',
        'fileable_id',
        'message_id',
        'is_shared',
        'shared_at',
        'accessed_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'size' => 'integer',
        'metadata' => 'array',
        'is_shared' => 'boolean',
        'swisstransfer_expires_at' => 'datetime',
        'shared_at' => 'datetime',
        'accessed_at' => 'datetime',
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
     * Get the user that received the file.
     */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
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

    /**
     * Check if user can access this file.
     *
     * @param User $user
     * @return bool
     */
    public function canBeAccessedBy(User $user): bool
    {
        // Owner can always access
        if ($this->user_id === $user->id) {
            return true;
        }

        // Receiver can access
        if ($this->receiver_id === $user->id) {
            return true;
        }

        // Admin can access
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Check if user can download this file.
     *
     * @param User $user
     * @return bool
     */
    public function canBeDownloadedBy(User $user): bool
    {
        return $this->canBeAccessedBy($user);
    }

    /**
     * Check if user can delete this file.
     *
     * @param User $user
     * @return bool
     */
    public function canBeDeletedBy(User $user): bool
    {
        // Only owner can delete
        if ($this->user_id === $user->id) {
            return true;
        }

        // Admin can delete
        if ($user->isAdmin() || $user->isSuperAdmin()) {
            return true;
        }

        return false;
    }

    /**
     * Mark file as accessed by user.
     *
     * @param User $user
     * @return void
     */
    public function markAsAccessedBy(User $user): void
    {
        if ($this->canBeAccessedBy($user)) {
            $this->update(['accessed_at' => now()]);
        }
    }

    /**
     * Share file with a receiver.
     *
     * @param User $receiver
     * @return void
     */
    public function shareWith(User $receiver): void
    {
        $this->update([
            'receiver_id' => $receiver->id,
            'is_shared' => true,
            'shared_at' => now(),
        ]);
    }

    /**
     * Unshare file from receiver.
     *
     * @return void
     */
    public function unshare(): void
    {
        $this->update([
            'receiver_id' => null,
            'is_shared' => false,
            'shared_at' => null,
        ]);
    }
}
