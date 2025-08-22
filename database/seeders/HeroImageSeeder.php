<?php

namespace Database\Seeders;

use App\Models\HeroImage;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HeroImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Supprimer les images existantes
        HeroImage::truncate();

        $heroImages = [
            [
                'title' => 'Architecture 3D Moderne',
                'description' => 'Découvrez nos services de modélisation 3D architecturale avec des rendus photoréalistes',
                'image_path' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Rendu 3D d\'un bâtiment moderne avec façade en verre',
                'is_active' => true,
                'position' => 1,
            ],
            [
                'title' => 'Design Intérieur Innovant',
                'description' => 'Visualisez vos espaces intérieurs avec nos solutions de design 3D immersives',
                'image_path' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Intérieur moderne avec mobilier design et éclairage ambiant',
                'is_active' => true,
                'position' => 2,
            ],
            [
                'title' => 'Réalité Virtuelle Immersive',
                'description' => 'Explorez vos projets en réalité virtuelle pour une expérience unique',
                'image_path' => 'https://images.unsplash.com/photo-1592478411213-6153e4ebc696?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Personne utilisant un casque de réalité virtuelle',
                'is_active' => true,
                'position' => 3,
            ],
            [
                'title' => 'Modélisation Industrielle',
                'description' => 'Solutions 3D pour l\'industrie et la conception de produits',
                'image_path' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Modélisation 3D d\'un objet industriel complexe',
                'is_active' => false,
                'position' => 4,
            ],
            [
                'title' => 'Animation 3D Professionnelle',
                'description' => 'Donnez vie à vos projets avec nos services d\'animation 3D',
                'image_path' => 'https://images.unsplash.com/photo-1551650975-87deedd944c3?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Scène d\'animation 3D avec personnages et environnement',
                'is_active' => false,
                'position' => 5,
            ],
            [
                'title' => 'Visualisation Architecturale',
                'description' => 'Présentez vos projets architecturaux avec des rendus époustouflants',
                'image_path' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1920&h=1080&fit=crop&crop=center',
                'alt_text' => 'Vue aérienne d\'un complexe architectural moderne',
                'is_active' => true,
                'position' => 6,
            ],
        ];

        foreach ($heroImages as $imageData) {
            HeroImage::create($imageData);
        }

        $this->command->info('✅ ' . count($heroImages) . ' images Hero créées avec succès !');
        $this->command->info('📊 ' . HeroImage::where('is_active', true)->count() . ' images actives');
        $this->command->info('📊 ' . HeroImage::where('is_active', false)->count() . ' images inactives');
    }
}
