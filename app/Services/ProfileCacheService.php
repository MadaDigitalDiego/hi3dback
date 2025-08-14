<?php

namespace App\Services;

use App\Models\ClientProfile;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Support\Facades\Cache;

class ProfileCacheService
{
    /**
     * Durée de mise en cache des profils en secondes (1 heure)
     */
    const CACHE_DURATION = 3600;

    /**
     * Récupérer un profil professionnel depuis le cache ou la base de données
     *
     * @param int $userId
     * @return ProfessionalProfile|null
     */
    public function getProfessionalProfile(int $userId): ?ProfessionalProfile
    {
        $cacheKey = "professional_profile_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return ProfessionalProfile::where('user_id', $userId)->first();
        });
    }

    /**
     * Récupérer un profil client depuis le cache ou la base de données
     *
     * @param int $userId
     * @return ClientProfile|null
     */
    public function getClientProfile(int $userId): ?ClientProfile
    {
        $cacheKey = "client_profile_{$userId}";

        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($userId) {
            return ClientProfile::where('user_id', $userId)->first();
        });
    }

    /**
     * Récupérer le profil approprié (professionnel ou client) pour un utilisateur
     *
     * @param User $user
     * @return ProfessionalProfile|ClientProfile|null
     */
    public function getProfileForUser(User $user)
    {
        if ($user->is_professional) {
            return $this->getProfessionalProfile($user->id);
        } else {
            return $this->getClientProfile($user->id);
        }
    }

    /**
     * Mettre à jour le cache pour un profil professionnel
     *
     * @param ProfessionalProfile $profile
     * @return void
     */
    public function updateProfessionalProfileCache(ProfessionalProfile $profile): void
    {
        $cacheKey = "professional_profile_{$profile->user_id}";
        Cache::put($cacheKey, $profile, self::CACHE_DURATION);
    }

    /**
     * Mettre à jour le cache pour un profil client
     *
     * @param ClientProfile $profile
     * @return void
     */
    public function updateClientProfileCache(ClientProfile $profile): void
    {
        $cacheKey = "client_profile_{$profile->user_id}";
        Cache::put($cacheKey, $profile, self::CACHE_DURATION);
    }

    /**
     * Supprimer le cache pour un profil professionnel
     *
     * @param int $userId
     * @return void
     */
    public function clearProfessionalProfileCache(int $userId): void
    {
        $cacheKey = "professional_profile_{$userId}";
        Cache::forget($cacheKey);
    }

    /**
     * Supprimer le cache pour un profil client
     *
     * @param int $userId
     * @return void
     */
    public function clearClientProfileCache(int $userId): void
    {
        $cacheKey = "client_profile_{$userId}";
        Cache::forget($cacheKey);
    }

    /**
     * Supprimer le cache pour tous les types de profils d'un utilisateur
     *
     * @param int $userId
     * @return void
     */
    public function clearAllProfileCaches(int $userId): void
    {
        $this->clearProfessionalProfileCache($userId);
        $this->clearClientProfileCache($userId);
    }
}
