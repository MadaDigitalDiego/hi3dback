<?php

namespace App\Http\Controllers\Api;

use App\Models\File;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class FileDownloadController
{
    /**
     * Download a file securely
     * 
     * This endpoint serves files through a protected route instead of direct URL access.
     * It verifies user permissions before allowing download.
     *
     * @param Request $request
     * @param File $file
     * @return StreamedResponse|JsonResponse
     */
    public function download(Request $request, File $file)
    {
        try {
            $user = $request->user();

            // Check if user can download this file
            if (!$file->canBeDownloadedBy($user)) {
                Log::warning('Unauthorized file download attempt', [
                    'file_id' => $file->id,
                    'user_id' => $user->id,
                    'file_owner_id' => $file->user_id,
                    'file_receiver_id' => $file->receiver_id,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Check if file is completed
            if ($file->status !== File::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is not available for download'
                ], 404);
            }

            // Check if file is expired (for SwissTransfer files)
            if ($file->isExpired()) {
                return response()->json([
                    'success' => false,
                    'message' => 'File has expired'
                ], 410);
            }

            // Mark file as accessed
            $file->markAsAccessedBy($user);

            // Log the download
            Log::info('File downloaded', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'file_owner_id' => $file->user_id,
                'storage_type' => $file->storage_type,
                'ip' => $request->ip(),
            ]);

            // Handle local file download
            if ($file->isLocal() && $file->local_path) {
                return $this->downloadLocalFile($file);
            }

            // Handle SwissTransfer file download
            if ($file->isSwissTransfer() && $file->swisstransfer_download_url) {
                return $this->downloadSwissTransferFile($file);
            }

            return response()->json([
                'success' => false,
                'message' => 'File not found'
            ], 404);

        } catch (Exception $e) {
            Log::error('File download error', [
                'file_id' => $file->id ?? null,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Download failed'
            ], 500);
        }
    }

    /**
     * Download a local file
     *
     * @param File $file
     * @return StreamedResponse
     */
    private function downloadLocalFile(File $file): StreamedResponse
    {
        $path = $file->local_path;

        if (!Storage::disk('public')->exists($path)) {
            Log::error('Local file not found', [
                'file_id' => $file->id,
                'path' => $path,
            ]);

            abort(404, 'File not found');
        }

        return Storage::disk('public')->download(
            $path,
            $file->original_name,
            [
                'Content-Type' => $file->mime_type,
                'Content-Disposition' => 'attachment; filename="' . $file->original_name . '"',
            ]
        );
    }

    /**
     * Redirect to SwissTransfer download URL
     *
     * @param File $file
     * @return RedirectResponse
     */
    private function downloadSwissTransferFile(File $file)
    {
        // Return the download URL for client-side redirect
        return response()->json([
            'success' => true,
            'data' => [
                'download_url' => $file->swisstransfer_download_url,
                'filename' => $file->original_name,
                'size' => $file->size,
                'mime_type' => $file->mime_type,
            ]
        ]);
    }

    /**
     * Stream a file for preview (for images, PDFs, etc.)
     *
     * @param Request $request
     * @param File $file
     * @return StreamedResponse|JsonResponse
     */
    public function preview(Request $request, File $file)
    {
        try {
            $user = $request->user();

            // Check if user can access this file
            if (!$file->canBeAccessedBy($user)) {
                Log::warning('Unauthorized file preview attempt', [
                    'file_id' => $file->id,
                    'user_id' => $user->id,
                    'ip' => $request->ip(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'Access denied'
                ], 403);
            }

            // Check if file is completed
            if ($file->status !== File::STATUS_COMPLETED) {
                return response()->json([
                    'success' => false,
                    'message' => 'File is not available'
                ], 404);
            }

            // Mark file as accessed
            $file->markAsAccessedBy($user);

            // Log the preview
            Log::info('File previewed', [
                'file_id' => $file->id,
                'user_id' => $user->id,
                'ip' => $request->ip(),
            ]);

            // Only allow preview for local files
            if (!$file->isLocal() || !$file->local_path) {
                return response()->json([
                    'success' => false,
                    'message' => 'Preview not available for this file'
                ], 400);
            }

            $path = $file->local_path;

            if (!Storage::disk('public')->exists($path)) {
                abort(404, 'File not found');
            }

            return Storage::disk('public')->response(
                $path,
                $file->original_name,
                [
                    'Content-Type' => $file->mime_type,
                ]
            );

        } catch (Exception $e) {
            Log::error('File preview error', [
                'file_id' => $file->id ?? null,
                'user_id' => $request->user()?->id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Preview failed'
            ], 500);
        }
    }
}

