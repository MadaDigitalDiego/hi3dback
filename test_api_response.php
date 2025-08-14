<?php

require_once __DIR__ . '/vendor/autoload.php';

use App\Models\ProfessionalProfile;

// Charger l'application Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    echo "=== Test de la structure de réponse API ===\n\n";
    
    // Simuler ce que fait le contrôleur ProfessionalController::index()
    $professionalProfiles = ProfessionalProfile::with(['user', 'views', 'likers'])->take(1)->get();
    
    if ($professionalProfiles->isEmpty()) {
        echo "Aucun profil professionnel trouvé.\n";
        echo "Créons un profil de test...\n";
        
        // Créer un profil de test simple
        $profile = new ProfessionalProfile();
        $profile->first_name = 'Test';
        $profile->last_name = 'User';
        $profile->title = 'Développeur Test';
        $profile->bio = 'Profil de test';
        $profile->hourly_rate = 50.00;
        $profile->availability_status = 'available';
        $profile->rating = 4.5;
        
        echo "Profil de test créé en mémoire.\n\n";
        $professionalProfiles = collect([$profile]);
    }
    
    // Formater les données comme dans le contrôleur
    $professionals = $professionalProfiles->map(function ($profile) {
        // Traiter les skills
        $skills = [];
        if ($profile->skills) {
            if (is_array($profile->skills)) {
                $skills = $profile->skills;
            } elseif (is_string($profile->skills)) {
                try {
                    $skills = json_decode($profile->skills, true);
                } catch (\Exception $e) {
                    $skills = [$profile->skills];
                }
            }
        }

        $achievements = $profile->achievements ?? collect();
        $user = $profile->user;
        $services = $user ? $user->serviceOffers : collect();

        return [
            'id' => $profile->id,
            'user_id' => $profile->user_id,
            'first_name' => $profile->first_name,
            'last_name' => $profile->last_name,
            'email' => $profile->user ? $profile->user->email : null,
            'is_professional' => $profile->user ? $profile->user->is_professional : true,
            'city' => $profile->city,
            'country' => $profile->country,
            'skills' => $skills,
            'availability_status' => $profile->availability_status,
            'hourly_rate' => $profile->hourly_rate,
            'avatar' => $profile->avatar,
            'cover_photo' => $profile->cover_photo,
            'profile_picture_path' => $profile->avatar,
            'rating' => $profile->rating,
            'review_count' => 0,
            'bio' => $profile->bio,
            'title' => $profile->title,
            'achievements' => $achievements,
            'service_offer' => $services,
            // Nouvelles données de likes et views
            'likes_count' => $profile->getTotalLikesAttribute(),
            'views_count' => $profile->getTotalViewsAttribute(),
            'popularity_score' => $profile->getPopularityScore(),
        ];
    });

    // Simuler la réponse JSON
    $response = [
        'success' => true,
        'professionals' => $professionals,
    ];

    echo "Structure de la réponse API :\n";
    echo "- success: " . ($response['success'] ? 'true' : 'false') . "\n";
    echo "- professionals: array(" . count($response['professionals']) . " éléments)\n\n";

    if (!empty($response['professionals'])) {
        $firstProfessional = $response['professionals'][0];
        echo "Premier professionnel :\n";
        echo "- ID: " . ($firstProfessional['id'] ?? 'null') . "\n";
        echo "- Nom: " . ($firstProfessional['first_name'] ?? '') . " " . ($firstProfessional['last_name'] ?? '') . "\n";
        echo "- Titre: " . ($firstProfessional['title'] ?? 'null') . "\n";
        echo "- Likes: " . ($firstProfessional['likes_count'] ?? 'null') . "\n";
        echo "- Vues: " . ($firstProfessional['views_count'] ?? 'null') . "\n";
        echo "- Score popularité: " . ($firstProfessional['popularity_score'] ?? 'null') . "\n\n";

        // Vérifier que les nouvelles clés sont présentes
        $requiredKeys = ['likes_count', 'views_count', 'popularity_score'];
        $missingKeys = [];

        foreach ($requiredKeys as $key) {
            if (!array_key_exists($key, $firstProfessional)) {
                $missingKeys[] = $key;
            }
        }

        if (empty($missingKeys)) {
            echo "✅ Toutes les nouvelles clés sont présentes.\n";
        } else {
            echo "❌ Clés manquantes : " . implode(', ', $missingKeys) . "\n";
        }

        // Vérifier les types de données
        $typesOk = true;
        if (!is_numeric($firstProfessional['likes_count'])) {
            echo "❌ likes_count n'est pas numérique\n";
            $typesOk = false;
        }
        if (!is_numeric($firstProfessional['views_count'])) {
            echo "❌ views_count n'est pas numérique\n";
            $typesOk = false;
        }
        if (!is_numeric($firstProfessional['popularity_score'])) {
            echo "❌ popularity_score n'est pas numérique\n";
            $typesOk = false;
        }

        if ($typesOk) {
            echo "✅ Tous les types de données sont corrects.\n";
        }
    }

    echo "\n=== Test terminé ===\n";

} catch (\Exception $e) {
    echo "❌ Erreur : " . $e->getMessage() . "\n";
    echo "Fichier : " . $e->getFile() . " ligne " . $e->getLine() . "\n";
}
