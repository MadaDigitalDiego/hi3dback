<?php

namespace App\Observers;

use App\Models\ServiceOffer;
use App\Jobs\IndexModelJob;
use App\Jobs\RemoveFromIndexJob;
use Illuminate\Support\Facades\Log;

class ServiceOfferObserver
{
    /**
     * Handle the ServiceOffer "created" event.
     */
    public function created(ServiceOffer $offer): void
    {
        Log::channel('meilisearch')->info('ServiceOfferObserver: created', [
            'offer_id' => $offer->id,
        ]);

        if ($offer->shouldBeSearchable()) {
            IndexModelJob::dispatch($offer, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the ServiceOffer "updated" event.
     */
    public function updated(ServiceOffer $offer): void
    {
        Log::channel('meilisearch')->info('ServiceOfferObserver: updated', [
            'offer_id' => $offer->id,
            'changes' => array_keys($offer->getDirty()),
        ]);

        // Check if status or is_private changed (affects shouldBeSearchable)
        if ($offer->isDirty(['status', 'is_private'])) {
            if ($offer->shouldBeSearchable()) {
                IndexModelJob::dispatch($offer, 'index')
                    ->onQueue('indexation')
                    ->onConnection('redis');
            } else {
                RemoveFromIndexJob::dispatch($offer)
                    ->onQueue('indexation')
                    ->onConnection('redis');
            }
        } else {
            IndexModelJob::dispatch($offer, 'update')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }

    /**
     * Handle the ServiceOffer "deleted" event.
     */
    public function deleted(ServiceOffer $offer): void
    {
        Log::channel('meilisearch')->info('ServiceOfferObserver: deleted', [
            'offer_id' => $offer->id,
        ]);

        RemoveFromIndexJob::dispatch($offer)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the ServiceOffer "forceDeleted" event.
     */
    public function forceDeleted(ServiceOffer $offer): void
    {
        Log::channel('meilisearch')->info('ServiceOfferObserver: forceDeleted', [
            'offer_id' => $offer->id,
        ]);

        RemoveFromIndexJob::dispatch($offer)
            ->onQueue('indexation')
            ->onConnection('redis');
    }

    /**
     * Handle the ServiceOffer "restored" event.
     */
    public function restored(ServiceOffer $offer): void
    {
        Log::channel('meilisearch')->info('ServiceOfferObserver: restored', [
            'offer_id' => $offer->id,
        ]);

        if ($offer->shouldBeSearchable()) {
            IndexModelJob::dispatch($offer, 'index')
                ->onQueue('indexation')
                ->onConnection('redis');
        }
    }
}

