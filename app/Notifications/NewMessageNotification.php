<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use App\Models\Message;

class NewMessageNotification extends Notification
{
    use Queueable;

    protected $message;

    /**
     * Create a new notification instance.
     */
    public function __construct(Message $message)
    {
        $this->message = $message;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $offer = $this->message->openOffer;
        $sender = $this->message->sender;
        // Récupérer l'URL du frontend depuis le fichier .env
        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');

        // Construire l'URL complète pour l'offre
        $url = $frontendUrl . '/dashboard/offers/'. $offer->id;
        // $url = 'http://localhost:3000/discussions/' . $offer->id; // Adjust the URL to the chat page

        return (new MailMessage)
            ->subject('New message received in the offer: ' . $offer->title)
            ->greeting('Hello ' . $notifiable->first_name . ' ' . $notifiable->last_name . ',')
            ->line('You have received a new message from ' . $sender->first_name . ' ' . $sender->last_name . ' regarding the offer: **' . $offer->title . '**.')
            ->line('**Message:** ' . substr($this->message->message_text, 0, 200) . '...')
            ->action('View Chat', $url)
            ->line('Reply now so you don\'t miss anything!')
            ->salutation('Best regards,')
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
            'open_offer_id' => $this->message->openOffer->id,
            'sender_id' => $this->message->sender->id,
            'sender_name' => $this->message->sender->first_name . ' ' . $this->message->sender->last_name,
            'message_text' => $this->message->message_text,
            'message' => 'New message from ' . $this->message->sender->first_name . ' ' . $this->message->sender->last_name . ' for the offer: ' . $this->message->openOffer->title,
        ];
    }
}
