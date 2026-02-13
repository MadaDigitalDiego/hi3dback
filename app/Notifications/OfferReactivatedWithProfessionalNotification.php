<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class OfferReactivatedWithProfessionalNotification extends Notification
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
            ->subject('Une offre à laquelle vous participez a été réactivée')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('L\'offre "' . $this->offer->title . '" à laquelle vous étiez attribué·e a été réactivée par le client.')
            ->line('Vous restez le ou la professionnel·le en charge de cette mission. N\'hésitez pas à reprendre contact avec le client si nécessaire.')
            ->action('Voir l\'offre', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'Une offre à laquelle vous étiez attribué·e a été réactivée.',
            'type' => 'offer_reactivated_with_professional',
        ];
    }
}
