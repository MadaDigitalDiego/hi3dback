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
            ->subject('An offer you participated in has been reopened to all professionals')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('The offer "' . $this->offer->title . '" you participated in has been reactivated and is now open to all professionals.')
            ->line('Your current assignment is considered completed for this offer. You can continue applying to other opportunities on the platform.')
            ->action('View offer', $url)
            ->salutation('Kind regards,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'An offer you participated in has been reopened to all professionals.',
            'type' => 'offer_reopened_to_all',
        ];
    }
}
