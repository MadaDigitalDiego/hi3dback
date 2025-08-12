<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\ProfessionalProfile;

class OfferApplication extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_offer_id',
        'professional_profile_id',
        'proposal',
        'status',
    ];

    /**
     * Get the open offer that the application belongs to.
     */
    public function openOffer(): BelongsTo
    {
        return $this->belongsTo(OpenOffer::class);
    }

    /**
     * Get the professional profile that made the application.
     */
    public function freelanceProfile(): BelongsTo
    {
        return $this->belongsTo(ProfessionalProfile::class, 'professional_profile_id');
    }
}
