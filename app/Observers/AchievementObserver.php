<?php

namespace App\Observers;

use App\Models\Achievement;
use App\Jobs\IndexModelJob;
use App\Jobs\RemoveFromIndexJob;
use Illuminate\Support\Facades\Log;

class AchievementObserver
{
    /**
     * Handle the Achievement "created" event.
     */
    public function created(Achievement $achievement): void
    {
        Log::channel('meilisearch')->info('AchievementObserver: created', [
            'achievement_id' => $achievement->id,
        ]);

        if ($achievement->shouldBeSearchable()) {
            IndexModelJob::dispatch($achievement, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the Achievement "updated" event.
     */
    public function updated(Achievement $achievement): void
    {
        Log::channel('meilisearch')->info('AchievementObserver: updated', [
            'achievement_id' => $achievement->id,
            'changes' => array_keys($achievement->getDirty()),
        ]);

        // Check if title or category changed (affects shouldBeSearchable)
        if ($achievement->isDirty(['title', 'category'])) {
            if ($achievement->shouldBeSearchable()) {
                IndexModelJob::dispatch($achievement, 'index')
                    ->onQueue('indexation')
                    ->onConnection('redis');
            } else {
                RemoveFromIndexJob::dispatch($achievement)
                    ->onQueue('indexation')
                    ->onConnection('redis');
            }
        } else {
            IndexModelJob::dispatch($achievement, 'update')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the Achievement "deleted" event.
     */
    public function deleted(Achievement $achievement): void
    {
        Log::channel('meilisearch')->info('AchievementObserver: deleted', [
            'achievement_id' => $achievement->id,
        ]);

        RemoveFromIndexJob::dispatch($achievement)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the Achievement "forceDeleted" event.
     */
    public function forceDeleted(Achievement $achievement): void
    {
        Log::channel('meilisearch')->info('AchievementObserver: forceDeleted', [
            'achievement_id' => $achievement->id,
        ]);

        RemoveFromIndexJob::dispatch($achievement)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the Achievement "restored" event.
     */
    public function restored(Achievement $achievement): void
    {
        Log::channel('meilisearch')->info('AchievementObserver: restored', [
            'achievement_id' => $achievement->id,
        ]);

        if ($achievement->shouldBeSearchable()) {
            IndexModelJob::dispatch($achievement, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }
}

