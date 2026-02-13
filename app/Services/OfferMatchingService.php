<?php

namespace App\Services;

use App\Models\OpenOffer;
use App\Models\ProfessionalProfile;

class OfferMatchingService
{
    /**
     * Find matching profiles and dispatch notifications (chunked)
     */
    public function matchAndNotify(OpenOffer $offer): int
    {
        $dispatched = 0;

        $this->getMatchingProfilesQuery($offer)
            ->chunk(100, function ($profiles) use (&$dispatched, $offer) {
                foreach ($profiles as $profile) {
                    \App\Jobs\NotifyOfferMatchJob::dispatch($offer->id, $profile->id)
                        ->onQueue('emails');
                    $dispatched++;
                }
            });

        return $dispatched;
    }

    /**
     * Return the query builder for matching profiles so callers can chunk it.
     */
    public function getMatchingProfilesQuery(OpenOffer $offer)
    {
        $filters = $this->parseFilters($offer->filters);

        $query = ProfessionalProfile::query()
            ->where('availability_status', $filters['availability_status'] ?? 'available')
            ->where('years_of_experience', '>=', $filters['experience_years'] ?? 0)
            ->when(!empty($filters['skills']), function($query) use ($filters) {
                $this->applyJsonContains($query, 'skills', $filters['skills']);
            })
            ->when(!empty($filters['languages']), function($query) use ($filters) {
                $this->applyJsonContains($query, 'languages', $filters['languages']);
            })
            ->whereDoesntHave('emailLogs', fn($q) => $q->where('offer_id', $offer->id))
            ->with('user');

        return $query;
    }

    /**
     * Trouve les profils professionnels correspondant aux critères de l'offre (retro-compat)
     */
    protected function findMatchingProfiles(OpenOffer $offer)
    {
        return $this->getMatchingProfilesQuery($offer)->get();
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
     * Envoie la notification — dispatch to job
     */
    protected function sendNotification(OpenOffer $offer, $user): void
    {
        \App\Jobs\NotifyOfferMatchJob::dispatch($offer->id, $user->id)->onQueue('emails');
    }
}