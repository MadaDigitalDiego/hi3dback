<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_offer_id',
        'service_id',
        'sender_id',
        'receiver_id', // Ajout de receiver_id
        // 'recipient_id', // Alias pour receiver_id pour la compatibilité
        'message_text',
        'content', // Alias pour message_text pour la compatibilité
        'is_read',
        'read_at',
    ];

    /**
     * Get the open offer that the message belongs to.
     */
    public function openOffer(): BelongsTo
    {
        return $this->belongsTo(OpenOffer::class);
    }

    /**
     * Get the user that sent the message.
     */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /**
     * Get the user that is receiving the message.
     */
    public function receiver(): BelongsTo // Relation pour le receiver
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /**
     * Alias for receiver for compatibility.
     */
    public function recipient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recipient_id');
    }

    /**
     * Get the service related to the message.
     */
    public function service(): BelongsTo
    {
        return $this->belongsTo(ServiceOffer::class, 'service_id');
    }
}
