<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferReopenedToAllNotification extends Notification
{
    use Queueable;

    protected OpenOffer $offer;

    public function __construct(OpenOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $frontendUrl . '/dashboard/offers/' . $this->offer->id;

        return (new MailMessage)
            ->subject('Une offre à laquelle vous participiez a été rouverte à tous')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('L\'offre "' . $this->offer->title . '" à laquelle vous participiez a été réactivée et est désormais ouverte à tous les professionnels.')
            ->line('Votre mission actuelle est considérée comme terminée pour cette offre. Vous pouvez toutefois continuer à postuler à d\'autres opportunités sur la plateforme.')
            ->action('Voir l\'offre', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'Une offre à laquelle vous participiez a été rouverte à tous les professionnels.',
            'type' => 'offer_reopened_to_all',
        ];
    }
}
