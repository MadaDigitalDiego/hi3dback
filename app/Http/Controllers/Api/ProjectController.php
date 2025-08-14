<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Experience;
use App\Http\Requests\ProjectRequest; // Créez ce Form Request (voir point 3)
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource. (Peut-être pas nécessaire pour les projets, car ils sont liés aux expériences)
     */
    // public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request, Experience $experience): JsonResponse
    {
        try {
            $projectData = $request->validated();

            // Gestion de l'upload d'image
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('project_images', 'public'); // Stockage dans storage/app/public/project_images
                $projectData['image_path'] = $path;
            }

            $project = new Project($projectData);
            $project->experience_id = $experience->id;
            $project->save();

            return response()->json(['project' => $project, 'message' => 'Projet ajouté avec succès.'], 201); // 201 Created
        } catch (\Exception $e) {
            Log::error('Erreur lors de l\'ajout d\'un projet: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de l\'ajout du projet. Veuillez réessayer plus tard.'], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project): JsonResponse
    {
        return response()->json(['project' => $project], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(ProjectRequest $request, Project $project): JsonResponse
    {
        try {
            $projectData = $request->validated();

            // Gestion de la mise à jour de l'image (si un nouveau fichier est uploadé)
            if ($request->hasFile('image')) {
                // Supprimer l'ancienne image si elle existe
                if ($project->image_path) {
                    Storage::disk('public')->delete($project->image_path);
                }
                $path = $request->file('image')->store('project_images', 'public');
                $projectData['image_path'] = $path;
            }

            $project->update($projectData);
            return response()->json(['project' => $project, 'message' => 'Projet mis à jour avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du projet: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du projet. Veuillez réessayer plus tard.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        try {
            // Supprimer l'image associée si elle existe
            if ($project->image_path) {
                Storage::disk('public')->delete($project->image_path);
            }
            $project->delete();
            return response()->json(['message' => 'Projet supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du projet: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression du projet. Veuillez réessayer plus tard.'], 500);
        }
    }
}
