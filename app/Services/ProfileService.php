<?php

namespace App\Services;

use App\Models\User;
use App\Models\Profile;
use App\Models\ProfessionalDetail;
use App\Models\ClientDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

class ProfileService
{
    /**
     * Cache duration in seconds (1 hour)
     */
    const CACHE_DURATION = 3600;

    /**
     * Get profile for a user
     */
    public function getProfileForUser(User $user)
    {
        $cacheKey = "profile_{$user->id}";
        
        return Cache::remember($cacheKey, self::CACHE_DURATION, function () use ($user) {
            return Profile::with(['professionalDetails', 'clientDetails'])
                ->where('user_id', $user->id)
                ->first();
        });
    }

    /**
     * Create or update a profile for a user
     */
    public function updateProfile(User $user, array $data)
    {
        try {
            // Get or create profile
            $profile = Profile::firstOrCreate(['user_id' => $user->id]);
            
            // Update common profile fields
            $profileData = array_intersect_key($data, array_flip([
                'phone', 'address', 'city', 'country', 'bio', 'social_links'
            ]));
            
            if (!empty($profileData)) {
                $profile->update($profileData);
            }
            
            // Handle avatar upload if present
            if (isset($data['avatar']) && $data['avatar']) {
                $this->handleAvatarUpload($profile, $data['avatar']);
            }
            
            // Update type-specific details
            if ($user->is_professional) {
                $this->updateProfessionalDetails($profile, $data);
            } else {
                $this->updateClientDetails($profile, $data);
            }
            
            // Calculate and update completion percentage
            $completionPercentage = $this->calculateCompletionPercentage($profile, $data);
            $profile->update(['completion_percentage' => $completionPercentage]);
            
            // Clear cache
            $this->clearProfileCache($user->id);
            
            return $profile->fresh(['professionalDetails', 'clientDetails']);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update professional details
     */
    private function updateProfessionalDetails(Profile $profile, array $data)
    {
        // Get or create professional details
        $professionalDetails = ProfessionalDetail::firstOrCreate(['profile_id' => $profile->id]);
        
        // Extract professional-specific fields
        $professionalData = array_intersect_key($data, array_flip([
            'title', 'profession', 'expertise', 'years_of_experience', 
            'hourly_rate', 'description', 'skills', 'availability_status',
            'languages', 'services_offered'
        ]));
        
        // Handle portfolio items if present
        if (isset($data['portfolio_items']) && is_array($data['portfolio_items'])) {
            $this->handlePortfolioUpload($professionalDetails, $data['portfolio_items']);
        }
        
        if (!empty($professionalData)) {
            $professionalDetails->update($professionalData);
        }
        
        return $professionalDetails;
    }

    /**
     * Update client details
     */
    private function updateClientDetails(Profile $profile, array $data)
    {
        // Get or create client details
        $clientDetails = ClientDetail::firstOrCreate(['profile_id' => $profile->id]);
        
        // Extract client-specific fields
        $clientData = array_intersect_key($data, array_flip([
            'type', 'company_name', 'company_size', 'industry', 
            'position', 'website', 'registration_number', 
            'birth_date', 'preferences'
        ]));
        
        if (!empty($clientData)) {
            $clientDetails->update($clientData);
        }
        
        return $clientDetails;
    }

    /**
     * Handle avatar upload
     */
    private function handleAvatarUpload(Profile $profile, $avatar)
    {
        // If avatar is a file upload
        if (is_object($avatar) && method_exists($avatar, 'getClientOriginalName')) {
            $filename = time() . '_' . $avatar->getClientOriginalName();
            $path = $avatar->storeAs('avatars', $filename, 'public');
            $profile->update(['avatar' => '/storage/' . $path]);
        } 
        // If avatar is a string (URL or path)
        else if (is_string($avatar)) {
            $profile->update(['avatar' => $avatar]);
        }
    }

    /**
     * Handle portfolio uploads
     */
    private function handlePortfolioUpload(ProfessionalDetail $details, array $portfolioItems)
    {
        $currentPortfolio = $details->portfolio ?? [];
        $newPortfolioItems = [];
        
        foreach ($portfolioItems as $item) {
            if (is_object($item) && method_exists($item, 'getClientOriginalName')) {
                $filename = time() . '_' . $item->getClientOriginalName();
                $path = $item->storeAs('portfolio', $filename, 'public');
                
                $newPortfolioItems[] = [
                    'id' => uniqid(),
                    'name' => $item->getClientOriginalName(),
                    'path' => '/storage/' . $path,
                    'type' => $item->getClientMimeType(),
                    'uploaded_at' => now()->toIso8601String()
                ];
            }
        }
        
        $updatedPortfolio = array_merge($currentPortfolio, $newPortfolioItems);
        $details->update(['portfolio' => $updatedPortfolio]);
    }

    /**
     * Calculate profile completion percentage
     */
    private function calculateCompletionPercentage(Profile $profile, array $data): int
    {
        $user = $profile->user;
        $requiredFields = [
            'phone', 'address', 'city', 'country', 'bio', 'avatar'
        ];
        
        $additionalFields = $user->is_professional 
            ? ['title', 'profession', 'skills', 'hourly_rate'] 
            : ['type'];
            
        $allFields = array_merge($requiredFields, $additionalFields);
        $filledFields = 0;
        
        // Check common profile fields
        foreach ($requiredFields as $field) {
            if (!empty($profile->$field)) {
                $filledFields++;
            }
        }
        
        // Check type-specific fields
        if ($user->is_professional && $profile->professionalDetails) {
            foreach (['title', 'profession', 'skills', 'hourly_rate'] as $field) {
                if (!empty($profile->professionalDetails->$field)) {
                    $filledFields++;
                }
            }
        } elseif (!$user->is_professional && $profile->clientDetails) {
            if (!empty($profile->clientDetails->type)) {
                $filledFields++;
            }
            
            // Additional fields for company type
            if ($profile->clientDetails->type === 'entreprise') {
                $companyFields = ['company_name', 'industry'];
                foreach ($companyFields as $field) {
                    if (!empty($profile->clientDetails->$field)) {
                        $filledFields++;
                    }
                }
                $allFields = array_merge($allFields, $companyFields);
            }
        }
        
        return min(100, round(($filledFields / count($allFields)) * 100));
    }

    /**
     * Clear profile cache for a user
     */
    public function clearProfileCache(int $userId): void
    {
        Cache::forget("profile_{$userId}");
    }
}
