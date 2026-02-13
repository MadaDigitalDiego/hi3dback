<?php

namespace App\Notifications;

use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvitationDeclinedNotification extends Notification
{
    use Queueable;

    protected OfferApplication $application;

    public function __construct(OfferApplication $application)
    {
        $this->application = $application;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $offer = $this->application->openOffer;
        $profile = $this->application->freelanceProfile;

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $offer
            ? $frontendUrl . '/dashboard/offers/' . $offer->id
            : $frontendUrl . '/dashboard/offers';

        $professionalName = $profile
            ? trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''))
            : 'Un professionnel';

        return (new MailMessage)
            ->subject('Invitation declined by a professional')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line($professionalName . ' has declined your invitation for the offer ' . ($offer ? '"' . $offer->title . '"' : '') . '.')
            ->action('View offer', $url)
            ->salutation('Best regards,')
            ->line(config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'open_offer_id' => $this->application->open_offer_id,
            'status' => $this->application->status,
            'message' => 'A professional has declined your invitation for this offer.',
            'type' => 'invitation_declined',
        ];
    }
}
