<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ActiveCampaignService;
use Illuminate\Support\Facades\Http;

class TestActiveCampaign extends Command
{
    protected $signature = 'activecampaign:test {--url=} {--key=}';

    protected $description = 'Test ActiveCampaign API connection using provided URL and Key (temporary, not persisted)';

    public function handle(ActiveCampaignService $service)
    {
        $url = $this->option('url') ?? null;
        $key = $this->option('key') ?? null;

        if (! $url || ! $key) {
            $this->error('Both --url and --key are required.');
            return 1;
        }

        $this->line('Testing ActiveCampaign connection...');

        $service->setCredentials($url, $key, true);

        // Try service test first
        try {
            $ok = $service->testConnection();
        } catch (\Exception $e) {
            $this->error('Service test threw exception: ' . $e->getMessage());
            $ok = false;
        }

        if ($ok) {
            $this->info('Success: connected to ActiveCampaign API (service path).');
            return 0;
        }

        // Fallback: make a raw HTTP request to a known endpoint to inspect response
        $this->line('Service path failed; performing raw HTTP GET to /api/3/users/me for diagnostics...');

        try {
            $resp = Http::withHeaders(['Api-Token' => $key])
                        ->timeout(30)
                        ->get(rtrim($url, '/') . '/api/3/users/me');

            $this->line('HTTP Status: ' . $resp->status());
            $this->line('Response body: ' . $resp->body());
        } catch (\Exception $e) {
            $this->error('Raw HTTP request failed: ' . $e->getMessage());
            return 4;
        }

        $this->error('Failed: could not validate credentials via service path. See raw HTTP output above.');
        return 2;
    }
}
