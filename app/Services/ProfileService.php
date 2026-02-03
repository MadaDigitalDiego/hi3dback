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
     * Now includes ALL important fields for accurate calculation.
     */
    private function calculateCompletionPercentage(Profile $profile, array $data): int
    {
        $user = $profile->user;
        
        if ($user->is_professional) {
            return $this->calculateProfessionalCompletion($profile, $data);
        } else {
            return $this->calculateClientCompletion($profile);
        }
    }
    
    /**
     * Calculate completion for professional profile with all fields.
     */
    private function calculateProfessionalCompletion(Profile $profile, array $data): int
    {
        $fields = [
            // Common profile fields
            'phone' => 5,
            'address' => 5,
            'city' => 5,
            'country' => 5,
            'bio' => 5,
            'avatar' => 5,
            
            // Professional detail fields
            'title' => 10,
            'profession' => 10,
            'description' => 10,
            'skills' => 10,
            'softwares' => 5,
            'years_of_experience' => 5,
            'hourly_rate' => 5,
            'services_offered' => 5,
            'availability_status' => 5,
            'languages' => 5,
            'portfolio' => 5,
        ];
        
        $totalWeight = array_sum($fields);
        $filledWeight = 0;
        
        // Check profile fields
        foreach (['phone', 'address', 'city', 'country', 'bio', 'avatar'] as $field) {
            $value = $profile->$field ?? null;
            if (!empty($value)) {
                $filledWeight += $fields[$field];
            }
        }
        
        // Check professional detail fields
        if ($profile->professionalDetails) {
            foreach (['title', 'profession', 'description', 'skills', 'softwares', 
                     'years_of_experience', 'hourly_rate', 'services_offered', 
                     'availability_status', 'languages', 'portfolio'] as $field) {
                $value = $profile->professionalDetails->$field ?? null;
                if ($this->isFieldFilled($value)) {
                    $filledWeight += $fields[$field];
                }
            }
        }
        
        return min(100, round(($filledWeight / $totalWeight) * 100));
    }
    
    /**
     * Check if a field is considered "filled".
     */
    private function isFieldFilled($value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }
        if (is_array($value) && count($value) === 0) {
            return false;
        }
        return true;
    }
    
    /**
     * Calculate completion for client profile.
     */
    private function calculateClientCompletion(Profile $profile): int
    {
        $fields = [
            'phone' => 10,
            'address' => 10,
            'city' => 10,
            'country' => 10,
            'bio' => 10,
            'avatar' => 10,
            'type' => 15,
            'company_name' => 15,
            'industry' => 10,
        ];
        
        $totalWeight = array_sum($fields);
        $filledWeight = 0;
        
        foreach ($fields as $field => $weight) {
            // Check profile fields first
            $value = $profile->$field ?? null;
            
            // For client-specific fields, check clientDetails
            if (in_array($field, ['type', 'company_name', 'industry']) && $profile->clientDetails) {
                $value = $profile->clientDetails->$field ?? null;
            }
            
            if ($this->isFieldFilled($value)) {
                $filledWeight += $weight;
            }
        }
        
        return min(100, round(($filledWeight / $totalWeight) * 100));
    }

    /**
     * Clear profile cache for a user
     */
    public function clearProfileCache(int $userId): void
    {
        Cache::forget("profile_{$userId}");
    }
}
