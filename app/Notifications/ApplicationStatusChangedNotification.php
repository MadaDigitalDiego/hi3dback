<?php

namespace App\Notifications;

use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class ApplicationStatusChangedNotification extends Notification
{
    use Queueable;

    protected OfferApplication $application;

    public function __construct(OfferApplication $application)
    {
        $this->application = $application;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $offer = $this->application->openOffer;
        $status = $this->application->status;

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $offer
            ? $frontendUrl . '/dashboard/offers/' . $offer->id
            : $frontendUrl . '/dashboard/offers';

        if ($status === 'accepted') {
            $subject = 'Votre candidature a été acceptée';
            $firstLine = 'Bonne nouvelle ! Votre candidature a été acceptée pour l\'offre ' . ($offer ? '"' . $offer->title . '"' : '') . '.';
        } elseif ($status === 'rejected') {
            $subject = 'Votre candidature a été refusée';
            $firstLine = 'Votre candidature pour l\'offre ' . ($offer ? '"' . $offer->title . '"' : '') . ' a été refusée.';
        } else {
            $subject = 'Statut de votre candidature mis à jour';
            $firstLine = 'Le statut de votre candidature pour l\'offre ' . ($offer ? '"' . $offer->title . '"' : '') . ' a été mis à jour à : ' . $status . '.';
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line($firstLine)
            ->action('Voir les détails de l\'offre', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'open_offer_id' => $this->application->open_offer_id,
            'status' => $this->application->status,
            'message' => 'Statut de votre candidature mis à jour.',
            'type' => 'application_status_changed',
        ];
    }
}
