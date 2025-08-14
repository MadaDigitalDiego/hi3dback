<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\UploadedFile;

class SwissTransferService
{
    private string $baseUrl;
    private string $apiUrl;
    private int $timeout;
    private int $maxFileSize;
    private bool $enabled;

    public function __construct()
    {
        $this->baseUrl = config('filesystems.swisstransfer.base_url', 'https://www.swisstransfer.com');
        $this->apiUrl = config('filesystems.swisstransfer.api_url', 'https://www.swisstransfer.com/api');
        $this->timeout = config('filesystems.swisstransfer.timeout', 300);
        $this->maxFileSize = config('filesystems.swisstransfer.max_file_size', 50000); // MB
        $this->enabled = config('filesystems.swisstransfer.enabled', true);
    }

    /**
     * Check if SwissTransfer service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Upload a file to SwissTransfer
     *
     * @param UploadedFile $file
     * @param array $options
     * @return array
     * @throws Exception
     */
    public function uploadFile(UploadedFile $file, array $options = []): array
    {
        if (!$this->isEnabled()) {
            throw new Exception('SwissTransfer service is disabled');
        }

        // Validate file size
        $fileSizeMB = $file->getSize() / 1024 / 1024;
        if ($fileSizeMB > $this->maxFileSize) {
            throw new Exception("File size ({$fileSizeMB}MB) exceeds maximum allowed size ({$this->maxFileSize}MB)");
        }

        try {
            // Step 1: Get upload session/token
            $sessionData = $this->initializeUploadSession($options);
            
            // Step 2: Upload the file
            $uploadResult = $this->performFileUpload($file, $sessionData);
            
            // Step 3: Finalize upload
            $finalResult = $this->finalizeUpload($uploadResult, $sessionData);
            
            Log::info('SwissTransfer upload successful', [
                'file_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'result' => $finalResult
            ]);

            return $finalResult;

        } catch (Exception $e) {
            Log::error('SwissTransfer upload failed', [
                'file_name' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    /**
     * Initialize upload session
     */
    private function initializeUploadSession(array $options = []): array
    {
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'Hi3D-FileManager/1.0',
            ])
            ->get($this->baseUrl);

        if (!$response->successful()) {
            throw new Exception('Failed to initialize SwissTransfer session');
        }

        // Extract CSRF token and session cookies from the response
        $cookies = $response->cookies();
        $csrfToken = $this->extractCsrfToken($response->body());

        return [
            'cookies' => $cookies,
            'csrf_token' => $csrfToken,
            'session_id' => uniqid('st_', true),
            'options' => $options
        ];
    }

    /**
     * Extract CSRF token from HTML response
     */
    private function extractCsrfToken(string $html): ?string
    {
        // Look for CSRF token in meta tags or form inputs
        if (preg_match('/<meta name="csrf-token" content="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }

        if (preg_match('/<input[^>]*name="_token"[^>]*value="([^"]+)"/', $html, $matches)) {
            return $matches[1];
        }

        // Look for token in JavaScript variables
        if (preg_match('/window\.csrfToken\s*=\s*["\']([^"\']+)["\']/', $html, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Perform the actual file upload
     */
    private function performFileUpload(UploadedFile $file, array $sessionData): array
    {
        $uploadUrl = $this->apiUrl . '/upload';
        
        $response = Http::timeout($this->timeout)
            ->withHeaders([
                'Accept' => 'application/json',
                'User-Agent' => 'Hi3D-FileManager/1.0',
                'X-CSRF-TOKEN' => $sessionData['csrf_token'] ?? '',
                'Referer' => $this->baseUrl,
            ])
            ->withCookies($sessionData['cookies'] ?? [], $this->baseUrl)
            ->attach('file', file_get_contents($file->getRealPath()), $file->getClientOriginalName())
            ->post($uploadUrl, [
                '_token' => $sessionData['csrf_token'] ?? '',
                'session_id' => $sessionData['session_id'],
                'language' => $sessionData['options']['language'] ?? 'en',
                'message' => $sessionData['options']['message'] ?? '',
                'email_sender' => $sessionData['options']['email_sender'] ?? '',
                'email_recipients' => $sessionData['options']['email_recipients'] ?? '',
                'download_limit' => $sessionData['options']['download_limit'] ?? 250,
                'expiration_days' => $sessionData['options']['expiration_days'] ?? 30,
            ]);

        if (!$response->successful()) {
            throw new Exception('File upload to SwissTransfer failed: ' . $response->body());
        }

        return $response->json();
    }

    /**
     * Finalize the upload process
     */
    private function finalizeUpload(array $uploadResult, array $sessionData): array
    {
        // Extract URLs from upload result
        $downloadUrl = $uploadResult['download_url'] ?? null;
        $deleteUrl = $uploadResult['delete_url'] ?? null;
        $shareUrl = $uploadResult['share_url'] ?? null;

        // Calculate expiration date
        $expirationDays = $sessionData['options']['expiration_days'] ?? 30;
        $expiresAt = now()->addDays($expirationDays);

        return [
            'success' => true,
            'download_url' => $downloadUrl,
            'delete_url' => $deleteUrl,
            'share_url' => $shareUrl,
            'expires_at' => $expiresAt,
            'upload_id' => $uploadResult['id'] ?? uniqid(),
            'metadata' => [
                'session_id' => $sessionData['session_id'],
                'upload_result' => $uploadResult,
                'expiration_days' => $expirationDays,
            ]
        ];
    }

    /**
     * Delete a file from SwissTransfer
     */
    public function deleteFile(string $deleteUrl): bool
    {
        try {
            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Hi3D-FileManager/1.0',
                ])
                ->delete($deleteUrl);

            return $response->successful();

        } catch (Exception $e) {
            Log::error('SwissTransfer delete failed', [
                'delete_url' => $deleteUrl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if a file is still available on SwissTransfer
     */
    public function checkFileAvailability(string $downloadUrl): bool
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Hi3D-FileManager/1.0',
                ])
                ->head($downloadUrl);

            return $response->successful();

        } catch (Exception $e) {
            Log::warning('SwissTransfer availability check failed', [
                'download_url' => $downloadUrl,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get file information from SwissTransfer
     */
    public function getFileInfo(string $downloadUrl): ?array
    {
        try {
            $response = Http::timeout(30)
                ->withHeaders([
                    'Accept' => 'application/json',
                    'User-Agent' => 'Hi3D-FileManager/1.0',
                ])
                ->get($downloadUrl . '/info');

            if ($response->successful()) {
                return $response->json();
            }

            return null;

        } catch (Exception $e) {
            Log::warning('SwissTransfer file info failed', [
                'download_url' => $downloadUrl,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
