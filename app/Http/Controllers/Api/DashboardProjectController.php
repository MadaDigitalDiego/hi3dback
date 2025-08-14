<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class DashboardProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'Aucun profil professionnel trouvé pour cet utilisateur.'], 422);
            }
            Log::info('Récupération des projets pour le profil professionnel: ' . $profile->id . ' - ' . $user->email);

            $projects = Achievement::where('professional_profile_id', $profile->id)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Projets récupérés: ' . $projects->count());

            return response()->json([
                'projects' => $projects,
                'message' => 'Projets récupérés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des projets: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Erreur lors de la récupération des projets: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Début de la méthode store pour la création de projet');
            Log::info('Données reçues: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string',
                'coverPhoto' => 'required|file|image|max:10240',
                'galleryPhotos' => 'nullable|array',
                'galleryPhotos.*' => 'file|image|max:10240',
                'youtubeLink' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                Log::error('Validation échouée: ' . json_encode($validator->errors()));
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'Aucun profil professionnel trouvé pour cet utilisateur.'], 422);
            }
            Log::info('Utilisateur authentifié: ' . $user->id . ' - ' . $user->email);

            $projectData = $validator->validated();
            Log::info('Données validées: ' . json_encode($projectData));

            // Traitement de la photo de couverture
            $coverPhotoPath = $request->file('coverPhoto')->store('project_covers', 'public');
            Log::info('Photo de couverture enregistrée: ' . $coverPhotoPath);

            // Traitement de la galerie
            $galleryPhotoPaths = [];
            if ($request->hasFile('galleryPhotos')) {
                Log::info('Fichiers de galerie détectés dans la requête');
                foreach ($request->file('galleryPhotos') as $file) {
                    Log::info('Traitement du fichier de galerie: ' . $file->getClientOriginalName());
                    $path = $file->store('project_galleries', 'public');
                    $galleryPhotoPaths[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                    Log::info('Fichier de galerie enregistré: ' . $path);
                }
            }

            $newProject = [
                'professional_profile_id' => $profile->id,
                'title' => $projectData['title'],
                'description' => $projectData['description'],
                'category' => $projectData['category'],
                'cover_photo' => $coverPhotoPath,
                'gallery_photos' => $galleryPhotoPaths,
                'youtube_link' => $projectData['youtubeLink'] ?? null,
                'status' => 'open',
            ];

            Log::info('Création du projet avec les données: ' . json_encode($newProject));
            $project = Achievement::create($newProject);
            Log::info('Projet créé avec succès, ID: ' . $project->id);

            return response()->json([
                'project' => $project,
                'message' => 'Projet créé avec succès.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la création du projet: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Erreur lors de la création du projet: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'Aucun profil professionnel trouvé pour cet utilisateur.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            return response()->json(['project' => $project], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération du projet: ' . $e->getMessage());
            return response()->json(['message' => 'Projet non trouvé.'], 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'category' => 'sometimes|required|string',
                'coverPhoto' => 'sometimes|file|image|max:10240',
                'galleryPhotos' => 'nullable|array',
                'galleryPhotos.*' => 'file|image|max:10240',
                'youtubeLink' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'Aucun profil professionnel trouvé pour cet utilisateur.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            $projectData = $validator->validated();

            // Mise à jour de la cover photo
            if ($request->hasFile('coverPhoto')) {
                // Supprimer l'ancienne cover si elle existe
                if ($project->cover_photo) {
                    \Storage::disk('public')->delete($project->cover_photo);
                }
                $coverPhotoPath = $request->file('coverPhoto')->store('project_covers', 'public');
                $projectData['cover_photo'] = $coverPhotoPath;
            }

            // Mise à jour de la galerie
            if ($request->hasFile('galleryPhotos')) {
                $galleryPhotoPaths = $project->gallery_photos ?? [];
                foreach ($request->file('galleryPhotos') as $file) {
                    $path = $file->store('project_galleries', 'public');
                    $galleryPhotoPaths[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
                $projectData['gallery_photos'] = $galleryPhotoPaths;
            }

            $project->update($projectData);

            return response()->json([
                'project' => $project,
                'message' => 'Projet mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la mise à jour du projet: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la mise à jour du projet.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, $id): JsonResponse
    {
        try {
            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'Aucun profil professionnel trouvé pour cet utilisateur.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            // Supprimer les fichiers associés
            if ($project->cover_photo) {
                \Storage::disk('public')->delete($project->cover_photo);
            }
            if ($project->gallery_photos && is_array($project->gallery_photos)) {
                foreach ($project->gallery_photos as $photo) {
                    if (isset($photo['path'])) {
                        \Storage::disk('public')->delete($photo['path']);
                    }
                }
            }

            $project->delete();

            return response()->json(['message' => 'Projet supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            \Log::error('Erreur lors de la suppression du projet: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression du projet.'], 500);
        }
    }

    /**
     * Remove an attachment from a project.
     */
    public function removeAttachment(Request $request, $id, $attachmentIndex): JsonResponse
    {
        try {
            $user = $request->user();
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $user->professionalProfile->id)
                ->firstOrFail();

            $attachments = $project->attachments ?? [];

            if (isset($attachments[$attachmentIndex])) {
                $attachment = $attachments[$attachmentIndex];

                // Supprimer le fichier
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }

                // Supprimer l'entrée du tableau
                array_splice($attachments, $attachmentIndex, 1);

                // Mettre à jour le projet
                $project->attachments = $attachments;
                $project->save();

                return response()->json([
                    'message' => 'Pièce jointe supprimée avec succès.',
                    'project' => $project
                ], 200);
            }

            return response()->json(['message' => 'Pièce jointe non trouvée.'], 404);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression de la pièce jointe: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la suppression de la pièce jointe.'], 500);
        }
    }
    /**
     * Filter projects based on various criteria.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function filter(Request $request): JsonResponse
    {
        try {
            $query = Achievement::query();

            // Filtrage par recherche
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filtrage par catégorie
            if ($request->has('category') && $request->input('category') !== 'all') {
                $query->where('category', $request->input('category'));
            }

            // Filtrage par compétences
            if ($request->has('skills') && !empty($request->input('skills'))) {
                $skills = explode(',', $request->input('skills'));
                foreach ($skills as $skill) {
                    $query->whereRaw("JSON_SEARCH(LOWER(skills), 'one', LOWER(?)) IS NOT NULL", ["%{$skill}%"]);
                }
            }

            // Tri des résultats
            if ($request->has('sort_by')) {
                $sortBy = $request->input('sort_by');
                switch ($sortBy) {
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'oldest':
                        $query->orderBy('created_at', 'asc');
                        break;
                    case 'title_asc':
                        $query->orderBy('title', 'asc');
                        break;
                    case 'title_desc':
                        $query->orderBy('title', 'desc');
                        break;
                    default:
                        $query->orderBy('created_at', 'desc');
                        break;
                }
            } else {
                $query->orderBy('created_at', 'desc');
            }

            // Récupérer les projets filtrés
            $projects = $query->get();

            return response()->json([
                'success' => true,
                'projects' => $projects,
                'message' => 'Projets filtrés récupérés avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors du filtrage des projets: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors du filtrage des projets.'], 500);
        }
    }
}