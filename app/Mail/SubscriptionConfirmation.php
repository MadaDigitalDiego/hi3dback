<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SubscriptionConfirmation extends Mailable
{
    use Queueable, SerializesModels;

    public $user;
    public $subscription;
    public $plan;

    /**
     * Create a new message instance.
     */
    public function __construct(User $user, Subscription $subscription)
    {
        $this->user = $user;
        $this->subscription = $subscription;
        $this->plan = $subscription->plan;
    }

    /**
     * Build the message.
     */
    public function build()
    {
        return $this->subject('Subscription Confirmation')
                    ->view('emails.subscription-confirmation')
                    ->with([
                        'user' => $this->user,
                        'subscription' => $this->subscription,
                        'plan' => $this->plan,
                    ]);
    }
}