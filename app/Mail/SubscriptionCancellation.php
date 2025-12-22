<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionCancellation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $plan;
    public $cancellationDate;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Subscription $subscription, $cancellationDate)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->plan = $subscription->plan;
        $this->cancellationDate = $cancellationDate;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Confirmation d\'annulation de votre abonnement')
                    ->view('emails.subscription-cancellation')
                    ->with([
                        'user' => $this->user,
                        'subscription' => $this->subscription,
                        'plan' => $this->plan,
                        'cancellationDate' => $this->cancellationDate,
                    ]);
    }
}