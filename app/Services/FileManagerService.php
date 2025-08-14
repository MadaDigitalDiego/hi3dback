<?php

namespace App\Services;

use App\Models\File;
use App\Models\User;
use Exception;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileManagerService
{
    private SwissTransferService $swissTransferService;
    private int $localStorageLimit; // MB
    private int $maxUploadSize; // MB
    private array $allowedMimeTypes;

    public function __construct(SwissTransferService $swissTransferService)
    {
        $this->swissTransferService = $swissTransferService;
        $this->localStorageLimit = config('filesystems.file_management.local_storage_limit', 10);
        $this->maxUploadSize = config('filesystems.file_management.max_upload_size', 500);
        $this->allowedMimeTypes = config('filesystems.file_management.allowed_mime_types', []);
    }

    /**
     * Upload a file with intelligent storage selection
     *
     * @param UploadedFile $file
     * @param User $user
     * @param mixed $fileable
     * @param array $options
     * @return File
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, User $user, $fileable = null, array $options = [], $messageId = null): File
    {
        // Validate file
        $this->validateFile($file);

        // Create file record
        $fileRecord = $this->createFileRecord($file, $user, $fileable, $messageId);

        try {
            // Determine storage type based on file size
            $fileSizeMB = $file->getSize() / 1024 / 1024;
            $useSwissTransfer = $fileSizeMB > $this->localStorageLimit;

            if ($useSwissTransfer && $this->swissTransferService->isEnabled()) {
                $this->uploadToSwissTransfer($file, $fileRecord, $options);
            } else {
                $this->uploadToLocal($file, $fileRecord);
            }

            // Mark as completed
            $fileRecord->update(['status' => File::STATUS_COMPLETED]);

            Log::info('File upload completed', [
                'file_id' => $fileRecord->id,
                'storage_type' => $fileRecord->storage_type,
                'file_size_mb' => round($fileSizeMB, 2),
                'user_id' => $user->id
            ]);

            return $fileRecord->fresh();

        } catch (Exception $e) {
            // Mark as failed
            $fileRecord->update([
                'status' => File::STATUS_FAILED,
                'error_message' => $e->getMessage()
            ]);

            Log::error('File upload failed', [
                'file_id' => $fileRecord->id,
                'error' => $e->getMessage(),
                'user_id' => $user->id
            ]);

            throw $e;
        }
    }

    /**
     * Upload multiple files
     *
     * @param array $files
     * @param User $user
     * @param mixed $fileable
     * @param array $options
     * @return array
     */
    public function uploadMultipleFiles(array $files, User $user, $fileable = null, array $options = [], $messageId = null): array
    {
        $results = [];
        $errors = [];

        foreach ($files as $index => $file) {
            try {
                $results[] = $this->uploadFile($file, $user, $fileable, $options, $messageId);
            } catch (Exception $e) {
                $errors[] = [
                    'index' => $index,
                    'filename' => $file->getClientOriginalName(),
                    'error' => $e->getMessage()
                ];
            }
        }

        return [
            'success' => $results,
            'errors' => $errors,
            'total' => count($files),
            'successful' => count($results),
            'failed' => count($errors)
        ];
    }

    /**
     * Validate uploaded file
     */
    private function validateFile(UploadedFile $file): void
    {
        // Check file size
        $fileSizeMB = $file->getSize() / 1024 / 1024;
        if ($fileSizeMB > $this->maxUploadSize) {
            throw new Exception("File size ({$fileSizeMB}MB) exceeds maximum allowed size ({$this->maxUploadSize}MB)");
        }

        // Check MIME type
        if (!empty($this->allowedMimeTypes) && !in_array($file->getMimeType(), $this->allowedMimeTypes)) {
            throw new Exception("File type '{$file->getMimeType()}' is not allowed");
        }

        // Check if file is valid
        if (!$file->isValid()) {
            throw new Exception("Invalid file upload");
        }
    }

    /**
     * Create initial file record
     */
    private function createFileRecord(UploadedFile $file, User $user, $fileable = null, $messageId = null): File
    {
        $filename = $this->generateUniqueFilename($file);
        
        return File::create([
            'original_name' => $file->getClientOriginalName(),
            'filename' => $filename,
            'mime_type' => $file->getMimeType(),
            'size' => $file->getSize(),
            'extension' => $file->getClientOriginalExtension(),
            'storage_type' => File::STORAGE_LOCAL, // Will be updated based on actual storage
            'status' => File::STATUS_UPLOADING,
            'user_id' => $user->id,
            'fileable_type' => $fileable ? get_class($fileable) : null,
            'fileable_id' => $fileable ? $fileable->id : null,
            'message_id' => $messageId,
        ]);
    }

    /**
     * Upload file to local storage
     */
    private function uploadToLocal(UploadedFile $file, File $fileRecord): void
    {
        $path = $file->store('uploads', 'public');
        
        $fileRecord->update([
            'storage_type' => File::STORAGE_LOCAL,
            'local_path' => $path,
        ]);
    }

    /**
     * Upload file to SwissTransfer
     */
    private function uploadToSwissTransfer(UploadedFile $file, File $fileRecord, array $options = []): void
    {
        $result = $this->swissTransferService->uploadFile($file, $options);
        
        $fileRecord->update([
            'storage_type' => File::STORAGE_SWISSTRANSFER,
            'swisstransfer_url' => $result['share_url'] ?? null,
            'swisstransfer_download_url' => $result['download_url'] ?? null,
            'swisstransfer_delete_url' => $result['delete_url'] ?? null,
            'swisstransfer_expires_at' => $result['expires_at'] ?? null,
            'metadata' => array_merge($fileRecord->metadata ?? [], $result['metadata'] ?? []),
        ]);
    }

    /**
     * Generate unique filename
     */
    private function generateUniqueFilename(UploadedFile $file): string
    {
        $extension = $file->getClientOriginalExtension();
        $basename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $basename = Str::slug($basename);
        
        return $basename . '_' . uniqid() . '.' . $extension;
    }

    /**
     * Delete a file
     */
    public function deleteFile(File $file): bool
    {
        try {
            DB::beginTransaction();

            if ($file->isLocal() && $file->local_path) {
                Storage::disk('public')->delete($file->local_path);
            }

            if ($file->isSwissTransfer() && $file->swisstransfer_delete_url) {
                $this->swissTransferService->deleteFile($file->swisstransfer_delete_url);
            }

            $file->delete();
            
            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('File deletion failed', [
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get file download URL
     */
    public function getDownloadUrl(File $file): ?string
    {
        if ($file->status !== File::STATUS_COMPLETED) {
            return null;
        }

        if ($file->isExpired()) {
            return null;
        }

        return $file->download_url;
    }

    /**
     * Check and update expired files
     */
    public function checkExpiredFiles(): int
    {
        $expiredCount = 0;
        
        $expiredFiles = File::where('storage_type', File::STORAGE_SWISSTRANSFER)
            ->where('status', File::STATUS_COMPLETED)
            ->where('swisstransfer_expires_at', '<', now())
            ->get();

        foreach ($expiredFiles as $file) {
            $file->update(['status' => File::STATUS_EXPIRED]);
            $expiredCount++;
        }

        Log::info("Marked {$expiredCount} files as expired");
        
        return $expiredCount;
    }

    /**
     * Get storage statistics
     */
    public function getStorageStats(): array
    {
        return [
            'total_files' => File::count(),
            'local_files' => File::byStorageType(File::STORAGE_LOCAL)->count(),
            'swisstransfer_files' => File::byStorageType(File::STORAGE_SWISSTRANSFER)->count(),
            'completed_files' => File::completed()->count(),
            'failed_files' => File::where('status', File::STATUS_FAILED)->count(),
            'expired_files' => File::where('status', File::STATUS_EXPIRED)->count(),
            'total_size_bytes' => File::completed()->sum('size'),
            'local_size_bytes' => File::byStorageType(File::STORAGE_LOCAL)->completed()->sum('size'),
            'swisstransfer_size_bytes' => File::byStorageType(File::STORAGE_SWISSTRANSFER)->completed()->sum('size'),
        ];
    }
}
