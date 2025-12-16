<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OfferAssignedNotification extends Notification
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
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/dashboard/offers/' . $this->offer->id;

        $clientUser = $this->offer->user;
        $companyName = ($clientUser && $clientUser->clientProfile && $clientUser->clientProfile->company_name)
            ? $clientUser->clientProfile->company_name
            : 'un client';

        return (new MailMessage)
            ->subject('Une offre vous a été attribuée')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('Félicitations ! Vous avez été choisi pour réaliser l\'offre d\'appel d\'offres suivante :')
            ->line('Titre de l\'offre : ' . $this->offer->title)
            ->line('Client : ' . $companyName)
            ->action('Voir les détails de l\'offre', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'application_id' => $this->application->id,
            'message' => 'Une offre vous a été attribuée.',
            'type' => 'offer_assigned',
        ];
    }
}
