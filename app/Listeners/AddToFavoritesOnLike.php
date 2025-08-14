<?php

namespace App\Listeners;

use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Overtrue\LaravelLike\Events\Liked;

class AddToFavoritesOnLike
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Liked $event): void
    {
        // Vérifier si l'objet liké est un ProfessionalProfile
        if ($event->like->likeable instanceof ProfessionalProfile) {
            $user = User::find($event->like->user_id);
            $profile = $event->like->likeable;

            if ($user && $profile) {
                // Ajouter automatiquement aux favoris
                $user->addToFavorites($profile);
            }
        }
    }
}
