<?php

require_once __DIR__ . '/vendor/autoload.php';

use Illuminate\Support\Facades\DB;
use App\Models\ProfessionalProfile;
use App\Models\User;

// Bootstrap Laravel
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "=== Test du système de matching ===\n\n";

// 1. Vérifier la structure de la base de données
echo "1. Vérification de la structure de la base de données:\n";

try {
    // Vérifier les colonnes de professional_profiles
    $columns = DB::select("DESCRIBE professional_profiles");
    echo "Colonnes de professional_profiles:\n";
    foreach ($columns as $column) {
        echo "  - {$column->Field} ({$column->Type})\n";
    }
    echo "\n";
} catch (Exception $e) {
    echo "Erreur lors de la vérification de la structure: " . $e->getMessage() . "\n";
}

// 2. Compter les professionnels
echo "2. Statistiques des professionnels:\n";

try {
    $totalUsers = User::count();
    $totalProfessionals = User::where('is_professional', true)->count();
    $totalProfiles = ProfessionalProfile::count();
    $profilesWithUsers = ProfessionalProfile::whereHas('user', function($q) {
        $q->where('is_professional', true);
    })->count();
    
    echo "  - Total utilisateurs: {$totalUsers}\n";
    echo "  - Total professionnels: {$totalProfessionals}\n";
    echo "  - Total profils professionnels: {$totalProfiles}\n";
    echo "  - Profils avec utilisateurs professionnels: {$profilesWithUsers}\n\n";
} catch (Exception $e) {
    echo "Erreur lors du comptage: " . $e->getMessage() . "\n";
}

// 3. Tester les filtres individuellement
echo "3. Test des filtres individuellement:\n";

try {
    // Test filtre compétences
    echo "  a) Test filtre compétences (skills):\n";
    $skillsQuery = ProfessionalProfile::query()
        ->with('user')
        ->whereHas('user', function ($q) {
            $q->where('is_professional', true);
        });
    
    // Récupérer quelques profils pour voir les compétences disponibles
    $sampleProfiles = ProfessionalProfile::whereNotNull('skills')->limit(5)->get();
    echo "    Exemples de compétences dans la base:\n";
    foreach ($sampleProfiles as $profile) {
        $skills = is_string($profile->skills) ? json_decode($profile->skills, true) : $profile->skills;
        if ($skills && is_array($skills)) {
            echo "      Profil {$profile->id}: " . implode(', ', $skills) . "\n";
        }
    }
    
    // Test avec une compétence spécifique
    $testSkill = 'PHP';
    $skillsQuery->where(function ($q) use ($testSkill) {
        $q->orWhereJsonContains('skills', $testSkill);
    });
    $skillsCount = $skillsQuery->count();
    echo "    Professionnels avec compétence '{$testSkill}': {$skillsCount}\n\n";
    
    // Test filtre langues
    echo "  b) Test filtre langues (languages):\n";
    $languagesQuery = ProfessionalProfile::query()
        ->with('user')
        ->whereHas('user', function ($q) {
            $q->where('is_professional', true);
        });
    
    // Récupérer quelques profils pour voir les langues disponibles
    $sampleProfiles = ProfessionalProfile::whereNotNull('languages')->limit(5)->get();
    echo "    Exemples de langues dans la base:\n";
    foreach ($sampleProfiles as $profile) {
        $languages = is_string($profile->languages) ? json_decode($profile->languages, true) : $profile->languages;
        if ($languages && is_array($languages)) {
            echo "      Profil {$profile->id}: " . implode(', ', $languages) . "\n";
        }
    }
    
    // Test avec une langue spécifique
    $testLanguage = 'Français';
    $languagesQuery->where(function ($q) use ($testLanguage) {
        $q->orWhereJsonContains('languages', $testLanguage);
    });
    $languagesCount = $languagesQuery->count();
    echo "    Professionnels parlant '{$testLanguage}': {$languagesCount}\n\n";
    
    // Test filtre localisation
    echo "  c) Test filtre localisation (city):\n";
    $cities = ProfessionalProfile::whereNotNull('city')->distinct()->pluck('city')->take(5);
    echo "    Exemples de villes dans la base: " . $cities->implode(', ') . "\n";
    
    if ($cities->isNotEmpty()) {
        $testCity = $cities->first();
        $cityCount = ProfessionalProfile::query()
            ->whereHas('user', function ($q) {
                $q->where('is_professional', true);
            })
            ->where('city', 'like', '%' . $testCity . '%')
            ->count();
        echo "    Professionnels dans '{$testCity}': {$cityCount}\n\n";
    }
    
    // Test filtre expérience
    echo "  d) Test filtre expérience (years_of_experience):\n";
    $experienceStats = ProfessionalProfile::query()
        ->whereHas('user', function ($q) {
            $q->where('is_professional', true);
        })
        ->whereNotNull('years_of_experience')
        ->selectRaw('MIN(years_of_experience) as min_exp, MAX(years_of_experience) as max_exp, AVG(years_of_experience) as avg_exp, COUNT(*) as count')
        ->first();
    
    if ($experienceStats) {
        echo "    Statistiques d'expérience:\n";
        echo "      Min: {$experienceStats->min_exp} ans\n";
        echo "      Max: {$experienceStats->max_exp} ans\n";
        echo "      Moyenne: " . round($experienceStats->avg_exp, 2) . " ans\n";
        echo "      Nombre de profils avec expérience: {$experienceStats->count}\n";
        
        // Test avec 2 ans d'expérience minimum
        $testExperience = 2;
        $expCount = ProfessionalProfile::query()
            ->whereHas('user', function ($q) {
                $q->where('is_professional', true);
            })
            ->where('years_of_experience', '>=', $testExperience)
            ->count();
        echo "    Professionnels avec >= {$testExperience} ans d'expérience: {$expCount}\n\n";
    }
    
    // Test filtre disponibilité
    echo "  e) Test filtre disponibilité (availability_status):\n";
    $availabilityStats = ProfessionalProfile::query()
        ->whereHas('user', function ($q) {
            $q->where('is_professional', true);
        })
        ->selectRaw('availability_status, COUNT(*) as count')
        ->groupBy('availability_status')
        ->get();
    
    echo "    Répartition par statut de disponibilité:\n";
    foreach ($availabilityStats as $stat) {
        echo "      {$stat->availability_status}: {$stat->count}\n";
    }
    echo "\n";
    
} catch (Exception $e) {
    echo "Erreur lors des tests de filtres: " . $e->getMessage() . "\n";
}

