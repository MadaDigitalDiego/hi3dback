<?php

namespace App\Notifications;

use App\Models\OfferApplication;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewApplicationNotification extends Notification
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

        $applicantName = $profile
            ? trim(($profile->first_name ?? '') . ' ' . ($profile->last_name ?? ''))
            : 'Un professionnel';

        $mail = (new MailMessage)
            ->subject('New application received for your offer')
            ->greeting('Hello ' . ($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '') . ',')
            ->line($applicantName . ' has submitted a new application for your offer ' . ($offer ? '"' . $offer->title . '"' : '') . '.');

        if (!empty($this->application->proposal)) {
            $mail->line('Excerpt from the proposal:')
                ->line(substr($this->application->proposal, 0, 200) . '...');
        }

        $mail->action('View application', $url)
            ->line('You can view the application details from your dashboard.')
            ->salutation('Best regards,')
            ->line(config('app.name'));

        return $mail;
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'application_id' => $this->application->id,
            'open_offer_id' => $this->application->open_offer_id,
            'message' => 'New application received for your offer.',
            'type' => 'application_received',
        ];
    }
}
