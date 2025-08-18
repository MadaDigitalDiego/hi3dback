<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;

class DiagnoseCors extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cors:diagnose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Diagnose CORS configuration';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== CORS Configuration Diagnosis ===');
        $this->newLine();

        // Check CORS configuration
        $this->info('CORS Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Allowed Origins', implode(', ', Config::get('cors.allowed_origins', []))],
                ['Allowed Methods', implode(', ', Config::get('cors.allowed_methods', []))],
                ['Allowed Headers', implode(', ', Config::get('cors.allowed_headers', []))],
                ['Supports Credentials', Config::get('cors.supports_credentials') ? 'true' : 'false'],
                ['Max Age', Config::get('cors.max_age')],
            ]
        );

        $this->newLine();

        // Check Sanctum configuration
        $this->info('Sanctum Configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Stateful Domains', implode(', ', Config::get('sanctum.stateful', []))],
                ['Guard', implode(', ', Config::get('sanctum.guard', []))],
            ]
        );

        $this->newLine();

        // Check environment variables
        $this->info('Environment Variables:');
        $this->table(
            ['Variable', 'Value'],
            [
                ['APP_URL', env('APP_URL', 'Not set')],
                ['FRONTEND_URL', env('FRONTEND_URL', 'Not set')],
                ['SANCTUM_STATEFUL_DOMAINS', env('SANCTUM_STATEFUL_DOMAINS', 'Not set')],
                ['APP_ENV', env('APP_ENV', 'Not set')],
            ]
        );

        $this->newLine();

        // Check middleware
        $this->info('Middleware Configuration:');
        $kernel = app(\App\Http\Kernel::class);

        // Use reflection to access protected property
        $reflection = new \ReflectionClass($kernel);
        $middlewareProperty = $reflection->getProperty('middleware');
        $middlewareProperty->setAccessible(true);
        $globalMiddleware = $middlewareProperty->getValue($kernel);

        $corsMiddleware = array_filter($globalMiddleware, function($middleware) {
            return str_contains($middleware, 'Cors') || str_contains($middleware, 'HandleCorsOptions');
        });

        if (empty($corsMiddleware)) {
            $this->error('No CORS middleware found in global middleware stack!');
        } else {
            $this->info('CORS Middleware found:');
            foreach ($corsMiddleware as $middleware) {
                $this->line('  - ' . $middleware);
            }
        }

        $this->newLine();
        $this->info('=== Recommendations ===');
        
        // Provide recommendations
        $recommendations = [];
        
        if (!in_array('https://dev-backend.hi-3d.com', Config::get('cors.allowed_origins', []))) {
            $recommendations[] = 'Add "https://dev-backend.hi-3d.com" to allowed_origins in config/cors.php';
        }
        
        if (!str_contains(env('SANCTUM_STATEFUL_DOMAINS', ''), 'dev-backend.hi-3d.com')) {
            $recommendations[] = 'Add "dev-backend.hi-3d.com" to SANCTUM_STATEFUL_DOMAINS in .env';
        }
        
        if (empty($recommendations)) {
            $this->info('Configuration looks good!');
        } else {
            foreach ($recommendations as $recommendation) {
                $this->warn('- ' . $recommendation);
            }
        }

        return 0;
    }
}
