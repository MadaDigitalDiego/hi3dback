<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use App\Models\ProfessionalProfile;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfessionalSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('🚀 Création de 6 professionnels avec leurs services et réalisations...');

        // Données des professionnels
        $professionals = [
            [
                'first_name' => 'Alexandre',
                'last_name' => 'Dubois',
                'email' => 'alexandre.dubois@hi3d.com',
                'profession' => 'Architecte 3D Senior',
                'bio' => 'Architecte 3D spécialisé dans la visualisation architecturale avec plus de 8 ans d\'expérience. Expert en modélisation de bâtiments résidentiels et commerciaux.',
                'city' => 'Paris',
                'expertise' => ['Architecture résidentielle', 'Rendu photoréaliste', 'BIM', 'Visualisation 3D'],
                'years_of_experience' => 8,
                'hourly_rate' => 85.00,
                'avatar' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?w=400&h=400&fit=crop&crop=face'
            ],
            [
                'first_name' => 'Sophie',
                'last_name' => 'Martin',
                'email' => 'sophie.martin@hi3d.com',
                'profession' => 'Designer d\'Intérieur 3D',
                'bio' => 'Designer d\'intérieur passionnée par la création d\'espaces uniques et fonctionnels. Spécialisée dans les rendus d\'intérieur haut de gamme.',
                'city' => 'Lyon',
                'expertise' => ['Design d\'intérieur', 'Aménagement d\'espace', 'Rendu d\'intérieur', 'Mobilier sur mesure'],
                'years_of_experience' => 6,
                'hourly_rate' => 75.00,
                'avatar' => 'https://images.unsplash.com/photo-1494790108755-2616b612b786?w=400&h=400&fit=crop&crop=face'
            ],
            [
                'first_name' => 'Thomas',
                'last_name' => 'Leroy',
                'email' => 'thomas.leroy@hi3d.com',
                'profession' => 'Spécialiste Animation 3D',
                'bio' => 'Animateur 3D créatif avec une expertise dans l\'animation architecturale et les présentations dynamiques de projets.',
                'city' => 'Marseille',
                'expertise' => ['Animation 3D', 'Motion Design', 'Présentation projet', 'Réalité virtuelle'],
                'years_of_experience' => 5,
                'hourly_rate' => 70.00,
                'avatar' => 'https://images.unsplash.com/photo-1472099645785-5658abf4ff4e?w=400&h=400&fit=crop&crop=face'
            ],
            [
                'first_name' => 'Camille',
                'last_name' => 'Rousseau',
                'email' => 'camille.rousseau@hi3d.com',
                'profession' => 'Expert Modélisation Produit',
                'bio' => 'Spécialiste en modélisation 3D de produits pour l\'e-commerce et le marketing. Créatrice de visuels produits impactants.',
                'city' => 'Toulouse',
                'expertise' => ['Modélisation produit', 'E-commerce 3D', 'Packaging 3D', 'Marketing visuel'],
                'years_of_experience' => 4,
                'hourly_rate' => 65.00,
                'avatar' => 'https://images.unsplash.com/photo-1438761681033-6461ffad8d80?w=400&h=400&fit=crop&crop=face'
            ],
            [
                'first_name' => 'Julien',
                'last_name' => 'Moreau',
                'email' => 'julien.moreau@hi3d.com',
                'profession' => 'Créateur d\'Environnements 3D',
                'bio' => 'Artiste 3D spécialisé dans la création d\'environnements immersifs et de paysages virtuels pour diverses applications.',
                'city' => 'Nantes',
                'expertise' => ['Environnements 3D', 'Paysagisme virtuel', 'Game Art', 'Texturing avancé'],
                'years_of_experience' => 7,
                'hourly_rate' => 80.00,
                'avatar' => 'https://images.unsplash.com/photo-1500648767791-00dcc994a43e?w=400&h=400&fit=crop&crop=face'
            ],
            [
                'first_name' => 'Emma',
                'last_name' => 'Bernard',
                'email' => 'emma.bernard@hi3d.com',
                'profession' => 'Spécialiste VR/AR',
                'bio' => 'Développeuse d\'expériences en réalité virtuelle et augmentée. Pionnière dans l\'intégration de la 3D aux nouvelles technologies.',
                'city' => 'Bordeaux',
                'expertise' => ['Réalité virtuelle', 'Réalité augmentée', 'Expériences immersives', 'Technologies émergentes'],
                'years_of_experience' => 3,
                'hourly_rate' => 90.00,
                'avatar' => 'https://images.unsplash.com/photo-1544005313-94ddf0286df2?w=400&h=400&fit=crop&crop=face'
            ]
        ];

        // Récupérer les catégories existantes
        $categories = Category::all();
        if ($categories->isEmpty()) {
            $this->command->error('❌ Aucune catégorie trouvée. Veuillez d\'abord exécuter CategorySeeder.');
            return;
        }

        foreach ($professionals as $index => $professionalData) {
            $this->command->info("👤 Création du professionnel: {$professionalData['first_name']} {$professionalData['last_name']}");

            // Vérifier si l'utilisateur existe déjà
            $existingUser = User::where('email', $professionalData['email'])->first();
            if ($existingUser) {
                $this->command->info("⚠️  Utilisateur {$professionalData['email']} existe déjà, passage au suivant...");
                continue;
            }

            // Créer l'utilisateur
            $user = User::create([
                'first_name' => $professionalData['first_name'],
                'last_name' => $professionalData['last_name'],
                'email' => $professionalData['email'],
                'password' => Hash::make('password123'),
                'email_verified_at' => now(),
                'is_professional' => true,
                'profile_completed' => true,
                'role' => 'user',
            ]);

            // Créer le profil professionnel (skip Meilisearch sync during seeding)
            $profile = ProfessionalProfile::withoutSyncingToSearch(function () use ($professionalData, $user) {
                return ProfessionalProfile::create([
                'user_id' => $user->id,
                'first_name' => $professionalData['first_name'],
                'last_name' => $professionalData['last_name'],
                'email' => $professionalData['email'],
                'phone' => '+33 ' . fake('fr_FR')->numerify('# ## ## ## ##'),
                'address' => fake('fr_FR')->streetAddress(),
                'city' => $professionalData['city'],
                'country' => 'France',
                'bio' => $professionalData['bio'],
                'avatar' => $professionalData['avatar'],
                'cover_photo' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=1200&h=400&fit=crop',
                'title' => $professionalData['profession'],
                'profession' => $professionalData['profession'],
                'expertise' => $professionalData['expertise'],
                'years_of_experience' => $professionalData['years_of_experience'],
                'hourly_rate' => $professionalData['hourly_rate'],
                'description' => $professionalData['bio'],
                'skills' => $this->getSkillsForProfession($professionalData['profession']),
                'portfolio' => $this->generatePortfolioLinks($professionalData['first_name']),
                'availability_status' => fake('fr_FR')->randomElement(['available', 'busy', 'unavailable']),
                'languages' => ['Français', 'Anglais'],
                'services_offered' => $this->getServicesForProfession($professionalData['profession']),
                'rating' => fake('fr_FR')->randomFloat(1, 4.0, 5.0),
                'social_links' => [
                    'linkedin' => "https://linkedin.com/in/{$professionalData['first_name']}-{$professionalData['last_name']}",
                    'website' => "https://{$professionalData['first_name']}-{$professionalData['last_name']}.com",
                ],
                'completion_percentage' => 100
                ]);
            });

            // Créer 4 services pour chaque professionnel
            $this->createServicesForProfessional($user, $profile, $categories);

            $this->command->info("✅ Professionnel créé avec succès !");
        }

        $this->command->info('🎉 Seeder terminé avec succès !');
        $this->command->info("📊 Résumé:");
        $this->command->info("   - 6 professionnels créés");
        $this->command->info("   - 24 services créés (4 par professionnel)");
        $this->command->info("   - 96 réalisations créées (4 par service)");
    }

    /**
     * Créer 4 services pour un professionnel
     */
    private function createServicesForProfessional(User $user, ProfessionalProfile $profile, $categories)
    {
        $serviceTemplates = $this->getServiceTemplatesForProfession($profile->profession);

        foreach ($serviceTemplates as $serviceData) {
            $this->command->info("   📋 Création du service: {$serviceData['title']}");

            // Créer le service (skip Meilisearch sync during seeding)
            $service = ServiceOffer::withoutSyncingToSearch(function () use ($serviceData, $user) {
                return ServiceOffer::create([
                    'user_id' => $user->id,
                    'title' => $serviceData['title'],
                    'description' => $serviceData['description'],
                    'price' => $serviceData['price'],
                    'price_unit' => 'fixed',
                    'execution_time' => $serviceData['execution_time'],
                    'concepts' => $serviceData['concepts'],
                    'revisions' => $serviceData['revisions'],
                    'is_private' => false,
                    'status' => 'active',
                    'categories' => $serviceData['categories'],
                    'files' => null,
                    'views' => fake('fr_FR')->numberBetween(50, 500),
                    'likes' => fake('fr_FR')->numberBetween(5, 50),
                    'rating' => fake('fr_FR')->randomFloat(1, 4.0, 5.0),
                    'image' => $serviceData['image'],
                ]);
            });

            // Créer 4 réalisations pour chaque service
            $this->createAchievementsForService($profile, $service, $serviceData);
        }
    }

    /**
     * Créer 4 réalisations pour un service
     */
    private function createAchievementsForService(ProfessionalProfile $profile, ServiceOffer $service, array $serviceData)
    {
        $achievementTemplates = $this->getAchievementTemplatesForService($serviceData);

        foreach ($achievementTemplates as $achievementData) {
            $this->command->info("      🏆 Création de la réalisation: {$achievementData['title']}");

            // Créer la réalisation (skip Meilisearch sync during seeding)
            Achievement::withoutSyncingToSearch(function () use ($achievementData, $profile, $serviceData) {
                return Achievement::create([
                    'professional_profile_id' => $profile->id,
                    'title' => $achievementData['title'],
                    'description' => $achievementData['description'],
                    'category' => $serviceData['categories'][0] ?? 'Architecture',
                    'cover_photo' => $achievementData['cover_photo'],
                    'gallery_photos' => $achievementData['gallery_photos'],
                    'youtube_link' => $achievementData['youtube_link'] ?? null,
                    'status' => 'active',
                    'date_obtained' => fake('fr_FR')->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
                ]);
            });
        }
    }

    /**
     * Obtenir les compétences selon la profession
     */
    private function getSkillsForProfession(string $profession): array
    {
        $skillsMap = [
            'Architecte 3D Senior' => ['3ds Max', 'AutoCAD', 'Revit', 'V-Ray', 'SketchUp', 'Photoshop', 'Lumion'],
            'Designer d\'Intérieur 3D' => ['3ds Max', 'V-Ray', 'SketchUp', 'AutoCAD', 'Photoshop', 'Corona Renderer'],
            'Spécialiste Animation 3D' => ['Cinema 4D', 'After Effects', 'Blender', '3ds Max', 'Maya', 'Premiere Pro'],
            'Expert Modélisation Produit' => ['Blender', 'KeyShot', 'SolidWorks', 'Rhino', 'Photoshop', 'Substance Painter'],
            'Créateur d\'Environnements 3D' => ['Blender', 'Unreal Engine', 'Substance Designer', 'World Machine', 'Houdini'],
            'Spécialiste VR/AR' => ['Unity', 'Unreal Engine', 'Blender', 'C#', 'JavaScript', 'WebXR']
        ];

        return $skillsMap[$profession] ?? ['3ds Max', 'Blender', 'Photoshop'];
    }

    /**
     * Générer des liens portfolio
     */
    private function generatePortfolioLinks(string $firstName): array
    {
        return [
            ['title' => 'Portfolio Principal', 'url' => "https://portfolio-{$firstName}.com"],
            ['title' => 'Behance', 'url' => "https://behance.net/{$firstName}"],
            ['title' => 'ArtStation', 'url' => "https://artstation.com/{$firstName}"],
        ];
    }

    /**
     * Obtenir les services offerts selon la profession
     */
    private function getServicesForProfession(string $profession): array
    {
        $servicesMap = [
            'Architecte 3D Senior' => ['Modélisation architecturale', 'Rendu photoréaliste', 'Plans 3D', 'Visualisation BIM'],
            'Designer d\'Intérieur 3D' => ['Design d\'intérieur', 'Aménagement d\'espace', 'Rendu d\'intérieur', 'Mobilier 3D'],
            'Spécialiste Animation 3D' => ['Animation 3D', 'Motion Design', 'Présentation animée', 'Visite virtuelle'],
            'Expert Modélisation Produit' => ['Modélisation produit', 'Rendu produit', 'Packaging 3D', 'Catalogue 3D'],
            'Créateur d\'Environnements 3D' => ['Environnements 3D', 'Paysages virtuels', 'Texturing', 'Éclairage 3D'],
            'Spécialiste VR/AR' => ['Expérience VR', 'Application AR', 'Visite virtuelle', 'Formation immersive']
        ];

        return $servicesMap[$profession] ?? ['Modélisation 3D', 'Rendu 3D'];
    }

    /**
     * Obtenir les templates de services selon la profession
     */
    private function getServiceTemplatesForProfession(string $profession): array
    {
        $templates = [
            'Architecte 3D Senior' => [
                [
                    'title' => 'Modélisation Architecturale Complète',
                    'description' => 'Création de modèles 3D détaillés pour projets architecturaux résidentiels et commerciaux. Incluant plans, élévations et coupes techniques.',
                    'price' => 2500.00,
                    'execution_time' => '2-3 semaines',
                    'concepts' => 3,
                    'revisions' => 2,
                    'categories' => ['Architecture 3D', 'Modélisation 3D'],
                    'image' => 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&h=600&fit=crop' // Architecture 3D moderne
                ],
                [
                    'title' => 'Rendu Photoréaliste Extérieur',
                    'description' => 'Création de rendus extérieurs photoréalistes avec éclairage naturel, végétation et environnement contextuel.',
                    'price' => 1800.00,
                    'execution_time' => '1-2 semaines',
                    'concepts' => 2,
                    'revisions' => 3,
                    'categories' => ['Architecture 3D', 'Rendu'],
                    'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop' // Rendu architectural extérieur
                ],
                [
                    'title' => 'Plans 3D Interactifs',
                    'description' => 'Développement de plans 3D interactifs permettant une navigation immersive dans le projet architectural.',
                    'price' => 3200.00,
                    'execution_time' => '3-4 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Architecture 3D', 'Interactif'],
                    'image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=600&fit=crop' // Plans 3D et modélisation
                ],
                [
                    'title' => 'Modélisation BIM Avancée',
                    'description' => 'Création de modèles BIM complets avec informations techniques détaillées pour la construction et la gestion.',
                    'price' => 4500.00,
                    'execution_time' => '4-5 semaines',
                    'concepts' => 1,
                    'revisions' => 2,
                    'categories' => ['Architecture 3D', 'BIM'],
                    'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop' // BIM et modélisation technique
                ]
            ],
            'Designer d\'Intérieur 3D' => [
                [
                    'title' => 'Design d\'Intérieur Résidentiel',
                    'description' => 'Conception complète d\'espaces intérieurs résidentiels avec mobilier, éclairage et décoration personnalisés.',
                    'price' => 1500.00,
                    'execution_time' => '2 semaines',
                    'concepts' => 3,
                    'revisions' => 3,
                    'categories' => ['Design d\'intérieur', 'Résidentiel'],
                    'image' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop' // Design intérieur moderne
                ],
                [
                    'title' => 'Aménagement d\'Espace Commercial',
                    'description' => 'Optimisation et design d\'espaces commerciaux pour maximiser l\'expérience client et la fonctionnalité.',
                    'price' => 2200.00,
                    'execution_time' => '2-3 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Design d\'intérieur', 'Commercial'],
                    'image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600&fit=crop' // Espace commercial moderne
                ],
                [
                    'title' => 'Rendu d\'Intérieur Haut de Gamme',
                    'description' => 'Création de rendus d\'intérieur photoréalistes avec attention particulière aux matériaux et à l\'éclairage.',
                    'price' => 1200.00,
                    'execution_time' => '1-2 semaines',
                    'concepts' => 4,
                    'revisions' => 3,
                    'categories' => ['Design d\'intérieur', 'Rendu'],
                    'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop' // Rendu intérieur luxueux
                ],
                [
                    'title' => 'Mobilier Sur Mesure 3D',
                    'description' => 'Conception et modélisation de mobilier sur mesure adapté aux besoins spécifiques du client.',
                    'price' => 800.00,
                    'execution_time' => '1 semaine',
                    'concepts' => 3,
                    'revisions' => 2,
                    'categories' => ['Design d\'intérieur', 'Mobilier'],
                    'image' => 'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&h=600&fit=crop' // Mobilier design moderne
                ]
            ]
        ];

        // Ajouter les autres professions...
        $templates = array_merge($templates, $this->getAdditionalServiceTemplates(), $this->getCompleteServiceTemplates());

        return $templates[$profession] ?? $templates['Architecte 3D Senior'];
    }

    /**
     * Obtenir les templates de services additionnels
     */
    private function getAdditionalServiceTemplates(): array
    {
        return [
            'Spécialiste Animation 3D' => [
                [
                    'title' => 'Animation Architecturale 3D',
                    'description' => 'Création d\'animations 3D immersives pour présenter vos projets architecturaux de manière dynamique et engageante.',
                    'price' => 3500.00,
                    'execution_time' => '3-4 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Animation', 'Architecture 3D'],
                    'image' => 'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=600&fit=crop' // Animation et motion design
                ],
                [
                    'title' => 'Motion Design pour Présentation',
                    'description' => 'Conception de présentations animées professionnelles avec effets visuels et transitions fluides.',
                    'price' => 1800.00,
                    'execution_time' => '2 semaines',
                    'concepts' => 3,
                    'revisions' => 3,
                    'categories' => ['Animation', 'Motion Design'],
                    'image' => 'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=600&fit=crop' // Écrans et présentation digitale
                ],
                [
                    'title' => 'Visite Virtuelle Interactive',
                    'description' => 'Développement de visites virtuelles interactives permettant une exploration immersive des espaces.',
                    'price' => 4200.00,
                    'execution_time' => '4-5 semaines',
                    'concepts' => 1,
                    'revisions' => 2,
                    'categories' => ['Animation', 'Réalité virtuelle'],
                    'image' => 'https://images.unsplash.com/photo-1592478411213-6153e4ebc696?w=800&h=600&fit=crop' // VR et réalité virtuelle
                ],
                [
                    'title' => 'Animation de Personnages 3D',
                    'description' => 'Animation de personnages 3D pour présentations, formations ou contenus marketing.',
                    'price' => 2800.00,
                    'execution_time' => '3 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Animation', 'Personnage 3D'],
                    'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop' // Animation de personnages
                ]
            ],
            'Expert Modélisation Produit' => [
                [
                    'title' => 'Modélisation Produit E-commerce',
                    'description' => 'Création de modèles 3D haute qualité pour catalogues e-commerce avec rendus photoréalistes.',
                    'price' => 1200.00,
                    'execution_time' => '1-2 semaines',
                    'concepts' => 3,
                    'revisions' => 3,
                    'categories' => ['Produit 3D', 'E-commerce'],
                    'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=600&fit=crop' // Produits 3D e-commerce
                ],
                [
                    'title' => 'Packaging 3D Interactif',
                    'description' => 'Conception de packaging 3D avec visualisation interactive pour marketing et présentation produit.',
                    'price' => 1800.00,
                    'execution_time' => '2-3 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Produit 3D', 'Packaging'],
                    'image' => 'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=800&h=600&fit=crop' // Packaging et design produit
                ],
                [
                    'title' => 'Catalogue Produit 3D',
                    'description' => 'Développement de catalogues produits 3D interactifs pour présentation commerciale professionnelle.',
                    'price' => 3500.00,
                    'execution_time' => '3-4 semaines',
                    'concepts' => 1,
                    'revisions' => 2,
                    'categories' => ['Produit 3D', 'Catalogue'],
                    'image' => 'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop' // Catalogue et présentation produit
                ],
                [
                    'title' => 'Visualisation Technique Produit',
                    'description' => 'Création de visualisations techniques détaillées pour documentation et formation produit.',
                    'price' => 2200.00,
                    'execution_time' => '2-3 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Produit 3D', 'Technique'],
                    'image' => 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&h=600&fit=crop' // Visualisation technique
                ]
            ]
        ];
    }

    /**
     * Compléter les templates avec les dernières professions
     */
    private function getCompleteServiceTemplates(): array
    {
        return [
            'Créateur d\'Environnements 3D' => [
                [
                    'title' => 'Environnements 3D Immersifs',
                    'description' => 'Création d\'environnements 3D détaillés et immersifs pour jeux, films ou expériences virtuelles.',
                    'price' => 4000.00,
                    'execution_time' => '4-5 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Environnement 3D', 'Game Art'],
                    'image' => 'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop' // Environnements 3D immersifs
                ],
                [
                    'title' => 'Paysagisme Virtuel',
                    'description' => 'Conception de paysages virtuels réalistes avec végétation, terrain et éclairage naturel.',
                    'price' => 2800.00,
                    'execution_time' => '3 semaines',
                    'concepts' => 3,
                    'revisions' => 2,
                    'categories' => ['Environnement 3D', 'Paysage'],
                    'image' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop' // Paysages virtuels
                ],
                [
                    'title' => 'Texturing et Matériaux Avancés',
                    'description' => 'Création de textures et matériaux haute qualité pour environnements et objets 3D.',
                    'price' => 1500.00,
                    'execution_time' => '1-2 semaines',
                    'concepts' => 4,
                    'revisions' => 3,
                    'categories' => ['Environnement 3D', 'Texturing'],
                    'image' => 'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop' // Textures et matériaux
                ],
                [
                    'title' => 'Éclairage 3D Professionnel',
                    'description' => 'Mise en place d\'éclairage 3D professionnel pour créer des ambiances et atmosphères uniques.',
                    'price' => 2000.00,
                    'execution_time' => '2 semaines',
                    'concepts' => 3,
                    'revisions' => 2,
                    'categories' => ['Environnement 3D', 'Éclairage'],
                    'image' => 'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop' // Éclairage professionnel
                ]
            ],
            'Spécialiste VR/AR' => [
                [
                    'title' => 'Expérience VR Immersive',
                    'description' => 'Développement d\'expériences de réalité virtuelle complètes avec interactions et navigation intuitive.',
                    'price' => 6000.00,
                    'execution_time' => '5-6 semaines',
                    'concepts' => 1,
                    'revisions' => 2,
                    'categories' => ['Réalité virtuelle/augmentée', 'Expérience'],
                    'image' => 'https://images.unsplash.com/photo-1592478411213-6153e4ebc696?w=800&h=600&fit=crop' // Expérience VR immersive
                ],
                [
                    'title' => 'Application AR Marketing',
                    'description' => 'Création d\'applications de réalité augmentée pour marketing et présentation produit innovante.',
                    'price' => 4500.00,
                    'execution_time' => '4 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Réalité virtuelle/augmentée', 'Marketing'],
                    'image' => 'https://images.unsplash.com/photo-1535223289827-42f1e9919769?w=800&h=600&fit=crop' // Application AR marketing
                ],
                [
                    'title' => 'Formation VR Interactive',
                    'description' => 'Développement de modules de formation en réalité virtuelle pour apprentissage immersif.',
                    'price' => 5500.00,
                    'execution_time' => '4-5 semaines',
                    'concepts' => 1,
                    'revisions' => 2,
                    'categories' => ['Réalité virtuelle/augmentée', 'Formation'],
                    'image' => 'https://images.unsplash.com/photo-1617802690992-15d93263d3a9?w=800&h=600&fit=crop' // Formation VR interactive
                ],
                [
                    'title' => 'Visite Virtuelle 360°',
                    'description' => 'Création de visites virtuelles 360° interactives pour immobilier, tourisme ou patrimoine.',
                    'price' => 3500.00,
                    'execution_time' => '3 semaines',
                    'concepts' => 2,
                    'revisions' => 2,
                    'categories' => ['Réalité virtuelle/augmentée', 'Visite'],
                    'image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=600&fit=crop' // Visite virtuelle 360°
                ]
            ]
        ];
    }

    /**
     * Obtenir les templates de réalisations pour un service
     */
    private function getAchievementTemplatesForService(array $serviceData): array
    {
        $serviceTitle = $serviceData['title'];
        $category = $serviceData['categories'][0] ?? 'Architecture';

        // Templates de base selon le type de service
        $baseTemplates = [
            'Modélisation' => [
                'Villa Contemporaine - Projet Résidentiel',
                'Immeuble de Bureaux - Centre d\'Affaires',
                'Complexe Commercial - Zone Urbaine',
                'Rénovation Patrimoine - Bâtiment Historique'
            ],
            'Rendu' => [
                'Rendu Extérieur - Villa de Luxe',
                'Visualisation Nocturne - Éclairage Architectural',
                'Rendu Aérien - Vue d\'Ensemble Projet',
                'Ambiance Saisonnière - Intégration Paysagère'
            ],
            'Animation' => [
                'Animation Survol - Présentation Projet',
                'Parcours Caméra - Visite Guidée',
                'Animation Temporelle - Évolution Projet',
                'Présentation Interactive - Client Final'
            ],
            'Design' => [
                'Aménagement Salon - Style Contemporain',
                'Cuisine Ouverte - Design Fonctionnel',
                'Chambre Parentale - Ambiance Cosy',
                'Espace de Travail - Bureau Moderne'
            ],
            'Produit' => [
                'Packaging Premium - Produit de Luxe',
                'Visualisation Technique - Composants',
                'Rendu E-commerce - Catalogue Produit',
                'Animation Produit - Démonstration Usage'
            ],
            'Environnement' => [
                'Paysage Naturel - Environnement Forestier',
                'Scène Urbaine - Quartier Moderne',
                'Environnement Fantastique - Monde Virtuel',
                'Paysage Industriel - Zone d\'Activité'
            ],
            'VR/AR' => [
                'Expérience VR - Visite Immersive',
                'Application AR - Présentation Produit',
                'Formation VR - Module Interactif',
                'Démonstration AR - Showroom Virtuel'
            ]
        ];

        // Déterminer le type de service
        $serviceType = 'Modélisation'; // Par défaut
        if (strpos($serviceTitle, 'Rendu') !== false) $serviceType = 'Rendu';
        elseif (strpos($serviceTitle, 'Animation') !== false) $serviceType = 'Animation';
        elseif (strpos($serviceTitle, 'Design') !== false || strpos($serviceTitle, 'Intérieur') !== false) $serviceType = 'Design';
        elseif (strpos($serviceTitle, 'Produit') !== false) $serviceType = 'Produit';
        elseif (strpos($serviceTitle, 'Environnement') !== false) $serviceType = 'Environnement';
        elseif (strpos($serviceTitle, 'VR') !== false || strpos($serviceTitle, 'AR') !== false) $serviceType = 'VR/AR';

        $titles = $baseTemplates[$serviceType] ?? $baseTemplates['Modélisation'];

        $achievements = [];
        foreach ($titles as $index => $title) {
            $achievements[] = [
                'title' => $title,
                'description' => $this->generateAchievementDescription($title, $serviceType),
                'cover_photo' => $this->getImageForServiceType($serviceType, $index),
                'gallery_photos' => [
                    $this->getImageForServiceType($serviceType, $index, 1),
                    $this->getImageForServiceType($serviceType, $index, 2),
                    $this->getImageForServiceType($serviceType, $index, 3)
                ],
                'youtube_link' => $index === 0 ? "https://youtube.com/watch?v=" . fake('fr_FR')->regexify('[A-Za-z0-9]{11}') : null
            ];
        }

        return $achievements;
    }

    /**
     * Générer une description pour une réalisation
     */
    private function generateAchievementDescription(string $title, string $serviceType): string
    {
        $descriptions = [
            'Modélisation' => [
                'Création d\'un modèle 3D détaillé avec attention particulière aux proportions et aux détails architecturaux.',
                'Modélisation complète incluant structure, façades et aménagements extérieurs.',
                'Développement d\'un modèle technique précis respectant les normes de construction.',
                'Réalisation d\'une maquette 3D fidèle aux plans architecturaux originaux.'
            ],
            'Rendu' => [
                'Production d\'images photoréalistes avec éclairage naturel et matériaux authentiques.',
                'Création de visuels haute qualité mettant en valeur l\'architecture et l\'environnement.',
                'Rendu professionnel avec post-production soignée pour un résultat impactant.',
                'Visualisation réaliste intégrant végétation, mobilier urbain et contexte environnemental.'
            ],
            'Animation' => [
                'Réalisation d\'une animation fluide présentant le projet sous tous ses angles.',
                'Création d\'un parcours caméra dynamique révélant progressivement l\'architecture.',
                'Animation professionnelle avec transitions soignées et rythme adapté.',
                'Production d\'une présentation animée engageante pour clients et investisseurs.'
            ],
            'Design' => [
                'Conception d\'un espace fonctionnel alliant esthétique et praticité.',
                'Aménagement personnalisé respectant les besoins et le style de vie du client.',
                'Design d\'intérieur harmonieux avec sélection minutieuse des matériaux et couleurs.',
                'Création d\'une ambiance unique à travers mobilier, éclairage et décoration.'
            ]
        ];

        $typeDescriptions = $descriptions[$serviceType] ?? $descriptions['Modélisation'];
        return fake('fr_FR')->randomElement($typeDescriptions) . ' ' .
               'Projet réalisé avec expertise technique et créativité, livré dans les délais convenus avec un résultat dépassant les attentes du client.';
    }

    /**
     * Obtenir une image appropriée selon le type de service
     */
    private function getImageForServiceType(string $serviceType, int $index, int $variant = 0): string
    {
        $imageMap = [
            'Modélisation' => [
                'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&h=600&fit=crop', // Architecture 3D moderne
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop', // Bâtiment futuriste
                'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?w=800&h=600&fit=crop', // Structure architecturale
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop'  // Rendu architectural
            ],
            'Rendu' => [
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop', // Rendu extérieur
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop', // Intérieur moderne
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop', // Design intérieur
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop'  // Architecture moderne
            ],
            'Animation' => [
                'https://images.unsplash.com/photo-1551434678-e076c223a692?w=800&h=600&fit=crop', // Technologie et écrans
                'https://images.unsplash.com/photo-1611224923853-80b023f02d71?w=800&h=600&fit=crop', // Présentation digitale
                'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop', // Animation et mouvement
                'https://images.unsplash.com/photo-1592478411213-6153e4ebc696?w=800&h=600&fit=crop'  // VR et immersion
            ],
            'Design' => [
                'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?w=800&h=600&fit=crop', // Design intérieur moderne
                'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=800&h=600&fit=crop', // Intérieur luxueux
                'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&h=600&fit=crop', // Espace commercial
                'https://images.unsplash.com/photo-1555041469-a586c61ea9bc?w=800&h=600&fit=crop'  // Mobilier design
            ],
            'Produit' => [
                'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=600&fit=crop', // Produits 3D
                'https://images.unsplash.com/photo-1586953208448-b95a79798f07?w=800&h=600&fit=crop', // Packaging design
                'https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&h=600&fit=crop', // Catalogue produit
                'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=800&h=600&fit=crop'  // Visualisation technique
            ],
            'Environnement' => [
                'https://images.unsplash.com/photo-1518709268805-4e9042af2176?w=800&h=600&fit=crop', // Environnement immersif
                'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&h=600&fit=crop', // Paysage virtuel
                'https://images.unsplash.com/photo-1558618666-fcd25c85cd64?w=800&h=600&fit=crop', // Textures et matériaux
                'https://images.unsplash.com/photo-1600607687939-ce8a6c25118c?w=800&h=600&fit=crop'  // Éclairage professionnel
            ],
            'VR/AR' => [
                'https://images.unsplash.com/photo-1592478411213-6153e4ebc696?w=800&h=600&fit=crop', // VR immersive
                'https://images.unsplash.com/photo-1535223289827-42f1e9919769?w=800&h=600&fit=crop', // AR marketing
                'https://images.unsplash.com/photo-1617802690992-15d93263d3a9?w=800&h=600&fit=crop', // Formation VR
                'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?w=800&h=600&fit=crop'  // Visite virtuelle 360°
            ]
        ];

        $images = $imageMap[$serviceType] ?? $imageMap['Modélisation'];
        $imageIndex = ($index + $variant) % count($images);

        return $images[$imageIndex];
    }
}
