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
            ->greeting('Bonjour ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Nous confirmons la bonne réception du paiement de votre abonnement.')
            ->line('Montant : ' . number_format((float) $amount, 2, ',', ' ') . ' ' . $currency);

        if (!empty($this->invoice->invoice_number)) {
            $mail->line('Numéro de facture interne : ' . $this->invoice->invoice_number);
        }

        if (!empty($this->invoice->pdf_url)) {
            $mail->action('Télécharger votre facture (PDF)', $this->invoice->pdf_url);
        }

        return $mail
            ->line('Vous pouvez retrouver l\'historique complet de vos factures dans votre espace client.')
            ->salutation('Cordialement,\n' . config('app.name'));
    }
}

