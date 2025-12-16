<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class OfferCompletedNotification extends Notification
{
    use Queueable;

    protected OpenOffer $offer;

    public function __construct(OpenOffer $offer)
    {
        $this->offer = $offer;
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/dashboard/offers/' . $this->offer->id;

        return (new MailMessage)
            ->subject('Votre offre a été marquée comme complétée')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('Votre offre "' . $this->offer->title . '" a été marquée comme complétée.')
            ->action('Voir l\'offre', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'Votre offre a été marquée comme complétée.',
            'type' => 'offer_completed',
        ];
    }
}