// 4. Test de matching complet
echo "4. Test de matching complet avec filtres combinés:\n";

try {
    $testFilters = [
        'skills' => ['PHP', 'JavaScript'],
        'languages' => ['Français'],
        'location' => 'Paris',
        'experience_years' => 1,
        'availability_status' => 'available'
    ];
    
    echo "  Filtres de test: " . json_encode($testFilters) . "\n";
    
    $query = ProfessionalProfile::query()
        ->with('user')
        ->whereHas('user', function ($q) {
            $q->where('is_professional', true);
        });
    
    // Appliquer les filtres
    if (isset($testFilters['skills']) && is_array($testFilters['skills']) && !empty($testFilters['skills'])) {
        $query->where(function ($q) use ($testFilters) {
            foreach ($testFilters['skills'] as $skill) {
                if (!empty($skill)) {
                    $q->orWhereJsonContains('skills', $skill);
                }
            }
        });
    }
    
    if (isset($testFilters['languages']) && is_array($testFilters['languages']) && !empty($testFilters['languages'])) {
        $query->where(function ($q) use ($testFilters) {
            foreach ($testFilters['languages'] as $lang) {
                if (!empty($lang)) {
                    $q->orWhereJsonContains('languages', $lang);
                }
            }
        });
    }
    
    if (isset($testFilters['location']) && !empty($testFilters['location'])) {
        $query->where('city', 'like', '%' . $testFilters['location'] . '%');
    }
    
    if (isset($testFilters['experience_years']) && is_numeric($testFilters['experience_years'])) {
        $query->where('years_of_experience', '>=', $testFilters['experience_years']);
    }
    
    if (isset($testFilters['availability_status']) && !empty($testFilters['availability_status'])) {
        $query->where('availability_status', $testFilters['availability_status']);
    }
    
    $eligibleProfessionals = $query->get();
    $count = $eligibleProfessionals->count();
    
    echo "  Résultat: {$count} professionnels éligibles trouvés\n";
    
    if ($count > 0) {
        echo "  Détails des professionnels trouvés:\n";
        foreach ($eligibleProfessionals->take(3) as $profile) {
            $skills = is_string($profile->skills) ? json_decode($profile->skills, true) : $profile->skills;
            $languages = is_string($profile->languages) ? json_decode($profile->languages, true) : $profile->languages;
            
            echo "    - Profil {$profile->id}: {$profile->first_name} {$profile->last_name}\n";
            echo "      Ville: {$profile->city}\n";
            echo "      Expérience: {$profile->years_of_experience} ans\n";
            echo "      Disponibilité: {$profile->availability_status}\n";
            echo "      Compétences: " . (is_array($skills) ? implode(', ', $skills) : 'N/A') . "\n";
            echo "      Langues: " . (is_array($languages) ? implode(', ', $languages) : 'N/A') . "\n";
            echo "      User ID: {$profile->user_id}\n\n";
        }
    }
    
} catch (Exception $e) {
    echo "Erreur lors du test de matching complet: " . $e->getMessage() . "\n";
}

echo "=== Fin du test ===\n";
