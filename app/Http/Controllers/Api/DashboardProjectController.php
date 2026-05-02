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
use App\Services\ImageStorageService;

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
                return response()->json(['message' => 'No professional profile found for this user.'], 422);
            }
            Log::info('Retrieving projects for professional profile: ' . $profile->id . ' - ' . $user->email);

            $projects = Achievement::where('professional_profile_id', $profile->id)
                ->orderBy('created_at', 'desc')
                ->get();

            Log::info('Projects retrieved: ' . $projects->count());

            return response()->json([
                'projects' => $projects,
                'message' => 'Projects retrieved successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving projects: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error retrieving projects: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            Log::info('Starting store method for project creation');
            Log::info('Received data: ' . json_encode($request->all()));

            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'required|string',
                'category' => 'required|string',
                'coverPhoto' => 'required|file|image|max:8192',
                'galleryPhotos' => 'nullable|array',
                'galleryPhotos.*' => 'file|image|max:8192',
                'youtubeLink' => 'nullable|string|max:255',
                'youtube_link' => 'nullable|string|max:2048',
                'youtubeLinks' => 'nullable|array',
                'youtubeLinks.*' => 'nullable|string|max:2048',
                'youtube_links' => 'nullable|array',
                'youtube_links.*' => 'nullable|string|max:2048',
            ]);

            if ($validator->fails()) {
                Log::error('Validation failed: ' . json_encode($validator->errors()));
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'No professional profile found for this user.'], 422);
            }
            Log::info('Authenticated user: ' . $user->id . ' - ' . $user->email);

            $projectData = $validator->validated();

            if (array_key_exists('youtubeLinks', $projectData) || array_key_exists('youtube_links', $projectData)) {
                $youtubeLinks = $projectData['youtubeLinks'] ?? $projectData['youtube_links'] ?? [];
                if (!is_array($youtubeLinks)) {
                    $youtubeLinks = [];
                }
                $youtubeLinks = array_values(array_filter($youtubeLinks, function ($value) {
                    return is_string($value) && trim($value) !== '';
                }));

                $projectData['youtube_links'] = $youtubeLinks;

                if (empty($projectData['youtube_link'] ?? null) && array_key_exists('youtubeLink', $projectData)) {
                    $projectData['youtube_link'] = $projectData['youtubeLink'] ?? null;
                }

                if (empty($projectData['youtube_link'] ?? null)) {
                    $projectData['youtube_link'] = !empty($youtubeLinks) ? $youtubeLinks[0] : null;
                }
            } elseif (array_key_exists('youtubeLink', $projectData) || array_key_exists('youtube_link', $projectData)) {
                $youtubeLink = $projectData['youtubeLink'] ?? $projectData['youtube_link'] ?? null;
                $projectData['youtube_link'] = $youtubeLink;
                $projectData['youtube_links'] = $youtubeLink ? [$youtubeLink] : [];
            }
            Log::info('Validated data: ' . json_encode($projectData));

            // Process cover photo
            $coverPhotoPath = app(ImageStorageService::class)->storeAsWebp($request->file('coverPhoto'), 'project_covers', 'public');
            $coverPhotoPath = '/storage/' . $coverPhotoPath;
            Log::info('Cover photo saved: ' . $coverPhotoPath);

            // Process gallery
            $galleryPhotoPaths = [];
            if ($request->hasFile('galleryPhotos')) {
                Log::info('Gallery files detected in request');
                foreach ($request->file('galleryPhotos') as $file) {
                    Log::info('Processing gallery file: ' . $file->getClientOriginalName());
                    $path = app(ImageStorageService::class)->storeAsWebp($file, 'project_galleries', 'public');
                    $galleryPhotoPaths[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/storage/' . $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                    Log::info('Gallery file saved: ' . $path);
                }
            }

            $newProject = [
                'professional_profile_id' => $profile->id,
                'title' => $projectData['title'],
                'description' => $projectData['description'],
                'category' => $projectData['category'],
                'cover_photo' => $coverPhotoPath,
                'gallery_photos' => $galleryPhotoPaths,
                'youtube_link' => $projectData['youtubeLink'] ?? $projectData['youtube_link'] ?? null,
                'youtube_links' => null,
                'status' => 'open',
            ];

            $youtubeLinks = $projectData['youtubeLinks'] ?? $projectData['youtube_links'] ?? null;
            if (is_array($youtubeLinks)) {
                $youtubeLinks = array_values(array_filter($youtubeLinks, function ($value) {
                    return is_string($value) && trim($value) !== '';
                }));
                $newProject['youtube_links'] = $youtubeLinks;
                if (empty($newProject['youtube_link']) && !empty($youtubeLinks)) {
                    $newProject['youtube_link'] = $youtubeLinks[0];
                }
            } elseif (!empty($newProject['youtube_link'])) {
                $newProject['youtube_links'] = [$newProject['youtube_link']];
            }

            Log::info('Creating project with data: ' . json_encode($newProject));
            $project = Achievement::create($newProject);
            Log::info('Project created successfully, ID: ' . $project->id);

            return response()->json([
                'project' => $project,
                'message' => 'Project created successfully.'
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating project: ' . $e->getMessage());
            Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json(['message' => 'Error creating project: ' . $e->getMessage()], 500);
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
                return response()->json(['message' => 'No professional profile found for this user.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            return response()->json(['project' => $project], 200);
        } catch (\Exception $e) {
            Log::error('Error retrieving project: ' . $e->getMessage());
            return response()->json(['message' => 'Project not found.'], 404);
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
                'coverPhoto' => 'sometimes|file|image|max:8192',
                'galleryPhotos' => 'nullable|array',
                'galleryPhotos.*' => 'file|image|max:8192',
                'youtubeLink' => 'nullable|string|max:255',
                'youtube_link' => 'nullable|string|max:2048',
                'youtubeLinks' => 'nullable|array',
                'youtubeLinks.*' => 'nullable|string|max:2048',
                'youtube_links' => 'nullable|array',
                'youtube_links.*' => 'nullable|string|max:2048',
            ]);

            if ($validator->fails()) {
                return response()->json(['errors' => $validator->errors()], 422);
            }

            $user = $request->user();
            $profile = $user->professionalProfile;
            if (!$profile) {
                return response()->json(['message' => 'No professional profile found for this user.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            $projectData = $validator->validated();

            if (array_key_exists('youtubeLinks', $projectData) || array_key_exists('youtube_links', $projectData)) {
                $youtubeLinks = $projectData['youtubeLinks'] ?? $projectData['youtube_links'] ?? [];
                if (!is_array($youtubeLinks)) {
                    $youtubeLinks = [];
                }
                $youtubeLinks = array_values(array_filter($youtubeLinks, function ($value) {
                    return is_string($value) && trim($value) !== '';
                }));

                $projectData['youtube_links'] = $youtubeLinks;

                if (empty($projectData['youtube_link'] ?? null) && array_key_exists('youtubeLink', $projectData)) {
                    $projectData['youtube_link'] = $projectData['youtubeLink'] ?? null;
                }

                if (empty($projectData['youtube_link'] ?? null)) {
                    $projectData['youtube_link'] = !empty($youtubeLinks) ? $youtubeLinks[0] : null;
                }
            } elseif (array_key_exists('youtubeLink', $projectData) || array_key_exists('youtube_link', $projectData)) {
                $youtubeLink = $projectData['youtubeLink'] ?? $projectData['youtube_link'] ?? null;
                $projectData['youtube_link'] = $youtubeLink;
                $projectData['youtube_links'] = $youtubeLink ? [$youtubeLink] : [];
            }

            // Update cover photo
            if ($request->hasFile('coverPhoto')) {
                // Delete old cover if it exists
                if ($project->cover_photo) {
                    $oldPath = str_replace('/storage/', '', $project->cover_photo);
                    \Storage::disk('public')->delete($oldPath);
                }
                $coverPhotoPath = app(ImageStorageService::class)->storeAsWebp($request->file('coverPhoto'), 'project_covers', 'public');
                $projectData['cover_photo'] = '/storage/' . $coverPhotoPath;
            }

            // Update gallery
            if ($request->hasFile('galleryPhotos')) {
                $galleryPhotoPaths = $project->gallery_photos ?? [];
                foreach ($request->file('galleryPhotos') as $file) {
                    $path = app(ImageStorageService::class)->storeAsWebp($file, 'project_galleries', 'public');
                    $galleryPhotoPaths[] = [
                        'name' => $file->getClientOriginalName(),
                        'path' => '/storage/' . $path,
                        'size' => $file->getSize(),
                        'type' => $file->getMimeType(),
                    ];
                }
                $projectData['gallery_photos'] = $galleryPhotoPaths;
            }

            $project->update($projectData);

            return response()->json([
                'project' => $project,
                'message' => 'Project updated successfully.'
            ], 200);
        } catch (\Exception $e) {
            \Log::error('Error updating project: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating project.'], 500);
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
                return response()->json(['message' => 'No professional profile found for this user.'], 422);
            }
            $project = Achievement::where('id', $id)
                ->where('professional_profile_id', $profile->id)
                ->firstOrFail();

            // Delete associated files
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

            return response()->json(['message' => 'Project deleted successfully.'], 200);
        } catch (\Exception $e) {
            \Log::error('Error deleting project: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting project.'], 500);
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

                // Delete the file
                if (isset($attachment['path'])) {
                    Storage::disk('public')->delete($attachment['path']);
                }

                // Delete the entry from the array
                array_splice($attachments, $attachmentIndex, 1);

                // Update the project
                $project->attachments = $attachments;
                $project->save();

                return response()->json([
                    'message' => 'Attachment deleted successfully.',
                    'project' => $project
                ], 200);
            }

            return response()->json(['message' => 'Attachment not found.'], 404);
        } catch (\Exception $e) {
            Log::error('Error deleting attachment: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting attachment.'], 500);
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

            // Filter by search
            if ($request->has('search') && !empty($request->input('search'))) {
                $search = $request->input('search');
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Filter by category
            if ($request->has('category') && $request->input('category') !== 'all') {
                $query->where('category', $request->input('category'));
            }

            // Filter by skills
            if ($request->has('skills') && !empty($request->input('skills'))) {
                $skills = explode(',', $request->input('skills'));
                foreach ($skills as $skill) {
                    $query->whereRaw("JSON_SEARCH(LOWER(skills), 'one', LOWER(?)) IS NOT NULL", ["%{$skill}%"]);
                }
            }

            // Sort results
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

            // Get filtered projects
            $projects = $query->get();

            return response()->json([
                'success' => true,
                'projects' => $projects,
                'message' => 'Filtered projects retrieved successfully.'
            ], 200);
        } catch (\Exception $e) {
            Log::error('Error filtering projects: ' . $e->getMessage());
            return response()->json(['message' => 'Error filtering projects.'], 500);
        }
    }
}
