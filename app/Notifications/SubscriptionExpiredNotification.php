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

        $frontendUrl = rtrim((string) config('app.frontend_url'), '/');
        $url = $frontendUrl . '/subscription';

        $mail = (new MailMessage)
            ->subject('Your subscription has expired')
            ->greeting('Hello ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Your ' . ($plan->name ?? '') . ' subscription has expired.')
            ->line('Some premium features may no longer be available.');

        $mail->action('Renew my subscription', $url)
            ->salutation('Kind regards,')
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
