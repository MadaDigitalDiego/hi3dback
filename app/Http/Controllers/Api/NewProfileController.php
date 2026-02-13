<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ProfileService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\ProfileUpdateNotification;

class NewProfileController extends Controller
{
    protected $profileService;

    public function __construct(ProfileService $profileService)
    {
        $this->profileService = $profileService;
    }

    /**
     * Get the profile of the authenticated user
     */
    public function getProfile(): JsonResponse
    {
        try {
            $user = auth()->user();
            $profile = $this->profileService->getProfileForUser($user);

            if (!$profile) {
                return response()->json([
                    'message' => 'Profile not found',
                    'profile' => null
                ], 404);
            }

            // Prepare response data
            $responseData = [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'phone' => $profile->phone,
                'address' => $profile->address,
                'city' => $profile->city,
                'country' => $profile->country,
                'bio' => $profile->bio,
                'avatar' => $profile->avatar,
                'social_links' => $profile->social_links,
                'completion_percentage' => $profile->completion_percentage,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'is_professional' => $user->is_professional,
            ];

            // Add type-specific fields
            if ($user->is_professional && $profile->professionalDetails) {
                $responseData = array_merge($responseData, [
                    'title' => $profile->professionalDetails->title,
                    'profession' => $profile->professionalDetails->profession,
                    'expertise' => $profile->professionalDetails->expertise,
                    'years_of_experience' => $profile->professionalDetails->years_of_experience,
                    'hourly_rate' => $profile->professionalDetails->hourly_rate,
                    'description' => $profile->professionalDetails->description,
                    'skills' => $profile->professionalDetails->skills,
                    'portfolio' => $profile->professionalDetails->portfolio,
                    'availability_status' => $profile->professionalDetails->availability_status,
                    'languages' => $profile->professionalDetails->languages,
                    'services_offered' => $profile->professionalDetails->services_offered,
                    'rating' => $profile->professionalDetails->rating,
                ]);
            } elseif (!$user->is_professional && $profile->clientDetails) {
                $responseData = array_merge($responseData, [
                    'type' => $profile->clientDetails->type,
                    'company_name' => $profile->clientDetails->company_name,
                    'company_size' => $profile->clientDetails->company_size,
                    'industry' => $profile->clientDetails->industry,
                    'position' => $profile->clientDetails->position,
                    'website' => $profile->clientDetails->website,
                    'birth_date' => $profile->clientDetails->birth_date,
                    'preferences' => $profile->clientDetails->preferences,
                ]);
            }

            return response()->json([
                'profile' => $responseData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error retrieving profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update the profile of the authenticated user
     */
    public function updateProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $data = $request->all();

            // Update the profile
            $profile = $this->profileService->updateProfile($user, $data);

            // Send email notification (queued)
            Mail::to($user->email)->queue(new ProfileUpdateNotification());

            // Prepare response data (similar to getProfile)
            $responseData = [
                'id' => $profile->id,
                'user_id' => $profile->user_id,
                'phone' => $profile->phone,
                'address' => $profile->address,
                'city' => $profile->city,
                'country' => $profile->country,
                'bio' => $profile->bio,
                'avatar' => $profile->avatar,
                'social_links' => $profile->social_links,
                'completion_percentage' => $profile->completion_percentage,
                'first_name' => $user->first_name,
                'last_name' => $user->last_name,
                'email' => $user->email,
                'is_professional' => $user->is_professional,
            ];

            // Add type-specific fields
            if ($user->is_professional && $profile->professionalDetails) {
                $responseData = array_merge($responseData, [
                    'title' => $profile->professionalDetails->title,
                    'profession' => $profile->professionalDetails->profession,
                    'expertise' => $profile->professionalDetails->expertise,
                    'years_of_experience' => $profile->professionalDetails->years_of_experience,
                    'hourly_rate' => $profile->professionalDetails->hourly_rate,
                    'description' => $profile->professionalDetails->description,
                    'skills' => $profile->professionalDetails->skills,
                    'portfolio' => $profile->professionalDetails->portfolio,
                    'availability_status' => $profile->professionalDetails->availability_status,
                    'languages' => $profile->professionalDetails->languages,
                    'services_offered' => $profile->professionalDetails->services_offered,
                    'rating' => $profile->professionalDetails->rating,
                ]);
            } elseif (!$user->is_professional && $profile->clientDetails) {
                $responseData = array_merge($responseData, [
                    'type' => $profile->clientDetails->type,
                    'company_name' => $profile->clientDetails->company_name,
                    'company_size' => $profile->clientDetails->company_size,
                    'industry' => $profile->clientDetails->industry,
                    'position' => $profile->clientDetails->position,
                    'website' => $profile->clientDetails->website,
                    'birth_date' => $profile->clientDetails->birth_date,
                    'preferences' => $profile->clientDetails->preferences,
                ]);
            }

            return response()->json([
                'message' => 'Profile updated successfully',
                'profile' => $responseData
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'avatar' => 'required|image|max:2048',
            ]);

            $user = auth()->user();
            $data = ['avatar' => $request->file('avatar')];

            $profile = $this->profileService->updateProfile($user, $data);

            return response()->json([
                'message' => 'Avatar uploaded successfully',
                'avatar_url' => $profile->avatar
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error uploading avatar: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error uploading avatar',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Upload portfolio items
     */
    public function uploadPortfolioItem(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'portfolio_items' => 'required|array',
                'portfolio_items.*' => 'file|max:10240',
            ]);

            $user = auth()->user();
            
            if (!$user->is_professional) {
                return response()->json([
                    'message' => 'Only professional users can upload portfolio items'
                ], 403);
            }

            $data = ['portfolio_items' => $request->file('portfolio_items')];
            $profile = $this->profileService->updateProfile($user, $data);

            return response()->json([
                'message' => 'Portfolio items uploaded successfully',
                'portfolio' => $profile->professionalDetails->portfolio
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error uploading portfolio items: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error uploading portfolio items',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete portfolio item
     */
    public function deletePortfolioItem(Request $request, string $id): JsonResponse
    {
        try {
            $user = auth()->user();
            
            if (!$user->is_professional) {
                return response()->json([
                    'message' => 'Only professional users can delete portfolio items'
                ], 403);
            }

            $profile = $this->profileService->getProfileForUser($user);
            
            if (!$profile || !$profile->professionalDetails) {
                return response()->json([
                    'message' => 'Professional profile not found'
                ], 404);
            }

            $portfolio = $profile->professionalDetails->portfolio ?? [];
            $updatedPortfolio = array_filter($portfolio, function($item) use ($id) {
                return $item['id'] !== $id;
            });

            $profile->professionalDetails->update(['portfolio' => array_values($updatedPortfolio)]);

            return response()->json([
                'message' => 'Portfolio item deleted successfully',
                'portfolio' => array_values($updatedPortfolio)
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting portfolio item: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error deleting portfolio item',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update availability status
     */
    public function updateAvailability(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'availability_status' => 'required|string|in:available,unavailable,busy',
            ]);

            $user = auth()->user();
            
            if (!$user->is_professional) {
                return response()->json([
                    'message' => 'Only professional users can update availability status'
                ], 403);
            }

            $data = ['availability_status' => $request->availability_status];
            $profile = $this->profileService->updateProfile($user, $data);

            return response()->json([
                'message' => 'Availability status updated successfully',
                'availability_status' => $profile->professionalDetails->availability_status
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error updating availability status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error updating availability status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get profile completion status
     */
    public function getCompletionStatus(): JsonResponse
    {
        try {
            $user = auth()->user();
            $profile = $this->profileService->getProfileForUser($user);

            if (!$profile) {
                return response()->json([
                    'completion_percentage' => 0,
                    'is_completed' => false
                ], 200);
            }

            return response()->json([
                'completion_percentage' => $profile->completion_percentage,
                'is_completed' => $profile->completion_percentage >= 70
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error getting completion status: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error getting completion status',
                'error' => $e->getMessage(),
                'completion_percentage' => 0,
                'is_completed' => false
            ], 500);
        }
    }

    /**
     * Complete profile (first login)
     */
    public function completeProfile(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $data = $request->all();

            // Update user's profile_completed status
            $user->update(['profile_completed' => true]);

            // Update the profile
            $profile = $this->profileService->updateProfile($user, $data);

            return response()->json([
                'message' => 'Profile completed successfully',
                'profile' => $profile
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error completing profile: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error completing profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
