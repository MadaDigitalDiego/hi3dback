<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferEmailLog extends Model
{
    public $timestamps = false; // Désactive les timestamps car on utilise sent_at

    protected $fillable = [
        'offer_id',
        'user_id',
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime'
    ];

    public function offer()
    {
        return $this->belongsTo(OpenOffer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
