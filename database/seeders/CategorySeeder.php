<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Création des catégories principales...');

        // Catégories principales
        $mainCategories = [
            [
                'value' => 'modeling',
                'name' => 'Modélisation 3D',
                'description' => 'Création de modèles 3D détaillés et optimisés pour diverses applications',
                'order' => 1,
                'count' => 120,
            ],
            [
                'value' => 'animation',
                'name' => 'Animation',
                'description' => 'Animation de personnages et d\'objets en 3D',
                'order' => 2,
                'count' => 85,
            ],
            [
                'value' => 'architectural',
                'name' => 'Architecture 3D',
                'description' => 'Modélisation et visualisation architecturale professionnelle',
                'order' => 3,
                'count' => 95,
            ],
            [
                'value' => 'product',
                'name' => 'Produit 3D',
                'description' => 'Modélisation de produits pour e-commerce et marketing',
                'order' => 4,
                'count' => 70,
            ],
            [
                'value' => 'character',
                'name' => 'Personnage 3D',
                'description' => 'Création et animation de personnages 3D',
                'order' => 5,
                'count' => 65,
            ],
            [
                'value' => 'environment',
                'name' => 'Environnement 3D',
                'description' => 'Création d\'environnements et de paysages 3D',
                'order' => 6,
                'count' => 55,
            ],
            [
                'value' => 'vr_ar',
                'name' => 'Réalité virtuelle/augmentée',
                'description' => 'Expériences VR et AR immersives',
                'order' => 7,
                'count' => 30,
            ],
            [
                'value' => 'game_art',
                'name' => 'Game Art',
                'description' => 'Assets et environnements pour jeux vidéo',
                'order' => 8,
                'count' => 40,
            ],
        ];

        $createdCategories = [];
        foreach ($mainCategories as $categoryData) {
            $category = Category::create($categoryData);
            $createdCategories[$category->value] = $category;
            $this->command->info("✓ Catégorie créée: {$category->name}");
        }

        $this->command->info('Création des sous-catégories d\'Architecture 3D...');

        // Sous-catégories d'Architecture 3D
        $architecturalSubcategories = [
            // Modélisation architecturale
            [
                'value' => 'arch_residential',
                'name' => 'Modélisation de bâtiments résidentiels',
                'description' => 'Création de modèles 3D de maisons, appartements et autres bâtiments résidentiels',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 1,
                'count' => 25,
            ],
            [
                'value' => 'arch_commercial',
                'name' => 'Modélisation de bâtiments commerciaux',
                'description' => 'Création de modèles 3D de bureaux, centres commerciaux et autres bâtiments commerciaux',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 2,
                'count' => 20,
            ],
            [
                'value' => 'arch_historical',
                'name' => 'Modélisation de bâtiments historiques',
                'description' => 'Création de modèles 3D de bâtiments historiques et patrimoniaux',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 3,
                'count' => 15,
            ],

            // Visualisation architecturale
            [
                'value' => 'viz_exterior',
                'name' => 'Rendus extérieurs',
                'description' => 'Création d\'images photoréalistes d\'extérieurs de bâtiments',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 4,
                'count' => 30,
            ],
            [
                'value' => 'viz_interior',
                'name' => 'Rendus intérieurs',
                'description' => 'Création d\'images photoréalistes d\'intérieurs de bâtiments',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 5,
                'count' => 35,
            ],

            // BIM (Building Information Modeling)
            [
                'value' => 'bim_modeling',
                'name' => 'Modélisation BIM',
                'description' => 'Création de modèles BIM pour la construction et la gestion de bâtiments',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 6,
                'count' => 18,
            ],

            // Design d'intérieur
            [
                'value' => 'interior_furniture',
                'name' => 'Modélisation de meubles',
                'description' => 'Création de modèles 3D de meubles et d\'accessoires',
                'parent_id' => $createdCategories['architectural']->id,
                'order' => 7,
                'count' => 22,
            ],
        ];

        foreach ($architecturalSubcategories as $subcategoryData) {
            $subcategory = Category::create($subcategoryData);
            $this->command->info("✓ Sous-catégorie créée: {$subcategory->name}");
        }

        $this->command->info('Seeder terminé avec succès !');
        $this->command->info('Total: ' . Category::count() . ' catégories créées');
    }
}
