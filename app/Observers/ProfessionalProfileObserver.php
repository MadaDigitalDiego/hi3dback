<?php

namespace App\Observers;

use App\Models\ProfessionalProfile;
use App\Jobs\IndexModelJob;
use App\Jobs\RemoveFromIndexJob;
use Illuminate\Support\Facades\Log;

class ProfessionalProfileObserver
{
    /**
     * Handle the ProfessionalProfile "created" event.
     */
    public function created(ProfessionalProfile $profile): void
    {
        Log::channel('meilisearch')->info('ProfessionalProfileObserver: created', [
            'profile_id' => $profile->id,
        ]);

        // Only queue for indexing if profile meets criteria
        if ($profile->shouldBeSearchable()) {
            IndexModelJob::dispatch($profile, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the ProfessionalProfile "updated" event.
     */
    public function updated(ProfessionalProfile $profile): void
    {
        Log::channel('meilisearch')->info('ProfessionalProfileObserver: updated', [
            'profile_id' => $profile->id,
            'changes' => array_keys($profile->getDirty()),
        ]);

        // Check if completion percentage changed (affects shouldBeSearchable)
        if ($profile->isDirty('completion_percentage')) {
            if ($profile->shouldBeSearchable()) {
                // Now searchable - add to index
                IndexModelJob::dispatch($profile, 'index')
                    ->onQueue('indexation')
                    ->onConnection('redis');
            } else {
                // No longer searchable - remove from index
                RemoveFromIndexJob::dispatch($profile)
                    ->onQueue('indexation')
                    ->onConnection('redis');
            }
        } else {
            // Regular update - reindex
            IndexModelJob::dispatch($profile, 'update')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the ProfessionalProfile "deleted" event.
     */
    public function deleted(ProfessionalProfile $profile): void
    {
        Log::channel('meilisearch')->info('ProfessionalProfileObserver: deleted', [
            'profile_id' => $profile->id,
        ]);

        RemoveFromIndexJob::dispatch($profile)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the ProfessionalProfile "forceDeleted" event.
     */
    public function forceDeleted(ProfessionalProfile $profile): void
    {
        Log::channel('meilisearch')->info('ProfessionalProfileObserver: forceDeleted', [
            'profile_id' => $profile->id,
        ]);

        RemoveFromIndexJob::dispatch($profile)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the ProfessionalProfile "restored" event.
     */
    public function restored(ProfessionalProfile $profile): void
    {
        Log::channel('meilisearch')->info('ProfessionalProfileObserver: restored', [
            'profile_id' => $profile->id,
        ]);

        if ($profile->shouldBeSearchable()) {
            IndexModelJob::dispatch($profile, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }
}

