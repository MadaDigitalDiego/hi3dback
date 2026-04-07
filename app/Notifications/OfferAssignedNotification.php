<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\HtmlString;

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
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $frontendUrl . '/dashboard/offers/' . $this->offer->id;

        $clientUser = $this->offer->user;
        $companyName = ($clientUser && $clientUser->clientProfile && $clientUser->clientProfile->company_name)
            ? $clientUser->clientProfile->company_name
            : 'a client';

        return (new MailMessage)
            ->subject('An offer has been assigned to you')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line('Congratulations! You have been selected for the following offer:')
            ->line(new HtmlString('<strong>Offer title:</strong><br>' . e($this->offer->title)))
            ->line(new HtmlString('<strong>Client:</strong><br>' . e($companyName)))
            ->action('View offer details', $url)
            ->salutation('Kind regards,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->offer->id,
            'application_id' => $this->application->id,
            'message' => 'An offer has been assigned to you.',
            'type' => 'offer_assigned',
        ];
    }
}
