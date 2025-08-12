<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DashboardProject;
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
     * Constructor to ensure the table exists
     */
    public function __construct()
    {
        $this->ensureTableExists();
    }

    /**
     * Ensure the dashboard_projects table exists
     */
    private function ensureTableExists(): void
    {
        if (!Schema::hasTable('dashboard_projects')) {
            Schema::create('dashboard_projects', function (Blueprint $table) {
                $table->id();
                $table->foreignId('user_id')->constrained()->onDelete('cascade');
                $table->string('title');
                $table->text('description');
                $table->string('category');
                $table->string('budget');
                $table->date('deadline');
                $table->json('skills')->nullable();
                $table->json('attachments')->nullable();
                $table->enum('status', ['draft', 'open', 'in_progress', 'completed', 'cancelled'])->default('draft');
                $table->timestamps();
            });

            Log::info('Table dashboard_projects created successfully');
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $user = $request->user();
            Log::info('Récupération des projets pour l\'utilisateur: ' . $user->id . ' - ' . $user->email);

            $projects = DashboardProject::where('user_id', $user->id)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Projets récupérés: ' . $projects->count());

            // Transformer les données JSON en tableaux PHP
            $projects = $projects->map(function ($project) {
                if (is_string($project->skills) && !empty($project->skills)) {
                    $project->skills = json_decode($project->skills, true);
                }
                if (is_string($project->attachments) && !empty($project->attachments)) {
                    $project->attachments = json_decode($project->attachments, true);
                }
                return $project;
            });

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
                'budget' => 'required|string',
                'deadline' => 'required|date',
                'skills' => 'nullable|array',
                'skills.*' => 'string',
                'attachments' => 'nullable|array',
                'attachments.*' => 'file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10240',
            ]);

            if ($validator->fails()) {
                Log::error('Validation échouée: ' . json_encode($validator->errors()));
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            Log::info('Utilisateur authentifié: ' . $user->id . ' - ' . $user->email);

            $projectData = $validator->validated();
            Log::info('Données validées: ' . json_encode($projectData));

            // Traitement des pièces jointes
            $attachments = [];
            if ($request->hasFile('attachments')) {
                Log::info('Fichiers détectés dans la requête');
                foreach ($request->file('attachments') as $file) {
                    Log::info('Traitement du fichier: ' . $file->getClientOriginalName());
                    $path = $file->store('project_attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                    Log::info('Fichier enregistré: ' . $path);
                }
            } else {
                Log::info('Aucun fichier détecté dans la requête');
            }

            $projectData['attachments'] = $attachments;
            $projectData['user_id'] = $user->id;
            $projectData['status'] = 'open'; // Par défaut, le projet est ouvert

            Log::info('Création du projet avec les données: ' . json_encode($projectData));
            $project = DashboardProject::create($projectData);
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
            $project = DashboardProject::where('id', $id)
                ->where('user_id', $user->id)
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
                'budget' => 'sometimes|required|string',
                'deadline' => 'sometimes|required|date',
                'skills' => 'nullable|array',
                'skills.*' => 'string',
                'status' => 'sometimes|required|in:draft,open,in_progress,completed,cancelled',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $project = DashboardProject::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            $projectData = $validator->validated();

            // Traitement des nouvelles pièces jointes
            if ($request->hasFile('new_attachments')) {
                $attachments = $project->attachments ?? [];
                foreach ($request->file('new_attachments') as $file) {
                    $path = $file->store('project_attachments', 'public');
                    $attachments[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
                $projectData['attachments'] = $attachments;
            }

            $project->update($projectData);

            return response()->json([
                'project' => $project,
                'message' => 'Projet mis à jour avec succès.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la mise à jour du projet: ' . $e->getMessage());
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
            $project = DashboardProject::where('id', $id)
                ->where('user_id', $user->id)
                ->firstOrFail();

            // Supprimer les pièces jointes
            if (!empty($project->attachments)) {
                foreach ($project->attachments as $attachment) {
                    if (isset($attachment['path'])) {
                        Storage::disk('public')->delete($attachment['path']);
                    }
                }
            }

            $project->delete();

            return response()->json(['message' => 'Projet supprimé avec succès.'], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la suppression du projet: ' . $e->getMessage());
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
            $project = DashboardProject::where('id', $id)
                ->where('user_id', $user->id)
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
            $query = DashboardProject::query();

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

            // Transformer les données JSON en tableaux PHP
            $projects = $projects->map(function ($project) {
                if (is_string($project->skills) && !empty($project->skills)) {
                    $project->skills = json_decode($project->skills, true);
                }
                if (is_string($project->attachments) && !empty($project->attachments)) {
                    $project->attachments = json_decode($project->attachments, true);
                }
                return $project;
            });

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