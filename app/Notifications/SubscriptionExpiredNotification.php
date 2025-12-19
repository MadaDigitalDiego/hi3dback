<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiredNotification extends Notification
{
    use Queueable;

    protected Subscription $subscription;

    public function __construct(Subscription $subscription)
    {
        $this->subscription = $subscription;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/subscription';

        $mail = (new MailMessage)
            ->subject('Votre abonnement a expiré')
            ->greeting('Bonjour ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Votre abonnement ' . ($plan->name ?? '') . ' est arrivé à expiration.')
            ->line('Certaines fonctionnalités premium peuvent ne plus être disponibles.');

        $mail->action('Renouveler mon abonnement', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_id' => $this->subscription->plan_id,
            'type' => 'subscription_expired',
            'current_period_end' => $this->subscription->current_period_end,
        ];
    }
}
