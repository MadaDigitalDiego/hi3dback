<?php

namespace App\Notifications;

use App\Models\OpenOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;

class NewOpenOfferNotification extends Notification
{
    use Queueable;

    protected $openOffer;

    /**
     * Create a new notification instance.
     */
    public function __construct(OpenOffer $openOffer)
    {
        $this->openOffer = $openOffer;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail']; // Use 'mail' for email notifications, you can add 'database' for in-app notifications later
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000'); // La valeur par défaut est 'http://localhost:3000' si FRONTEND_URL n'est pas défini

        // Construire l'URL complète pour l'offre
        $url = $frontendUrl . '/dashboard/offers/' .   $this->openOffer->id;
        // $url = 'http://localhost:3000/offre/' . $this->openOffer->id; // URL to the open offer details page, adjust as needed

        return (new MailMessage)
            ->subject('New open offer available')
            ->greeting('Bonjour ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',') // Assuming FreelanceProfile has first_name and last_name
            ->line('Une nouvelle offre d\'appel d\'offres correspondant à votre profil est disponible.')
            ->line('**Titre de l\'offre:** ' . $this->openOffer->title)
            ->line('**Description:** ' . substr(strip_tags($this->openOffer->description), 0, 200) . '...') // Shorten description for email
            ->action('Voir l\'Offre', $url)
            ->line('Ne manquez pas cette opportunité de projet !')
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            // 'open_offer_id' => $this->openOffer->id, // Optional: Include offer ID in database notification
            'message' => 'Une nouvelle offre d\'appel d\'offres est disponible: ' . $this->openOffer->title,
        ];
    }
}
