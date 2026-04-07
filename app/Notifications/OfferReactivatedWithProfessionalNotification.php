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
            ->subject('An offer assigned to you has been reactivated')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('The offer "' . $this->offer->title . '" assigned to you has been reactivated by the client.')
            ->line('You remain the professional in charge of this project. Feel free to contact the client again if needed.')
            ->action('View offer', $url)
            ->salutation('Kind regards,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'status' => $this->offer->status,
            'message' => 'An offer assigned to you has been reactivated.',
            'type' => 'offer_reactivated_with_professional',
        ];
    }
}
