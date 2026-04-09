<?php

namespace App\Listeners;

use App\Jobs\ActiveCampaignSyncContactJob;
use Illuminate\Auth\Events\Registered;

class SyncUserToActiveCampaign
{
    /**
     * Handle the event.
     */
    public function handle(Registered $event): void
    {
        $user = $event->user;

        if (! $user || ! filled($user->email)) {
            return;
        }

        $data = [];
        if (property_exists($user, 'first_name') || isset($user->first_name)) {
            $data['firstName'] = $user->first_name ?? null;
        }
        if (property_exists($user, 'last_name') || isset($user->last_name)) {
            $data['lastName'] = $user->last_name ?? null;
        }
        if (property_exists($user, 'phone') || isset($user->phone)) {
            $data['phone'] = $user->phone ?? null;
        }

        // Dispatch a queued job to sync contact (retries configured in job)
        ActiveCampaignSyncContactJob::dispatch($user->email, $data);
    }
}
