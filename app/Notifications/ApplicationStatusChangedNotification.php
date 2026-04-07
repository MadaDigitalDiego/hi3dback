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
            $subject = 'Your application has been accepted';
            $firstLine = 'Good news! Your application has been accepted for the offer ' . ($offer ? '"' . $offer->title . '"' : '') . '.';
        } elseif ($status === 'rejected') {
            $subject = 'Your application was not selected';
            $firstLine = 'Your application for the offer ' . ($offer ? '"' . $offer->title . '"' : '') . ' was not selected.';
        } else {
            $subject = 'Your application status has been updated';
            $firstLine = 'The status of your application for the offer ' . ($offer ? '"' . $offer->title . '"' : '') . ' has been updated to: ' . $status . '.';
        }

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line($firstLine)
            ->action('View offer details', $url)
            ->salutation('Kind regards,')
            ->line(config('app.name'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'open_offer_id' => $this->application->open_offer_id,
            'status' => $this->application->status,
            'message' => 'Your application status has been updated.',
            'type' => 'application_status_changed',
        ];
    }
}
