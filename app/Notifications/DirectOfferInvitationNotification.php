<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\OpenOffer;
use App\Models\User; // Import User model

class DirectOfferInvitationNotification extends Notification
{
    use Queueable;

    protected $openOffer;
    protected $clientUser; // Add client user

    /**
     * Create a new notification instance.
     */
    public function __construct(OpenOffer $openOffer, User $clientUser) // Accept client user in constructor
    {
        $this->openOffer = $openOffer;
        $this->clientUser = $clientUser; // Assign client user
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail', 'database']; // Add 'database' for in-app notifications as well
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        // Récupérer l'URL du frontend depuis le fichier .env
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000'); // La valeur par défaut est 'http://localhost:3000' si FRONTEND_URL n'est pas défini

        // Construire l'URL complète pour l'offre
        $url = $frontendUrl . '/dashboard/offers/' . $this->openOffer->id;
        // $url = 'http://localhost:3000/offre/' . $this->openOffer->id; // URL to the open offer details page

        return (new MailMessage)
            ->subject('Invitation Directe à un Appel d\'Offres')
            ->greeting('Bonjour ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',') // Using User model properties
            ->line('Vous avez été directement invité à rejoindre un appel d\'offres par ' . $this->clientUser->clientProfile->company_name . '.') // Use client's company name
            ->line('**Titre de l\'offre:** ' . $this->openOffer->title)
            ->line('**Description:** ' . substr($this->openOffer->description, 0, 200) . '...') // Shorten description for email
            ->action('Voir l\'Offre', $url)
            ->line('Vous pouvez consulter les détails de l\'offre et décider d\'y participer.')
            ->salutation('Cordialement,')
            ->line(config('app.name'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @param  object  $notifiable
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'open_offer_id' => $this->openOffer->id,
            'message' => 'Vous avez été invité à rejoindre l\'offre d\'appel d\'offres: ' . $this->openOffer->title . ' par ' . $this->clientUser->clientProfile->company_name . '.',
            'type' => 'offer_invitation', // Indicate notification type for frontend handling
        ];
    }
}
