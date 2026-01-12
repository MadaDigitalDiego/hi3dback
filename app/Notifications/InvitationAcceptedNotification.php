<?php

namespace App\Notifications;

use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvitationAcceptedNotification extends Notification
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

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $offer
            ? $frontendUrl . '/dashboard/offers/' . $offer->id
            : $frontendUrl . '/dashboard/offers';

        $professionalName = $profile
            ? trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''))
            : 'Un professionnel';

        return (new MailMessage)
            ->subject('Invitation acceptée par un professionnel')
            ->greeting('Bonjour ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line($professionalName . ' a accepté votre invitation pour l\'offre ' . ($offer ? '"' . $offer->title . '"' : '') . '.')
            ->line('Vous pouvez maintenant discuter avec ce professionnel dans votre messagerie.')
            ->action('Voir l\'offre', $url)
            ->salutation('Cordialement,')
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
            'message' => 'Un professionnel a accepté votre invitation pour cette offre.',
            'type' => 'invitation_accepted',
        ];
    }
}
