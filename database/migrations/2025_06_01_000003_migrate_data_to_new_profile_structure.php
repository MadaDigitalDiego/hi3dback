<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use App\Models\FreelanceProfile;
use App\Models\CompanyProfile;
use App\Models\Profile;
use App\Models\ProfessionalDetail;
use App\Models\ClientDetail;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrate data from old tables to new structure
        try {
            // Process all users
            $users = User::all();
            
            foreach ($users as $user) {
                Log::info("Migrating user ID: {$user->id}, is_professional: {$user->is_professional}");
                
                // Create base profile for each user
                $profile = Profile::create([
                    'user_id' => $user->id,
                    'completion_percentage' => $user->profile_completed ? 100 : 20,
                ]);
                
                if ($user->is_professional) {
                    $this->migrateProfessionalProfile($user, $profile);
                } else {
                    $this->migrateClientProfile($user, $profile);
                }
            }
            
            Log::info('Profile data migration completed successfully.');
        } catch (\Exception $e) {
            Log::error('Error during profile data migration: ' . $e->getMessage());
            Log::error($e->getTraceAsString());
        }
    }

    /**
     * Migrate professional profile data
     */
    private function migrateProfessionalProfile(User $user, Profile $profile): void
    {
        // Try to get professional profile
        $professionalProfile = ProfessionalProfile::where('user_id', $user->id)->first();
        
        // If no professional profile, try freelance profile
        if (!$professionalProfile) {
            $freelanceProfile = FreelanceProfile::where('user_id', $user->id)->first();
            
            if ($freelanceProfile) {
                Log::info("Using freelance profile for user ID: {$user->id}");
                
                // Update base profile with common fields
                $profile->update([
                    'phone' => $freelanceProfile->phone,
                    'address' => $freelanceProfile->address,
                    'city' => $freelanceProfile->city,
                    'country' => $freelanceProfile->country,
                    'bio' => $freelanceProfile->bio,
                    'avatar' => $freelanceProfile->avatar,
                    'completion_percentage' => $freelanceProfile->completion_percentage ?? 20,
                ]);
                
                // Create professional details
                ProfessionalDetail::create([
                    'profile_id' => $profile->id,
                    'title' => $freelanceProfile->title,
                    'profession' => 'Non spécifié',
                    'years_of_experience' => $freelanceProfile->experience,
                    'hourly_rate' => 0.00,
                    'description' => '',
                    'skills' => $freelanceProfile->skills,
                    'portfolio' => $freelanceProfile->portfolio,
                    'availability_status' => $freelanceProfile->availability_status ?? 'unavailable',
                    'languages' => $freelanceProfile->languages,
                    'services_offered' => $freelanceProfile->services_offered,
                    'rating' => $freelanceProfile->rating ?? 0,
                ]);
            }
            return;
        }
        
        // Update base profile with common fields
        $profile->update([
            'phone' => $professionalProfile->phone,
            'address' => $professionalProfile->address,
            'city' => $professionalProfile->city,
            'country' => $professionalProfile->country,
            'bio' => $professionalProfile->bio,
            'avatar' => $professionalProfile->avatar,
            'social_links' => $professionalProfile->social_links,
            'completion_percentage' => $professionalProfile->completion_percentage ?? 20,
        ]);
        
        // Create professional details
        ProfessionalDetail::create([
            'profile_id' => $profile->id,
            'title' => $professionalProfile->title,
            'profession' => $professionalProfile->profession,
            'expertise' => $professionalProfile->expertise,
            'years_of_experience' => $professionalProfile->years_of_experience,
            'hourly_rate' => $professionalProfile->hourly_rate,
            'description' => $professionalProfile->description,
            'skills' => $professionalProfile->skills,
            'portfolio' => $professionalProfile->portfolio,
            'availability_status' => $professionalProfile->availability_status ?? 'unavailable',
            'languages' => $professionalProfile->languages,
            'services_offered' => $professionalProfile->services_offered,
            'rating' => $professionalProfile->rating ?? 0,
        ]);
        
        Log::info("Professional profile migrated for user ID: {$user->id}");
    }

    /**
     * Migrate client profile data
     */
    private function migrateClientProfile(User $user, Profile $profile): void
    {
        // Try to get client profile
        $clientProfile = ClientProfile::where('user_id', $user->id)->first();
        
        // If no client profile, try company profile
        if (!$clientProfile) {
            $companyProfile = CompanyProfile::where('user_id', $user->id)->first();
            
            if ($companyProfile) {
                Log::info("Using company profile for user ID: {$user->id}");
                
                // Update base profile with common fields
                $profile->update([
                    'phone' => $companyProfile->phone,
                    'address' => $companyProfile->address,
                    'city' => $companyProfile->city,
                    'country' => $companyProfile->country,
                    'bio' => $companyProfile->bio ?? '',
                    'avatar' => $companyProfile->avatar,
                    'completion_percentage' => $companyProfile->completion_percentage ?? 20,
                ]);
                
                // Create client details
                ClientDetail::create([
                    'profile_id' => $profile->id,
                    'type' => 'entreprise',
                    'company_name' => $companyProfile->company_name,
                    'company_size' => $companyProfile->company_size,
                    'industry' => $companyProfile->industry,
                    'registration_number' => $companyProfile->registration_number,
                ]);
            }
            return;
        }
        
        // Update base profile with common fields
        $profile->update([
            'phone' => $clientProfile->phone,
            'address' => $clientProfile->address,
            'city' => $clientProfile->city,
            'country' => $clientProfile->country,
            'bio' => $clientProfile->bio,
            'avatar' => $clientProfile->avatar,
            'social_links' => $clientProfile->social_links,
            'completion_percentage' => $clientProfile->completion_percentage ?? 20,
        ]);
        
        // Create client details
        ClientDetail::create([
            'profile_id' => $profile->id,
            'type' => $clientProfile->type,
            'company_name' => $clientProfile->company_name,
            'company_size' => $clientProfile->company_size,
            'industry' => $clientProfile->industry,
            'position' => $clientProfile->position,
            'website' => $clientProfile->website,
            'birth_date' => $clientProfile->birth_date,
            'preferences' => $clientProfile->preferences,
        ]);
        
        Log::info("Client profile migrated for user ID: {$user->id}");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // This migration is not reversible as it would be complex to restore the exact state
        // If needed, you should restore from a backup
        Log::warning('Attempted to reverse profile data migration - this is not supported.');
    }
};
