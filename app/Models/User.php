<?php

namespace App\Models;

use App\Models\ServiceOffer;
use App\Models\ClientProfile;
use App\Models\OfferEmailLog;
use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\Message;
use App\Models\File;
use Laravel\Sanctum\HasApiTokens;
use App\Models\ProfessionalProfile;
use Overtrue\LaravelLike\Traits\Liker;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
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
        'stripe_payment_method_id',
        'billing_address',
        'trial_ends_at',
        'is_professional', // Ajout pour distinguer le type d'utilisateur
        'email_verified_at', // Pour la vérification d'email
        'profile_completed', // Pour indiquer si le profil a été complété
        'role', // Rôle de l'utilisateur dans l'administration
        'google2fa_secret',
        'google2fa_enabled',
        'google2fa_enabled_at',
        'is_admin', // Pour les permissions Filament
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
        'billing_address' => 'array',
        'trial_ends_at' => 'datetime',
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

    // Define the many-to-many relationship with OpenOffer (offers attributed to a professional)
    public function attributedOpenOffers(): BelongsToMany
    {
        return $this->belongsToMany(OpenOffer::class, 'open_offer_user');
    }

    /**
     * Open offers created by the user (as a client).
     */
    public function openOffers(): HasMany
    {
        return $this->hasMany(OpenOffer::class);
    }

    /**
     * Service offers created by the user (as a professional).
     */
    public function serviceOffers(): HasMany
    {
        return $this->hasMany(ServiceOffer::class);
    }

    /**
     * Applications submitted by the user through their professional profile.
     */
    public function offerApplications(): HasManyThrough
    {
        return $this->hasManyThrough(
            OfferApplication::class,
            ProfessionalProfile::class,
            'user_id',                 // Foreign key on ProfessionalProfile
            'professional_profile_id', // Foreign key on OfferApplication
            'id',                      // Local key on User
            'id'                       // Local key on ProfessionalProfile
        );
    }

    /**
     * Messages sent by the user.
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    /**
     * All files owned by the user (used for portfolio/file limits).
     */
    public function files(): HasMany
    {
        return $this->hasMany(File::class);
    }

    /**
     * Alias for files() used by legacy "portfolio_files" limits.
     */
    public function portfolioFiles(): HasMany
    {
        return $this->files();
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

    /**
     * Get the user's subscriptions.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Get the user's current subscription.
     *
     * We consider both `active` and `trialing` Stripe statuses as "current"
     * so that users on a valid trial benefit from their plan limits instead
     * of falling back to the free plan quotas.
     */
    public function currentSubscription()
    {
        return $this->subscriptions()
            ->whereIn('stripe_status', ['active', 'trialing'])
            ->latest()
            ->first();
    }

    /**
     * Get the user's invoices.
     */
    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Get the coupons used by this user.
     */
    public function coupons(): BelongsToMany
    {
        return $this->belongsToMany(Coupon::class, 'coupon_user')
            ->withPivot('subscription_id', 'discount_amount', 'used_at')
            ->withTimestamps();
    }

    /**
     * Get the user's payment methods.
     */
    public function paymentMethods(): HasMany
    {
        return $this->hasMany(PaymentMethod::class);
    }

    /**
     * Check if the user is premium (has active subscription).
     */
    public function isPremium(): bool
    {
        $subscription = $this->currentSubscription();
        return $subscription && $subscription->isActive();
    }

    /**
     * Check if the user is on trial.
     */
    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    /**
     * Get the user's plan limits as an associative array.
     *
     * This is mainly a convenience helper for APIs; the canonical
     * source of truth for a single feature is Plan::getLimit().
     */
    public function getPlanLimits(): array
    {
        $subscription = $this->currentSubscription();

        // Fallback to config-based "free" plan when there is no subscription
        if (!$subscription || !$subscription->plan) {
            return config('subscription.plans.free.limits', []);
        }

        $plan = $subscription->plan;

        $features = [
            'service_offers',
            'open_offers',
            'applications',
            'messages',
            'portfolio_files', // legacy key, still exposed in some configs
        ];

        $limits = [];
        foreach ($features as $feature) {
            // Map public feature key to Plan::getLimit key
            $planKey = match ($feature) {
                'service_offers' => 'service_offers',
                default => $feature,
            };

            $value = $plan->getLimit($planKey);
            if ($value !== null) {
                $limits[$feature] = $value;
            }
        }

        // Also merge any raw JSON limits defined on the plan for backward compatibility
        if (is_array($plan->limits)) {
            $limits = array_merge($plan->limits, $limits);
        }

        return $limits;
    }

    /**
     * Compute the limit and current usage for a given feature/action.
     *
     * @return array{limit: int|null, used: int}
     */
    public function getActionLimitAndUsage(string $action): array
    {
        // Normalise external aliases used by the API/frontend into internal keys
        $normalized = match ($action) {
            'services' => 'service_offers',
            default => $action,
        };

        // Determine the limit for this feature
        $subscription = $this->currentSubscription();
        $limit = null;

        if ($subscription && $subscription->plan) {
            $planKey = match ($normalized) {
                'service_offers' => 'service_offers',
                default => $normalized,
            };

            $limit = $subscription->plan->getLimit($planKey);
        } else {
            // No active subscription: use configured free plan limits
            $freeLimits = config('subscription.plans.free.limits', []);
            if (isset($freeLimits[$normalized])) {
                $limit = $freeLimits[$normalized];
            }
        }

        // Compute current usage for this feature

	    	// For "applications", we count:
	    	// - applications submitted by the user as a professional (pending/accepted)
	    	// - invitations sent by the user as a client (OfferApplication rows in
	    	//   "invited" status on open offers owned by this user)
	    	$applicationsUsed = $this->offerApplications()
	    	    ->whereIn('status', ['pending', 'accepted'])
	    	    ->count()
	    	    + OfferApplication::whereHas('openOffer', function ($query) {
	    	        $query->where('user_id', $this->id);
	    	    })
	    	        ->where('status', 'invited')
	    	        ->count();

        $used = match ($normalized) {
            'service_offers' => $this->serviceOffers()->count(),
            'open_offers' => $this->openOffers()->count(),
            'applications' => $applicationsUsed,
            'messages' => $this->sentMessages()->count(),
            default => 0,
        };

        return [
            'limit' => $limit,
            'used' => $used,
        ];
    }

    /**
     * Check if the user can perform an action based on plan limits.
     *
     * Supported feature keys (aliases are accepted):
     *  - service_offers / services
     *  - open_offers
     *  - applications
     *  - messages
     */
    public function canPerformAction(string $action): bool
    {
        ['limit' => $limit, 'used' => $used] = $this->getActionLimitAndUsage($action);

        // If there is no limit configured, consider the feature unlimited
        if ($limit === null) {
            return true;
        }

        return $used < $limit;
    }

}
