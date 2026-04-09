<?php

namespace App\Jobs;

use App\Services\ActiveCampaignService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActiveCampaignPerformActionsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $email;
    public array $actions;

    public int $tries = 3;

    public function __construct(string $email, array $actions = [])
    {
        $this->email = $email;
        $this->actions = $actions;
    }

    public function backoff(): array
    {
        return [60, 120, 300];
    }

    public function handle(ActiveCampaignService $service)
    {
        try {
            // Ensure contact exists
            $contactId = $service->syncContact($this->email);

            if (! $contactId) {
                Log::warning('ActiveCampaignPerformActionsJob: contact not created', ['email' => $this->email]);
                return;
            }

            // Tags
            foreach (($this->actions['tags'] ?? []) as $tag) {
                $service->addTag($contactId, $tag);
            }

            // Lists (expect list IDs)
            foreach (($this->actions['lists'] ?? []) as $listId) {
                if (is_numeric($listId)) {
                    $service->addToList((int)$contactId, (int)$listId);
                } else {
                    // mapping may provide string -> id conversion elsewhere
                }
            }

            // Automations (expect automation IDs)
            foreach (($this->actions['automations'] ?? []) as $automationId) {
                if (is_numeric($automationId)) {
                    $service->addToAutomation((int)$contactId, (int)$automationId);
                }
            }
        } catch (\Exception $e) {
            Log::error('ActiveCampaignPerformActionsJob failed: ' . $e->getMessage(), ['email' => $this->email, 'actions' => $this->actions]);
            throw $e;
        }
    }
}
