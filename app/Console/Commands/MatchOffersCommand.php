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
        $offers = OpenOffer::where('status', 'open')->get();
        $matchingService = new OfferMatchingService();

        foreach ($offers as $offer) {
            $count = $matchingService->matchAndNotify($offer);
            $this->info("Matched {$count} profiles for offer #{$offer->id}");
        }
    }
}
