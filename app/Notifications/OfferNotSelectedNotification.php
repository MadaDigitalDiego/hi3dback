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
        $url = $frontendUrl . '/dashboard/profile';

        return (new MailMessage)
            ->subject('Update on your application')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('Thank you for applying to the following offer:')
            ->line('Offer title: ' . ($this->offer->title ?? 'Offer'))
            ->line('The client has selected another professional for this project.')
            ->line('We appreciated your profile — feel free to apply to other offers. Your next opportunity may be just around the corner.')
            ->action('View your profile', $url)
            ->salutation('Kind regards,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'application_id' => $this->application->id,
            'message' => 'The client selected another professional for this offer. Thank you for your application.',
            'type' => 'offer_not_selected',
        ];
    }
}
