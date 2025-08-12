<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
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
            // Pour l'instant, nous retournons des catégories statiques
            // Dans une version future, ces données pourraient venir d'une table de la base de données
            $categories = [
                [
                    'id' => 1,
                    'value' => 'modeling',
                    'name' => 'Modélisation 3D',
                    'slug' => 'modelisation-3d',
                    'description' => 'Création de modèles 3D détaillés et optimisés',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 120,
                ],
                [
                    'id' => 2,
                    'value' => 'animation',
                    'name' => 'Animation',
                    'slug' => 'animation',
                    'description' => 'Animation de personnages et d\'objets',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 85,
                ],
                [
                    'id' => 3,
                    'value' => 'architectural',
                    'name' => 'Architecture 3D',
                    'slug' => 'architecture-3d',
                    'description' => 'Modélisation et visualisation architecturale',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 95,
                ],
                [
                    'id' => 4,
                    'value' => 'texturing',
                    'name' => 'Texturing',
                    'slug' => 'texturing',
                    'description' => 'Application de textures détaillées',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 70,
                ],
                [
                    'id' => 5,
                    'value' => 'character',
                    'name' => 'Personnage 3D',
                    'slug' => 'personnage-3d',
                    'description' => 'Création et animation de personnages',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 65,
                ],
                [
                    'id' => 6,
                    'value' => 'environment',
                    'name' => 'Environnement 3D',
                    'slug' => 'environnement-3d',
                    'description' => 'Création d\'environnements et de paysages',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 55,
                ],
                [
                    'id' => 7,
                    'value' => 'vfx',
                    'name' => 'Effets spéciaux',
                    'slug' => 'effets-speciaux',
                    'description' => 'Création d\'effets visuels impressionnants',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 40,
                ],
                [
                    'id' => 8,
                    'value' => 'vr_ar',
                    'name' => 'Réalité virtuelle/augmentée',
                    'slug' => 'realite-virtuelle-augmentee',
                    'description' => 'Expériences VR et AR immersives',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 30,
                ],
            ];

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
            // Pour l'instant, nous retournons des catégories statiques
            $categories = [
                1 => [
                    'id' => 1,
                    'value' => 'modeling',
                    'name' => 'Modélisation 3D',
                    'slug' => 'modelisation-3d',
                    'description' => 'Création de modèles 3D détaillés et optimisés',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 120,
                ],
                2 => [
                    'id' => 2,
                    'value' => 'animation',
                    'name' => 'Animation',
                    'slug' => 'animation',
                    'description' => 'Animation de personnages et d\'objets',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 85,
                ],
                3 => [
                    'id' => 3,
                    'value' => 'architectural',
                    'name' => 'Architecture 3D',
                    'slug' => 'architecture-3d',
                    'description' => 'Modélisation et visualisation architecturale',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 95,
                ],
                // ... autres catégories
            ];

            if (!isset($categories[$id])) {
                return response()->json(['message' => 'Catégorie non trouvée.'], 404);
            }

            return response()->json(['category' => $categories[$id]], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération de la catégorie: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération de la catégorie.'], 500);
        }
    }

    /**
     * Récupérer les sous-catégories d'Architecture 3D
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getArchitecturalSubcategories(): JsonResponse
    {
        try {
            // Sous-catégories d'Architecture 3D
            $subcategories = [
                // Modélisation architecturale
                [
                    'id' => 101,
                    'value' => 'arch_residential',
                    'name' => 'Modélisation de bâtiments résidentiels',
                    'slug' => 'modelisation-batiments-residentiels',
                    'description' => 'Création de modèles 3D de maisons, appartements et autres bâtiments résidentiels',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 25,
                ],
                [
                    'id' => 102,
                    'value' => 'arch_commercial',
                    'name' => 'Modélisation de bâtiments commerciaux',
                    'slug' => 'modelisation-batiments-commerciaux',
                    'description' => 'Création de modèles 3D de bureaux, centres commerciaux et autres bâtiments commerciaux',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 20,
                ],
                [
                    'id' => 103,
                    'value' => 'arch_historical',
                    'name' => 'Modélisation de bâtiments historiques',
                    'slug' => 'modelisation-batiments-historiques',
                    'description' => 'Création de modèles 3D de bâtiments historiques et patrimoniaux',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 15,
                ],

                // Visualisation architecturale
                [
                    'id' => 201,
                    'value' => 'viz_exterior',
                    'name' => 'Rendus extérieurs',
                    'slug' => 'rendus-exterieurs',
                    'description' => 'Création d\'images photoréalistes d\'extérieurs de bâtiments',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 30,
                ],
                [
                    'id' => 202,
                    'value' => 'viz_interior',
                    'name' => 'Rendus intérieurs',
                    'slug' => 'rendus-interieurs',
                    'description' => 'Création d\'images photoréalistes d\'intérieurs de bâtiments',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 35,
                ],

                // BIM (Building Information Modeling)
                [
                    'id' => 401,
                    'value' => 'bim_modeling',
                    'name' => 'Modélisation BIM',
                    'slug' => 'modelisation-bim',
                    'description' => 'Création de modèles BIM pour la construction et la gestion de bâtiments',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 18,
                ],

                // Design d'intérieur
                [
                    'id' => 501,
                    'value' => 'interior_furniture',
                    'name' => 'Modélisation de meubles',
                    'slug' => 'modelisation-meubles',
                    'description' => 'Création de modèles 3D de meubles et d\'accessoires',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 22,
                ],

                // Réalité virtuelle/augmentée pour l'architecture
                [
                    'id' => 701,
                    'value' => 'vr_arch_experience',
                    'name' => 'Expériences VR architecturales',
                    'slug' => 'experiences-vr-architecturales',
                    'description' => 'Création d\'expériences de réalité virtuelle pour l\'architecture',
                    'parent' => 'architectural',
                    'image_url' => 'https://images.unsplash.com/photo-1626544827763-d516dce335e2?q=80&w=2070&auto=format&fit=crop&ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D',
                    'count' => 12,
                ],
            ];

            return response()->json(['subcategories' => $subcategories], 200);
        } catch (\Exception $e) {
            Log::error('Erreur lors de la récupération des sous-catégories d\'Architecture 3D: ' . $e->getMessage());
            return response()->json(['message' => 'Erreur lors de la récupération des sous-catégories.'], 500);
        }
    }
}
