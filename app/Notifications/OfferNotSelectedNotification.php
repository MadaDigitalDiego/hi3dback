<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferNotSelectedNotification extends Notification
{
    use Queueable;

    protected OpenOffer $offer;
    protected OfferApplication $application;

    public function __construct(OpenOffer $offer, OfferApplication $application)
    {
        $this->offer = $offer;
        $this->application = $application;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $frontendUrl . '/dashboard/offers';

        return (new MailMessage)
            ->subject('Mise à jour de votre candidature')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('Merci d\'avoir postulé à l\'offre suivante :')
            ->line('Titre de l\'offre : ' . ($this->offer->title ?? 'Offre'))
            ->line('Le client a finalement sélectionné un autre professionnel pour ce projet.')
            ->line('Votre profil a été apprécié — n\'hésitez pas à postuler à d\'autres offres, votre prochaine opportunité est peut‑être juste là.')
            ->action('Voir les offres disponibles', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'application_id' => $this->application->id,
            'message' => 'Le client a sélectionné un autre professionnel pour cette offre. Merci pour votre candidature.',
            'type' => 'offer_not_selected',
        ];
    }
}
