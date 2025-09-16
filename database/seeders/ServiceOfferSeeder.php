<?php

namespace Database\Seeders;

use App\Models\ServiceOffer;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ServiceOfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Créer quelques utilisateurs professionnels s'ils n'existent pas
        $professionals = User::where('is_professional', true)->take(3)->get();

        if ($professionals->count() < 3) {
            $professionals = User::factory(3)->create([
                'is_professional' => true,
            ]);
        }

        // Créer des offres de service avec les nouveaux champs
        foreach ($professionals as $professional) {
            ServiceOffer::withoutSyncingToSearch(function () use ($professional) {
                ServiceOffer::factory(2)->create([
                    'user_id' => $professional->id,
                    'what_you_get' => "• Modélisation 3D haute qualité\n• Rendus photoréalistes\n• Fichiers sources inclus\n• Support technique pendant 30 jours",
                    'who_is_this_for' => "Ce service est parfait pour :\n• Architectes cherchant des visualisations professionnelles\n• Promoteurs immobiliers\n• Designers d'intérieur\n• Particuliers avec des projets de rénovation",
                    'delivery_method' => "Livraison numérique via plateforme sécurisée :\n• Fichiers 3D au format .blend, .fbx, .obj\n• Images haute résolution (4K)\n• Vidéo de présentation (optionnel)\n• Documentation technique",
                    'why_choose_me' => "✓ Plus de 5 ans d'expérience en architecture 3D\n✓ Portfolio de 200+ projets réalisés\n✓ Révisions illimitées jusqu'à satisfaction\n✓ Respect des délais garanti\n✓ Communication transparente tout au long du projet"
                ]);
            });
        }
    }
}
