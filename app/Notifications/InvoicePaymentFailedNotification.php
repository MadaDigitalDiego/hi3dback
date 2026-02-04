<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoicePaymentFailedNotification extends Notification
{
    use Queueable;

    protected Invoice $invoice;

    public function __construct(Invoice $invoice)
    {
        $this->invoice = $invoice;
    }

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $amount = $this->invoice->total ?? $this->invoice->amount;
        $currency = $this->invoice->currency ?? 'EUR';

        $mail = (new MailMessage)
            ->subject('Subscription payment failed')
            ->greeting('Hello ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('We encountered an issue while charging your subscription.')
            ->line('Amount: ' . number_format((float) $amount, 2, ',', ' ') . ' ' . $currency)
            ->line('Your subscription may be suspended if the payment is not resolved.');

        if (!empty($this->invoice->invoice_number)) {
            $mail->line('Internal invoice reference: ' . $this->invoice->invoice_number);
        }

        return $mail
            ->line('Please check or update your payment method from your account area.')
            ->salutation('Best regards,\n' . config('app.name'));
    }
}

