<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\ProfessionalProfile;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class TestMatching extends Command
{
    protected $signature = 'test:matching';
    protected $description = 'Test the professional matching system';

    public function handle()
    {
        $this->info('=== Test du système de matching ===');
        $this->newLine();

        // 1. Statistiques de base
        $this->info('1. Statistiques de base:');
        $totalUsers = User::count();
        $totalProfessionals = User::where('is_professional', true)->count();
        $totalProfiles = ProfessionalProfile::count();
        $profilesWithUsers = ProfessionalProfile::whereHas('user', function($q) {
            $q->where('is_professional', true);
        })->count();

        $this->line("  - Total utilisateurs: {$totalUsers}");
        $this->line("  - Total professionnels: {$totalProfessionals}");
        $this->line("  - Total profils professionnels: {$totalProfiles}");
        $this->line("  - Profils avec utilisateurs professionnels: {$profilesWithUsers}");
        $this->newLine();

        // 2. Vérifier la structure des données
        $this->info('2. Vérification de la structure des données:');
        
        // Vérifier les compétences
        $skillsData = ProfessionalProfile::whereNotNull('skills')->limit(3)->get();
        $this->line("  Exemples de compétences:");
        foreach ($skillsData as $profile) {
            $skills = is_string($profile->skills) ? json_decode($profile->skills, true) : $profile->skills;
            if ($skills && is_array($skills)) {
                $this->line("    Profil {$profile->id}: " . implode(', ', $skills));
            } else {
                $this->line("    Profil {$profile->id}: Format invalide - " . gettype($profile->skills));
            }
        }
        
        // Vérifier les langues
        $languagesData = ProfessionalProfile::whereNotNull('languages')->limit(3)->get();
        $this->line("  Exemples de langues:");
        foreach ($languagesData as $profile) {
            $languages = is_string($profile->languages) ? json_decode($profile->languages, true) : $profile->languages;
            if ($languages && is_array($languages)) {
                $this->line("    Profil {$profile->id}: " . implode(', ', $languages));
            } else {
                $this->line("    Profil {$profile->id}: Format invalide - " . gettype($profile->languages));
            }
        }
        $this->newLine();

        // 3. Test des filtres individuels
        $this->info('3. Test des filtres individuels:');
        
        // Test compétences
        $this->testSkillsFilter();
        
        // Test langues
        $this->testLanguagesFilter();
        
        // Test localisation
        $this->testLocationFilter();
        
        // Test expérience
        $this->testExperienceFilter();
        
        // Test disponibilité
        $this->testAvailabilityFilter();

        // 4. Test de matching complet
        $this->info('4. Test de matching complet:');
        $this->testCompleteMatching();

        $this->info('=== Fin du test ===');
    }

    private function testSkillsFilter()
    {
        $this->line("  a) Test filtre compétences:");
        
        // Compter les profils avec des compétences
        $profilesWithSkills = ProfessionalProfile::whereNotNull('skills')
            ->whereHas('user', function($q) {
                $q->where('is_professional', true);
            })->count();
        $this->line("    Profils avec compétences: {$profilesWithSkills}");
        
        // Test avec une compétence spécifique
        $testSkill = 'PHP';
        try {
            $count = ProfessionalProfile::query()
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                })
                ->whereJsonContains('skills', $testSkill)
                ->count();
            $this->line("    Profils avec compétence '{$testSkill}': {$count}");
        } catch (\Exception $e) {
            $this->error("    Erreur test compétence: " . $e->getMessage());
        }
    }

    private function testLanguagesFilter()
    {
        $this->line("  b) Test filtre langues:");
        
        // Compter les profils avec des langues
        $profilesWithLanguages = ProfessionalProfile::whereNotNull('languages')
            ->whereHas('user', function($q) {
                $q->where('is_professional', true);
            })->count();
        $this->line("    Profils avec langues: {$profilesWithLanguages}");
        
        // Test avec une langue spécifique
        $testLanguage = 'Français';
        try {
            $count = ProfessionalProfile::query()
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                })
                ->whereJsonContains('languages', $testLanguage)
                ->count();
            $this->line("    Profils parlant '{$testLanguage}': {$count}");
        } catch (\Exception $e) {
            $this->error("    Erreur test langue: " . $e->getMessage());
        }
    }

    private function testLocationFilter()
    {
        $this->line("  c) Test filtre localisation:");
        
        $profilesWithCity = ProfessionalProfile::whereNotNull('city')
            ->whereHas('user', function($q) {
                $q->where('is_professional', true);
            })->count();
        $this->line("    Profils avec ville: {$profilesWithCity}");
        
        // Obtenir quelques villes
        $cities = ProfessionalProfile::whereNotNull('city')->distinct()->pluck('city')->take(3);
        if ($cities->isNotEmpty()) {
            $testCity = $cities->first();
            $count = ProfessionalProfile::query()
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                })
                ->where('city', 'like', '%' . $testCity . '%')
                ->count();
            $this->line("    Profils dans '{$testCity}': {$count}");
        }
    }

    private function testExperienceFilter()
    {
        $this->line("  d) Test filtre expérience:");
        
        $profilesWithExp = ProfessionalProfile::whereNotNull('years_of_experience')
            ->whereHas('user', function($q) {
                $q->where('is_professional', true);
            })->count();
        $this->line("    Profils avec expérience: {$profilesWithExp}");
        
        if ($profilesWithExp > 0) {
            $testExperience = 2;
            $count = ProfessionalProfile::query()
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                })
                ->where('years_of_experience', '>=', $testExperience)
                ->count();
            $this->line("    Profils avec >= {$testExperience} ans: {$count}");
        }
    }

    private function testAvailabilityFilter()
    {
        $this->line("  e) Test filtre disponibilité:");
        
        $availabilityStats = ProfessionalProfile::query()
            ->whereHas('user', function($q) {
                $q->where('is_professional', true);
            })
            ->selectRaw('availability_status, COUNT(*) as count')
            ->groupBy('availability_status')
            ->get();
        
        foreach ($availabilityStats as $stat) {
            $this->line("    {$stat->availability_status}: {$stat->count}");
        }
    }

    private function testCompleteMatching()
    {
        $filters = [
            'skills' => ['PHP'],
            'experience_years' => 1,
            'availability_status' => 'available'
        ];
        
        $this->line("  Filtres de test: " . json_encode($filters));
        
        try {
            $query = ProfessionalProfile::query()
                ->with('user')
                ->whereHas('user', function ($q) {
                    $q->where('is_professional', true);
                });
            
            // Appliquer les filtres comme dans le contrôleur
            if (isset($filters['skills']) && is_array($filters['skills']) && !empty($filters['skills'])) {
                $query->where(function ($q) use ($filters) {
                    foreach ($filters['skills'] as $skill) {
                        if (!empty($skill)) {
                            $q->orWhereJsonContains('skills', $skill);
                        }
                    }
                });
            }
            
            if (isset($filters['experience_years']) && is_numeric($filters['experience_years'])) {
                $query->where('years_of_experience', '>=', $filters['experience_years']);
            }
            
            if (isset($filters['availability_status']) && !empty($filters['availability_status'])) {
                $query->where('availability_status', $filters['availability_status']);
            }
            
            $eligibleProfessionals = $query->get();
            $count = $eligibleProfessionals->count();
            
            $this->line("  Résultat: {$count} professionnels éligibles");
            
            if ($count > 0) {
                $this->line("  Premiers résultats:");
                foreach ($eligibleProfessionals->take(2) as $profile) {
                    $this->line("    - {$profile->first_name} {$profile->last_name} (ID: {$profile->id})");
                    $this->line("      Expérience: {$profile->years_of_experience} ans");
                    $this->line("      Disponibilité: {$profile->availability_status}");
                }
            }
            
        } catch (\Exception $e) {
            $this->error("  Erreur lors du test complet: " . $e->getMessage());
        }
    }
}
