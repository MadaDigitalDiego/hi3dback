<?php

namespace App\Http\Controllers\Api;

use App\Models\Achievement;
use Illuminate\Http\Request;
// use App\Models\FreelanceProfile;
use Illuminate\Http\JsonResponse;
use App\Models\ProfessionalProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\AchievementRequest;

class AchievementController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->freelanceProfile;

        if (!$profile) {
            return response()->json(['message' => 'Freelance profile not found.'], 404);
        }

        $achievements = $profile->achievements;
        return response()->json(['achievements' => $achievements], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AchievementRequest $request): JsonResponse
    {
        try {
        $achievementData = $request->validated();

        $user = $request->user();
        $professionalProfile = $user->professionalProfile;

        if (!$professionalProfile) {
            return response()->json(['message' => 'Freelance profile not found.'], 404);
        }

        $achievement = new Achievement($achievementData);
        $achievement->professional_profile_id = $professionalProfile->id;
        $achievement->save();

        return response()->json([
            'achievement' => $achievement,
            'message' => 'Achievement/Certification added successfully.'
        ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout d\'une réalisation/certification: ' . $e->getMessage());
            return response()->json(['message' => 'Error while adding the achievement/certification. Please try again later.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Achievement $achievement): JsonResponse
    {
        try {
            // Vérifier si l'utilisateur est authentifié
            if (Auth::check()) {
                $user = Auth::user();
                $profile = $user->freelanceProfile;

                // Si l'utilisateur est le propriétaire de cette réalisation, on peut la montrer
                if ($profile && $achievement->professional_profile_id === $profile->id) {
                    return response()->json(['achievement' => $achievement], 200);
                }
            }

            // Pour les utilisateurs non authentifiés ou non propriétaires,
            // on vérifie si la réalisation appartient à un profil public
            $profile = ProfessionalProfile::find($achievement->professional_profile_id);
            if ($profile && $profile->completion_percentage >= 100) {
                return response()->json(['achievement' => $achievement], 200);
            }

            return response()->json(['message' => 'Achievement not found or access not authorized.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'affichage de la réalisation: ' . $e->getMessage());
            return response()->json(['message' => 'Error while retrieving the achievement.'], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AchievementRequest $request, Achievement $achievement): JsonResponse
    {
        try {
            $user = Auth::user();
            $profile = $user->professionalProfile;

            if (!$profile || $achievement->professional_profile_id !== $profile->id) {
                return response()->json(['message' => 'Not authorized to update this achievement.'], 403);
            }

            $achievementData = $request->validated();
            $achievement->update($achievementData);
            return response()->json(['achievement' => $achievement, 'message' => 'Achievement/Certification updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de la réalisation/certification: ' . $e->getMessage());
            return response()->json(['message' => 'Error while updating the achievement/certification. Please try again later.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Achievement $achievement): JsonResponse
    {
        try {
            $user = Auth::user();
            $profile = $user->freelanceProfile;

            if (!$profile || $achievement->professional_profile_id !== $profile->id) {
                return response()->json(['message' => 'Not authorized to delete this achievement.'], 403);
            }

            $achievement->delete();
            return response()->json(['message' => 'Achievement/Certification deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la réalisation/certification: ' . $e->getMessage());
            return response()->json(['message' => 'Error while deleting the achievement/certification. Please try again later.'], 500);
        }
    }


    public function explorerRealisation(Request $request): JsonResponse
    {
        try {
            // Récupère tous les achievements avec éventuelles relations
            // $achievements = Achievement::all();
            $achievements = Achievement::with('professionalProfile')->get();
            return response()->json([
                'success' => true,
                'achievements' => $achievements
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des réalisations: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving achievements.'
            ], 500);
        }
    }

    /**
     * Get achievements by professional ID (public endpoint).
     *
     * @param int $professionalId
     * @return JsonResponse
     */
    public function getByProfessionalId(int $professionalId): JsonResponse
    {
        try {
            $profile = ProfessionalProfile::findOrFail($professionalId);
            $achievements = $profile->achievements()->orderBy('created_at', 'desc')->get();

            $achievements = $achievements->map(function ($achievement) use ($profile) {
                $achievement->professional = $profile;
                return $achievement;
            });

            return response()->json([
                'success' => true,
                'achievements' => $achievements
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des réalisations du professionnel: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error while retrieving professional achievements.'
            ], 500);
        }
    }

    /**
     * Download a file associated with an achievement.
     *
     * @param Achievement $achievement
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\StreamedResponse|JsonResponse
     */
    public function downloadFile(Achievement $achievement, Request $request)
    {
        try {
            $fileIndex = $request->query('file_index', 0);

            // Support pour le nouveau format (files array)
            if ($achievement->files && is_array($achievement->files)) {
                if (!isset($achievement->files[$fileIndex])) {
                    return response()->json(['message' => 'File not found for this achievement.'], 404);
                }

                $file = $achievement->files[$fileIndex];

                if (!isset($file['path']) || !Storage::disk('public')->exists($file['path'])) {
                    return response()->json(['message' => 'The requested file does not exist or has been deleted.'], 404);
                }

                $fileContent = Storage::disk('public')->get($file['path']);
                $fileName = $file['original_name'] ?? basename($file['path']);
                $mimeType = $file['mime_type'] ?? 'application/octet-stream';

                return response($fileContent)
                    ->header('Content-Type', $mimeType)
                    ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            }

            // Support pour l'ancien format (file_path)
            elseif ($achievement->file_path && $fileIndex === 0) {
                if (!Storage::disk('public')->exists($achievement->file_path)) {
                    return response()->json(['message' => 'The requested file does not exist or has been deleted.'], 404);
                }

                $fileContent = Storage::disk('public')->get($achievement->file_path);
                $fileName = basename($achievement->file_path);

                return response($fileContent)
                    ->header('Content-Type', 'application/octet-stream')
                    ->header('Content-Disposition', 'attachment; filename="' . $fileName . '"');
            }

            return response()->json(['message' => 'No file found for this achievement.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors du téléchargement du fichier pour la réalisation ID ' . $achievement->id . ': ' . $e->getMessage());
            return response()->json(['message' => 'Error while downloading the file.'], 500);
        }
    }
}
