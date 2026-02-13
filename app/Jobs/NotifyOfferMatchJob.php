<?php

namespace App\Jobs;

use App\Models\OfferEmailLog;
use App\Models\OpenOffer;
use App\Models\ProfessionalProfile;
use App\Mail\OfferMatchNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class NotifyOfferMatchJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 120;
    public array $backoff = [60, 120, 300];

    protected int $offerId;
    protected int $profileId;

    public function __construct(int $offerId, int $profileId)
    {
        $this->offerId = $offerId;
        $this->profileId = $profileId;
    }

    public function handle(): void
    {
        try {
            $offer = OpenOffer::find($this->offerId);
            $profile = ProfessionalProfile::with('user')->find($this->profileId);

            if (!$offer || !$profile || !$profile->user) {
                Log::channel('meilisearch')->warning('NotifyOfferMatchJob: missing data', [
                    'offer_id' => $this->offerId,
                    'profile_id' => $this->profileId,
                ]);
                return;
            }

            // Ensure idempotent log
            OfferEmailLog::firstOrCreate(
                [
                    'offer_id' => $offer->id,
                    'user_id' => $profile->user->id,
                ],
                [
                    'sent_at' => now(),
                ]
            );

            // Send email (synchronous inside queued job)
            Mail::to($profile->user->email)->send(new OfferMatchNotification($offer));

            Log::channel('meilisearch')->info('NotifyOfferMatchJob sent', [
                'offer_id' => $offer->id,
                'user_id' => $profile->user->id,
            ]);

        } catch (\Throwable $e) {
            Log::channel('meilisearch')->error('NotifyOfferMatchJob failed', [
                'offer_id' => $this->offerId,
                'profile_id' => $this->profileId,
                'exception' => $e->getMessage(),
            ]);

            throw $e; // let the queue retry
        }
    }
}
