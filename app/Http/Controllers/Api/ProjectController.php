<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Models\Experience;
use App\Http\Requests\ProjectRequest; // Create this Form Request (see point 3)
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource. (May not be necessary for projects as they are linked to experiences)
     */
    // public function index() {}

    /**
     * Store a newly created resource in storage.
     */
    public function store(ProjectRequest $request, Experience $experience): JsonResponse
    {
        try {
            $projectData = $request->validated();

            // Handle image upload
            if ($request->hasFile('image')) {
                $path = $request->file('image')->store('project_images', 'public'); // Store in storage/app/public/project_images
                $projectData['image_path'] = $path;
            }

            $project = new Project($projectData);
            $project->experience_id = $experience->id;
            $project->save();

            return response()->json(['project' => $project, 'message' => 'Project added successfully.'], 201); // 201 Created
        } catch (\Exception $e) {
            Log::error('Error adding project: ' . $e->getMessage());
            return response()->json(['message' => 'Error adding project. Please try again later.'], 500);
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

            // Handle image update (if a new file is uploaded)
            if ($request->hasFile('image')) {
                // Delete old image if it exists
                if ($project->image_path) {
                    Storage::disk('public')->delete($project->image_path);
                }
                $path = $request->file('image')->store('project_images', 'public');
                $projectData['image_path'] = $path;
            }

            $project->update($projectData);
            return response()->json(['project' => $project, 'message' => 'Project updated successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating project: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating project. Please try again later.'], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Project $project): JsonResponse
    {
        try {
            // Delete associated image if it exists
            if ($project->image_path) {
                Storage::disk('public')->delete($project->image_path);
            }
            $project->delete();
            return response()->json(['message' => 'Project deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting project: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting project. Please try again later.'], 500);
        }
    }
}
