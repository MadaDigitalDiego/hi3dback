<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\File;
use App\Services\FileManagerService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class FileController extends Controller
{
    private FileManagerService $fileManagerService;

    public function __construct(FileManagerService $fileManagerService)
    {
        $this->fileManagerService = $fileManagerService;
    }

    /**
     * Upload single or multiple files
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'files' => 'required|array|min:1',
                'files.*' => 'required|file|max:' . (config('filesystems.file_management.max_upload_size', 500) * 10240),
                'fileable_type' => 'nullable|string',
                'fileable_id' => 'nullable|integer',
                'options' => 'nullable|array',
                'options.message' => 'nullable|string|max:500',
                'options.email_recipients' => 'nullable|string',
                'options.download_limit' => 'nullable|integer|min:1|max:1000',
                'options.expiration_days' => 'nullable|integer|min:1|max:90',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = $request->user();
            $files = $request->file('files');
            $options = $request->input('options', []);
            $messageId = $request->input('message_id');

            // Handle fileable relationship
            $fileable = null;
            if ($request->filled('fileable_type') && $request->filled('fileable_id')) {
                $fileableType = $request->input('fileable_type');
                $fileableId = $request->input('fileable_id');

                // Validate fileable type (security check)
                $allowedTypes = [
                    'App\Models\Achievement',
                    'App\Models\ServiceOffer',
                    'App\Models\OpenOffer',
                    'App\Models\Project',
                    'App\Models\ProfessionalProfile',
                ];

                if (in_array($fileableType, $allowedTypes)) {
                    $fileable = $fileableType::find($fileableId);
                }
            }

            // Upload files
            if (count($files) === 1) {
                // Single file upload
                $file = $this->fileManagerService->uploadFile($files[0], $user, $fileable, $options, $messageId);

                return response()->json([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'data' => $this->formatFileResponse($file)
                ], 201);
            } else {
                // Multiple files upload
                $result = $this->fileManagerService->uploadMultipleFiles($files, $user, $fileable, $options, $messageId);

                return response()->json([
                    'success' => true,
                    'message' => "Uploaded {$result['successful']} of {$result['total']} files",
                    'data' => [
                        'files' => array_map([$this, 'formatFileResponse'], $result['success']),
                        'errors' => $result['errors'],
                        'statistics' => [
                            'total' => $result['total'],
                            'successful' => $result['successful'],
                            'failed' => $result['failed']
                        ]
                    ]
                ], 201);
            }

        } catch (Exception $e) {
            Log::error('File upload error', [
                'error' => $e->getMessage(),
                'user_id' => $request->user()?->id,
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Upload failed: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get files associated with a specific message
     *
     * @param Request $request
     * @param int $messageId
     * @return JsonResponse
     */
    public function getFilesByMessage(Request $request, int $messageId): JsonResponse
    {
        try {
            $user = $request->user();

            // Get the message to verify access
            $message = \App\Models\Message::find($messageId);

            if (!$message) {
                return response()->json([
                    'success' => false,
                    'message' => 'Message not found'
                ], 404);
            }

            // Check if user is sender or receiver of the message
            $isParticipant = $message->sender_id === $user->id || $message->receiver_id === $user->id;

            if (!$isParticipant && !$user->isAdmin() && !$user->isSuperAdmin()) {
                Log::warning('Unauthorized message access attempt', [
                    'message_id' => $messageId,
                    'user_id' => $user->id,
                    'sender_id' => $message->sender_id,
                    'receiver_id' => $message->receiver_id,
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Get files associated with the message
            // Files can be accessed by:
            // 1. The file owner (user_id)
            // 2. The file receiver (receiver_id)
            // 3. The message sender
            // 4. The message receiver
            $files = File::where('message_id', $messageId)
                        ->where('status', File::STATUS_COMPLETED)
                        ->get()
                        ->filter(function ($file) use ($user) {
                            return $file->canBeAccessedBy($user);
                        });

            return response()->json([
                'success' => true,
                'message' => 'Files retrieved successfully',
                'data' => [
                    'files' => array_map([$this, 'formatFileResponse'], $files->toArray()),
                    'count' => $files->count()
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Error retrieving files for message', [
                'message_id' => $messageId,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve files: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get file information
     *
     * @param File $file
     * @return JsonResponse
     */
    public function show(File $file): JsonResponse
    {
        try {
            // Check if user can access this file
            if (!$this->canAccessFile($file, request()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => $this->formatFileResponse($file)
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving file information'
            ], 500);
        }
    }

    /**
     * Get download URL for a file
     *
     * @param File $file
     * @return JsonResponse
     */
    public function download(File $file): JsonResponse
    {
        try {
            // Check if user can access this file
            if (!$this->canAccessFile($file, request()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $downloadUrl = $this->fileManagerService->getDownloadUrl($file);

            if (!$downloadUrl) {
                return response()->json([
                    'success' => false,
                    'message' => 'File not available for download'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'download_url' => $downloadUrl,
                    'filename' => $file->original_name,
                    'size' => $file->size,
                    'mime_type' => $file->mime_type
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error generating download URL'
            ], 500);
        }
    }

    /**
     * Delete a file
     *
     * @param File $file
     * @return JsonResponse
     */
    public function destroy(File $file): JsonResponse
    {
        try {
            // Check if user can delete this file
            if (!$this->canDeleteFile($file, request()->user())) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            $deleted = $this->fileManagerService->deleteFile($file);

            if ($deleted) {
                return response()->json([
                    'success' => true,
                    'message' => 'File deleted successfully'
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete file'
                ], 500);
            }

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting file'
            ], 500);
        }
    }

    /**
     * Get user's files
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $perPage = min($request->input('per_page', 15), 100);

            $query = File::where('user_id', $user->id)
                ->with('fileable')
                ->orderBy('created_at', 'desc');

            // Filter by status
            if ($request->filled('status')) {
                $query->where('status', $request->input('status'));
            }

            // Filter by storage type
            if ($request->filled('storage_type')) {
                $query->where('storage_type', $request->input('storage_type'));
            }

            // Filter by fileable type
            if ($request->filled('fileable_type')) {
                $query->where('fileable_type', $request->input('fileable_type'));
            }

            $files = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'files' => $files->items(),
                    'pagination' => [
                        'current_page' => $files->currentPage(),
                        'last_page' => $files->lastPage(),
                        'per_page' => $files->perPage(),
                        'total' => $files->total(),
                    ]
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving files'
            ], 500);
        }
    }

    /**
     * Get storage statistics
     *
     * @return JsonResponse
     */
    public function stats(): JsonResponse
    {
        try {
            $stats = $this->fileManagerService->getStorageStats();

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics'
            ], 500);
        }
    }

    /**
     * Format file response
     */
    private function formatFileResponse(File $file): array
    {
        return [
            'id' => $file->id,
            'original_name' => $file->original_name,
            'filename' => $file->filename,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'human_size' => $file->human_size,
            'extension' => $file->extension,
            'storage_type' => $file->storage_type,
            'status' => $file->status,
            'download_url' => $file->download_url,
            'expires_at' => $file->swisstransfer_expires_at?->toISOString(),
            'is_expired' => $file->isExpired(),
            'created_at' => $file->created_at->toISOString(),
            'updated_at' => $file->updated_at->toISOString(),
        ];
    }

    /**
     * Check if user can access file
     */
    private function canAccessFile(File $file, $user): bool
    {
        if (!$user) {
            return false;
        }

        return $file->canBeAccessedBy($user);
    }

    /**
     * Check if user can delete file
     */
    private function canDeleteFile(File $file, $user): bool
    {
        if (!$user) {
            return false;
        }

        return $file->canBeDeletedBy($user);
    }
}
