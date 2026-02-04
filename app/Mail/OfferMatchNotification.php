<?php

namespace App\Mail;

use App\Models\OpenOffer;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OfferMatchNotification extends Mailable
{
    use Queueable, SerializesModels;

    public $offer;

    public function __construct(OpenOffer $offer)
    {
        $this->offer = $offer;
    }

    public function build()
    {
        return $this->subject("New offer matching your profile")
                    ->view('emails.offer_match');
    }
}
