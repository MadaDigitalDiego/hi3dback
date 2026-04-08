<?php

namespace App\Jobs;

use App\Services\ActiveCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActiveCampaignSyncContactJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $email;
    public array $data;

    public int $tries = 3;

    public function __construct(string $email, array $data = [])
    {
        $this->email = $email;
        $this->data = $data;
    }

    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function handle(ActiveCampaignService $service)
    {
        try {
            // Use the service to sync contact; the service handles duplicates via email
            $contactId = $service->syncContact($this->email, $this->data);

            if (!$contactId) {
                Log::warning('ActiveCampaignSyncContactJob: no contact id returned', ['email' => $this->email]);
            }
        } catch (\Exception $e) {
            Log::error('ActiveCampaignSyncContactJob failed: ' . $e->getMessage(), ['email' => $this->email]);
            throw $e;
        }
    }
}
