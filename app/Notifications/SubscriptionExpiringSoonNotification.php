<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class SubscriptionExpiringSoonNotification extends Notification
{
    use Queueable;

    protected Subscription $subscription;
    protected int $daysRemaining;

    public function __construct(Subscription $subscription, int $daysRemaining)
    {
        $this->subscription = $subscription;
        $this->daysRemaining = $daysRemaining;
    }

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $plan = $this->subscription->plan;
        $endDate = $this->subscription->current_period_end
            ? $this->subscription->current_period_end->format('d/m/Y')
            : null;

        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $url = $frontendUrl . '/subscription';

        $mail = (new MailMessage)
            ->subject('Votre abonnement arrive à échéance')
            ->greeting('Bonjour ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Votre abonnement ' . ($plan->name ?? '') . ' arrive à échéance dans ' . $this->daysRemaining . ' jour' . ($this->daysRemaining > 1 ? 's' : '') . '.');

        if ($endDate) {
            $mail->line('Date de fin de la période actuelle : ' . $endDate . '.');
        }

        $mail->action('Gérer mon abonnement', $url)
            ->salutation('Cordialement,')
            ->line(config('app.name'));

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'subscription_id' => $this->subscription->id,
            'plan_id' => $this->subscription->plan_id,
            'type' => 'subscription_expiring_soon',
            'days_remaining' => $this->daysRemaining,
            'current_period_end' => $this->subscription->current_period_end,
        ];
    }
}
