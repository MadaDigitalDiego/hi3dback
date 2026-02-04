<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\MailMessage;

class InvoicePaidNotification extends Notification
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
            ->subject('Your subscription payment has been received')
            ->greeting('Hello ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('We confirm receipt of your subscription payment.')
            ->line('Amount: ' . number_format((float) $amount, 2, ',', ' ') . ' ' . $currency);

        if (!empty($this->invoice->invoice_number)) {
            $mail->line('Internal invoice number: ' . $this->invoice->invoice_number);
        }

        if (!empty($this->invoice->pdf_url)) {
            $mail->action('Download your invoice (PDF)', $this->invoice->pdf_url);
        }

        return $mail
            ->line('You can find the full history of your invoices in your account area.')
            ->salutation('Best regards,\n' . config('app.name'));
    }
}

