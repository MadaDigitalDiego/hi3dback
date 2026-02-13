<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\OpenOffer;
use App\Services\OfferMatchingService;

class MatchOffersCommand extends Command
{
    protected $signature = 'offers:match';
    protected $description = 'Match open offers with professional profiles and send notifications';

    public function handle()
    {
        $this->info('🚀 Starting offers matching (chunked & queued)...');
        $matchingService = new OfferMatchingService();

        // Process offers in small chunks to avoid large memory spikes
        OpenOffer::where('status', 'open')->chunkById(10, function ($offers) use ($matchingService) {
            foreach ($offers as $offer) {
                $matched = 0;

                // Use a query to iterate matching profiles in chunks and dispatch notification jobs
                $matchingService->getMatchingProfilesQuery($offer)
                    ->chunk(100, function ($profiles) use ($offer, &$matched) {
                        foreach ($profiles as $profile) {
                            \App\Jobs\NotifyOfferMatchJob::dispatch($offer->id, $profile->id)
                                ->onQueue('emails');
                            $matched++;
                        }
                    });

                $this->info("Dispatched {$matched} notifications for offer #{$offer->id}");
            }
        });
    }
}
