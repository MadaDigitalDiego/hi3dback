<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class DiagnoseCorsLargeFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cors:diagnose-large-files';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose CORS and large file upload configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔍 CORS and Large File Upload Diagnostic');
        $this->line('');

        // Check PHP Configuration
        $this->info('📋 PHP Configuration:');
        $this->checkPhpConfig('upload_max_filesize');
        $this->checkPhpConfig('post_max_size');
        $this->checkPhpConfig('max_execution_time');
        $this->checkPhpConfig('max_input_time');
        $this->checkPhpConfig('memory_limit');
        $this->line('');

        // Check Laravel Configuration
        $this->info('⚙️  Laravel Configuration:');
        $maxUploadSize = config('filesystems.file_management.max_upload_size', 500);
        $this->line("  FILE_MAX_UPLOAD_SIZE: {$maxUploadSize} MB");
        
        $localStorageLimit = config('filesystems.file_management.local_storage_limit', 10);
        $this->line("  FILE_LOCAL_STORAGE_LIMIT: {$localStorageLimit} MB");
        $this->line('');

        // Check CORS Configuration
        $this->info('🔐 CORS Configuration:');
        $corsConfig = config('cors');
        
        $allowedHeaders = $corsConfig['allowed_headers'] ?? [];
        $this->line('  Allowed Headers:');
        foreach ($allowedHeaders as $header) {
            $this->line("    - $header");
        }
        $this->line('');

        $exposedHeaders = $corsConfig['exposed_headers'] ?? [];
        $this->line('  Exposed Headers:');
        if (empty($exposedHeaders)) {
            $this->warn('    ⚠️  No exposed headers configured!');
        } else {
            foreach ($exposedHeaders as $header) {
                $this->line("    - $header");
            }
        }
        $this->line('');

        // Check for required headers
        $this->info('✅ Required Headers Check:');
        $requiredHeaders = [
            'Content-Length',
            'X-Content-Length',
            'X-File-Size',
            'X-File-Name',
            'X-File-Type',
        ];

        foreach ($requiredHeaders as $header) {
            if (in_array($header, $allowedHeaders)) {
                $this->line("  ✓ $header is allowed");
            } else {
                $this->error("  ✗ $header is NOT allowed");
            }
        }
        $this->line('');

        // Check Allowed Origins
        $this->info('🌐 Allowed Origins:');
        $allowedOrigins = $corsConfig['allowed_origins'] ?? [];
        foreach ($allowedOrigins as $origin) {
            $this->line("  - $origin");
        }
        $this->line('');

        // Check Origin Patterns
        $this->info('🔗 Allowed Origin Patterns:');
        $originPatterns = $corsConfig['allowed_origins_patterns'] ?? [];
        foreach ($originPatterns as $pattern) {
            $this->line("  - $pattern");
        }
        $this->line('');

        // Check Middleware
        $this->info('🔧 Middleware Check:');
        $middlewares = config('app.middleware', []);
        if (class_exists('App\Http\Middleware\ValidateLargeFileUpload')) {
            $this->line('  ✓ ValidateLargeFileUpload middleware exists');
        } else {
            $this->error('  ✗ ValidateLargeFileUpload middleware NOT found');
        }
        $this->line('');

        // Recommendations
        $this->info('💡 Recommendations:');
        
        if ($maxUploadSize < 500) {
            $this->warn("  ⚠️  FILE_MAX_UPLOAD_SIZE is only {$maxUploadSize}MB. Consider increasing to 500MB for large files.");
        } else {
            $this->line('  ✓ FILE_MAX_UPLOAD_SIZE is adequate');
        }

        if (empty($exposedHeaders)) {
            $this->warn('  ⚠️  No exposed headers configured. Add Content-Length and file-related headers.');
        } else {
            $this->line('  ✓ Exposed headers are configured');
        }

        $this->line('');
        $this->info('✨ Diagnostic complete!');
        $this->line('For more information, see: CORS_LARGE_FILES_FIX.md');
    }

    /**
     * Check a PHP configuration value
     */
    private function checkPhpConfig(string $key): void
    {
        $value = ini_get($key);
        if ($value === false) {
            $this->error("  ✗ $key: Not set");
        } else {
            $this->line("  ✓ $key: $value");
        }
    }
}

