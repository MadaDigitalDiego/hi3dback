<?php

namespace App\Models;

use Laravel\Sanctum\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
        'stripe_customer_id',
        'is_professional', // Ajout pour distinguer le type d'utilisateur
        'email_verified_at', // Pour la vérification d'email
        'profile_completed', // Pour indiquer si le profil a été complété
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_professional' => 'boolean', // Cast en boolean
        'profile_completed' => 'boolean',
    ];

    /**
     * Get the profile associated with the user.
     *
     * Note: This method is temporarily disabled because the profiles table doesn't exist yet.
     * Use professionalProfile() or clientProfile() instead.
     */
    public function profile(): HasOne
    {
        // Temporarily return professionalProfile() to avoid errors
        return $this->professionalProfile();
    }

    /**
     * Get the professional profile associated with the user.
     * @deprecated Use profile()->professionalDetails() instead.
     */
    public function professionalProfile(): HasOne
    {
        return $this->hasOne(ProfessionalProfile::class);
    }

    /**
     * Get the client profile associated with the user.
     * @deprecated Use profile()->clientDetails() instead.
     */
    public function clientProfile(): HasOne
    {
        return $this->hasOne(ClientProfile::class);
    }

    /**
     * Get the freelance profile associated with the user.
     * Alias pour professionalProfile pour la compatibilité avec le code existant.
     */
    public function freelanceProfile(): HasOne
    {
        return $this->professionalProfile();
    }

    /**
     * Alias pour clientProfile pour la compatibilité avec le code existant.
     * @deprecated Utiliser profile()->clientDetails() à la place.
     */
    public function companyProfile(): HasOne
    {
        return $this->clientProfile();
    }

    /**
     * Get professional details through profile relationship.
     */
    public function professionalDetails()
    {
        return $this->profile ? $this->profile->professionalDetails : null;
    }

    /**
     * Get client details through profile relationship.
     */
    public function clientDetails()
    {
        return $this->profile ? $this->profile->clientDetails : null;
    }

    // Define the many-to-many relationship with OpenOffer
    public function attributedOpenOffers(): BelongsToMany // You can name this relationship as you like
    {
        return $this->belongsToMany(OpenOffer::class, 'open_offer_user'); // Second argument is the pivot table name
    }

    public function serviceOffers(): HasMany
    {
        return $this->hasMany(ServiceOffer::class);
    }

    public function offerEmailLogs()
    {
        return $this->hasMany(OfferEmailLog::class);
    }
}
