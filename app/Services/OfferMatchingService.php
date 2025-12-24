<?php

namespace App\Services;

use App\Models\OpenOffer;
use App\Models\ProfessionalProfile;
use App\Models\OfferEmailLog;
use Illuminate\Support\Facades\Mail;
use App\Mail\OfferMatchNotification;

class OfferMatchingService
{
    /**
     * Trouve les profils correspondants et envoie les notifications
     */
    public function matchAndNotify(OpenOffer $offer): int
    {
        $matchingProfiles = $this->findMatchingProfiles($offer);

        foreach ($matchingProfiles as $profile) {
            $this->sendNotification($offer, $profile->user);
        }

        return $matchingProfiles->count();
    }

    /**
     * Trouve les profils professionnels correspondant aux critères de l'offre
     */
    protected function findMatchingProfiles(OpenOffer $offer)
    {
        $filters = $this->parseFilters($offer->filters);

        return ProfessionalProfile::query()
            ->where('availability_status', $filters['availability_status'] ?? 'available')
            ->where('years_of_experience', '>=', $filters['experience_years'] ?? 0)
            ->when(!empty($filters['skills']), function($query) use ($filters) {
                $this->applyJsonContains($query, 'skills', $filters['skills']);
            })
            ->when(!empty($filters['languages']), function($query) use ($filters) {
                $this->applyJsonContains($query, 'languages', $filters['languages']);
            })
            ->whereDoesntHave('emailLogs', fn($q) => $q->where('offer_id', $offer->id))
            ->with('user')
            ->get();
    }

    /**
     * Parse les filtres (support à la fois JSON et tableau)
     */
    protected function parseFilters($filters): array
    {
        if (is_string($filters)) {
            return json_decode($filters, true) ?? [];
        }

        return $filters ?? [];
    }

    /**
     * Applique une condition JSON_CONTAINS pour un champ
     */
    protected function applyJsonContains($query, string $field, array $values)
    {
        $query->where(function($q) use ($field, $values) {
            foreach ($values as $value) {
                $q->orWhereJsonContains($field, $value);
            }
        });
    }

    /**
     * Envoie la notification et log l'action
     */
    protected function sendNotification(OpenOffer $offer, $user): void
    {
        OfferEmailLog::firstOrCreate([
            'offer_id' => $offer->id,
            'user_id' => $user->id
        ]);

	        // Envoi synchrone de l'email (pas de file d'attente)
	        Mail::to($user->email)->send(new OfferMatchNotification($offer));
    }
}
