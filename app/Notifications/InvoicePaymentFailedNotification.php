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
            ->greeting('Bonjour ' . trim(($notifiable->first_name ?? '') . ' ' . ($notifiable->last_name ?? '')) . ',')
            ->line('Nous avons rencontré un problème lors du prélèvement de votre abonnement.')
            ->line('Montant concerné : ' . number_format((float) $amount, 2, ',', ' ') . ' ' . $currency)
            ->line('Votre abonnement peut être suspendu si le paiement n\'est pas régularisé.');

        if (!empty($this->invoice->invoice_number)) {
            $mail->line('Référence interne de la facture : ' . $this->invoice->invoice_number);
        }

        return $mail
            ->line('Merci de vérifier ou mettre à jour votre moyen de paiement depuis votre espace client.')
            ->salutation('Cordialement,\n' . config('app.name'));
    }
}

