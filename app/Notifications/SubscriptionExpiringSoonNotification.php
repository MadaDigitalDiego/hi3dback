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

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $frontendUrl . '/subscription';

        $mail = (new MailMessage)
            ->subject('Your subscription is expiring soon')
            ->greeting('Hello ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Your subscription ' . ($plan->name ?? '') . ' is expiring in ' . $this->daysRemaining . ' day' . ($this->daysRemaining > 1 ? 's' : '') . '.');

        if ($endDate) {
            $mail->line('Current period end date: ' . $endDate . '.');
        }

        $mail->action('Manage my subscription', $url)
            ->salutation('Best regards,')
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
