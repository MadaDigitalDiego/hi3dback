<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class CategoryController extends Controller
{
    /**
     * Récupérer toutes les catégories
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $categories = Category::active()
                ->main()
                ->orderBy('order')
                ->select(['id', 'value', 'name', 'slug', 'description', 'image_url', 'count'])
                ->get();

            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des catégories: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des catégories.'], 500);
        }
    }

    /**
     * Récupérer une catégorie spécifique
     *
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $category = Category::active()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('order');
                }])
                ->find($id);

            if (!$category) {
                return response()->json(['message' => 'Catégorie non trouvée.'], 404);
            }

            return response()->json(['category' => $category], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la catégorie: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération de la catégorie.'], 500);
        }
    }

    /**
     * Récupérer les sous-catégories d'une catégorie parente
     *
     * @param string $parentValue
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSubcategories(string $parentValue): JsonResponse
    {
        try {
            $parentCategory = Category::active()
                ->where('value', $parentValue)
                ->first();

            if (!$parentCategory) {
                return response()->json(['message' => 'Catégorie parente non trouvée.'], 404);
            }

            $subcategories = Category::active()
                ->where('parent_id', $parentCategory->id)
                ->orderBy('order')
                ->select(['id', 'value', 'name', 'slug', 'description', 'image_url', 'count'])
                ->get();

            return response()->json([
                'parent' => $parentCategory,
                'subcategories' => $subcategories
            ], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des sous-catégories: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des sous-catégories.'], 500);
        }
    }

    /**
     * Récupérer toutes les catégories avec leur hiérarchie
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getHierarchy(): JsonResponse
    {
        try {
            $categories = Category::active()
                ->main()
                ->with(['children' => function ($query) {
                    $query->active()->orderBy('order');
                }])
                ->orderBy('order')
                ->get();

            return response()->json(['categories' => $categories], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la hiérarchie: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération de la hiérarchie.'], 500);
        }
    }
}
