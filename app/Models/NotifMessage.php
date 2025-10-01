<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotifMessage extends Model
{
    use HasFactory;
    protected $fillable = [
        'message_id',
        'sender_id',
        'receiver_id',
        'offer_id',
        'title',
        'is_read',
        'read_at',
    ];

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id')
        ->with(['freelanceProfile', 'clientProfile']); // Charger les profils freelance et client
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    public function offer()
    {
        return $this->belongsTo(OpenOffer::class, 'offer_id');
    }

    public function message()
    {
        return $this->belongsTo(Message::class, 'message_id');
    }
}
