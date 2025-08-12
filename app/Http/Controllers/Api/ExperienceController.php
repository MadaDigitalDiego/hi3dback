<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Experience;
use App\Http\Requests\ExperienceRequest; // Créez ce Form Request (voir point 3)
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class ExperienceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $profile = $user->freelanceProfile;

        if (!$profile) {
            return response()->json(['message' => 'Profil freelance non trouvé.'], 404);
        }

        $experiences = $profile->experiences()->with('projects')->get(); // Charger les projets associés
        return response()->json(['experiences' => $experiences], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(ExperienceRequest $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $user->freelanceProfile;

            if (!$profile) {
                return response()->json(['message' => 'Profil freelance non trouvé.'], 404);
            }

            $experience = new Experience($request->validated());
            $experience->freelance_profile_id = $profile->id;
            $experience->save();

            return response()->json(['experience' => $experience, 'message' => 'Expérience ajoutée avec succès.'], 201); // 201 Created
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout d\'une expérience: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'ajout de l\'expérience. Veuillez réessayer plus tard.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Experience $experience): JsonResponse
    {
        return response()->json(['experience' => $experience->load('projects')], 200); // Charger les projets associés
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ExperienceRequest $request, Experience $experience): JsonResponse
    {
        try {
            $experience->update($request->validated());
            return response()->json(['experience' => $experience, 'message' => 'Expérience mise à jour avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour de l\'expérience: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour de l\'expérience. Veuillez réessayer plus tard.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Experience $experience): JsonResponse
    {
        try {
            $experience->delete();
            return response()->json(['message' => 'Expérience supprimée avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de l\'expérience: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de l\'expérience. Veuillez réessayer plus tard.'], 500);
        }
    }
}
