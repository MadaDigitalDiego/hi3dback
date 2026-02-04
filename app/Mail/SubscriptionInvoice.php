<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;

class SubscriptionInvoice extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $invoice;
    public $subscription;
    public $attachmentPath;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Invoice $invoice, $subscription = null, $attachmentPath = null)
    {
        $this->user = $user;
        $this->invoice = $invoice;
        $this->subscription = $subscription;
        $this->attachmentPath = $attachmentPath;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        $email = $this->subject("Invoice #{$this->invoice->invoice_number} - Your subscription")
                    ->view('emails.subscription-invoice')
                    ->with([
                        'user' => $this->user,
                        'invoice' => $this->invoice,
                        'subscription' => $this->subscription,
                    ]);

        // Ajouter la pièce jointe PDF si disponible
        if ($this->attachmentPath && Storage::exists($this->attachmentPath)) {
            $email->attach(storage_path('app/' . $this->attachmentPath), [
                'as' => "facture-{$this->invoice->invoice_number}.pdf",
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}