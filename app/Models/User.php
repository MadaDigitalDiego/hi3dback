<?php

namespace App\Models;

use App\Models\ServiceOffer;
use App\Models\ClientProfile;
use App\Models\OfferEmailLog;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ProfessionalProfile;
use Overtrue\LaravelLike\Traits\Liker;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;


class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, Liker;

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
        'role', // Rôle de l'utilisateur dans l'administration
        'google2fa_secret',
        'google2fa_enabled',
        'google2fa_enabled_at',
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
        'google2fa_enabled' => 'boolean',
        'google2fa_enabled_at' => 'datetime',
    ];

    /**
     * Boot method pour gérer les valeurs par défaut
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            // Si first_name ou last_name sont vides, les remplir avec des valeurs par défaut
            if (empty($user->first_name)) {
                $user->first_name = 'Admin';
            }
            if (empty($user->last_name)) {
                $user->last_name = 'User';
            }

            // Si le rôle n'est pas défini, définir un rôle par défaut
            if (empty($user->role)) {
                $user->role = 'user';
            }
        });
    }

    /**
     * Check if user has admin role or higher
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['admin', 'super_admin']);
    }

    /**
     * Check if user has super admin role
     */
    public function isSuperAdmin(): bool
    {
        return $this->role === 'super_admin';
    }

    /**
     * Check if user has moderator role or higher
     */
    public function isModerator(): bool
    {
        return in_array($this->role, ['moderator', 'admin', 'super_admin']);
    }

    /**
     * Get the user's full name for Filament
     */
    public function getFilamentName(): string
    {
        $firstName = $this->first_name ?: 'Admin';
        $lastName = $this->last_name ?: 'User';
        return trim($firstName . ' ' . $lastName);
    }

    /**
     * Get the user's name attribute (required by some Laravel features)
     */
    public function getNameAttribute(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

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

    /**
     * Get the user's favorites.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(UserFavorite::class);
    }

    /**
     * Get the user's favorite professional profiles.
     */
    public function favoriteProfessionalProfiles()
    {
        return $this->favorites()
            ->where('favoritable_type', ProfessionalProfile::class)
            ->with('favoritable');
    }

    /**
     * Get the user's favorite service offers.
     */
    public function favoriteServiceOffers()
    {
        return $this->favorites()
            ->where('favoritable_type', ServiceOffer::class)
            ->with('favoritable');
    }

    /**
     * Add a model to favorites (polymorphic).
     */
    public function addToFavorites($model): UserFavorite
    {
        return $this->favorites()->firstOrCreate([
            'favoritable_type' => get_class($model),
            'favoritable_id' => $model->id,
        ]);
    }

    /**
     * Remove a model from favorites (polymorphic).
     */
    public function removeFromFavorites($model): bool
    {
        return $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->id)
            ->delete() > 0;
    }

    /**
     * Check if a model is in favorites (polymorphic).
     */
    public function hasFavorite($model): bool
    {
        return $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->id)
            ->exists();
    }
}
