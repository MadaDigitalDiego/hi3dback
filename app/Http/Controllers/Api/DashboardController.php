<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;
use App\Models\OpenOffer;
use App\Models\OfferApplication;
use App\Models\User;
use App\Models\FreelanceProfile;
use App\Models\CompanyProfile;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard data for the authenticated user
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getDashboardData(Request $request): JsonResponse
    {
        // try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        // Données communes pour tous les utilisateurs
        $data = [
            'user' => $user,
        ];

        // Données spécifiques selon le type d'utilisateur
        if ($user->is_professional) {
            $data = array_merge($data, $this->getProfessionalDashboardData($user));
        } else {
            $data = array_merge($data, $this->getClientDashboardData($user));
        }

        return response()->json($data, 200);
        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de la récupération des données du tableau de bord: ' . $e->getMessage());
        //     return response()->json(['message' => 'Erreur lors de la récupération des données du tableau de bord.'], 500);
        // }
    }

    public function getAllACtivity(Request $request): JsonResponse
    {
        // try {
        $user = $request->user();

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non authentifié.'], 401);
        }

        // Données communes pour tous les utilisateurs
        $data = [
            // 'user' => $user,
        ];

        // Données spécifiques selon le type d'utilisateur
        if ($user->is_professional) {
            $data = array_merge($data, $this->getProfessionalACtivity($user));
        } else {
            $data = array_merge($data, $this->getClientActivity($user));
        }

        return response()->json($data, 200);
        // } catch (\Exception $e) {
        //     Log::error('Erreur lors de la récupération des données du tableau de bord: ' . $e->getMessage());
        //     return response()->json(['message' => 'Erreur lors de la récupération des données du tableau de bord.'], 500);
        // }
    }

    /**
     * Get dashboard data for a professional user
     *
     * @param User $user
     * @return array
     */
    private function getProfessionalDashboardData(User $user): array
    {
        // Utiliser directement l'ancienne structure car la nouvelle n'est pas encore disponible
        $profile = $user->freelanceProfile;

        // Si aucun profil n'est trouvé, retourner des données par défaut
        if (!$profile) {
            return [
                'stats' => [
                    'activeProjects' => 0,
                    'earnings' => '0 €',
                    'rating' => 0,
                    'completionRate' => 0,
                ],
                'projects' => [],
                'activities' => [],
                'profile' => null
            ];
        }

        // Utiliser directement le rating du profil
        $rating = $profile->rating ?? 0;

        // Récupérer les offres attribuées au professionnel
        $attributedOffers = $user->attributedOpenOffers()
            ->with('user')
            ->get();

        // Calculer les statistiques
        $activeProjects = $attributedOffers->where('status', 'in_progress')->count();

        // Calculer les revenus (à adapter selon votre modèle de données)
        // $earnings = $attributedOffers->where('status', 'completed')->sum('budget');

        $earnings = $attributedOffers
        ->where('status', 'completed')
        ->sum(function ($offer) {
            return is_numeric($offer->budget) ? (float) $offer->budget : 0;
        });

        // Calculer la note moyenne (à adapter selon votre modèle de données)
        // Déjà calculé plus haut

        // Calculer le taux de complétion des projets
        $totalProjects = $attributedOffers->whereIn('status', ['in_progress', 'completed'])->count();
        $completedProjects = $attributedOffers->where('status', 'completed')->count();
        $completionRate = $totalProjects > 0 ? round(($completedProjects / $totalProjects) * 100) : 0;

        // Formater les projets pour l'affichage
        $projects = [];
        foreach ($attributedOffers as $offer) {
            $projects[] = [
                'id' => $offer->id,
                'title' => $offer->title,
                'description' => $offer->description,
                'budget' => $offer->budget . ' €',
                'deadline' => $offer->deadline,
                'status' => $offer->status,
                'client' => [
                    'id' => $offer->user->id,
                    'name' => $offer->user->first_name . ' ' . $offer->user->last_name,
                    'avatar' => $this->getUserAvatar($offer->user),
                ],
            ];
        }

        // Récupérer les activités récentes (à adapter selon votre modèle de données)
        $activities = [];

        // Récupérer les candidatures récentes
        $recentApplications = OfferApplication::where('professional_profile_id', $profile->id)
            ->with(['openOffer', 'openOffer.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentApplications as $application) {
            $activities[] = [
                'id' => $application->id,
                'title' => 'Candidature à ' . $application->openOffer->title,
                'description' => 'Statut: ' . $this->translateApplicationStatus($application->status),
                'timestamp' => $application->created_at->toISOString(),
                'icon' => 'Briefcase',
                'iconBackground' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
            ];
        }

        // Récupérer les offres récemment attribuées
        $recentAttributions = $attributedOffers
            ->sortByDesc('updated_at')
            ->take(5);
        //'attr_' .
        foreach ($recentAttributions as $attribution) {
            $activities[] = [
                'id' => $attribution->id,
                'title' => 'Projet attribué: ' . $attribution->title,
                'description' => 'Client: ' . $attribution->user->first_name . ' ' . $attribution->user->last_name,
                'timestamp' => $attribution->updated_at->toISOString(),
                'icon' => 'CheckCircle',
                'iconBackground' => 'bg-green-100',
                'iconColor' => 'text-green-600',
                'user' => [
                    'name' => $attribution->user->first_name . ' ' . $attribution->user->last_name,
                    'avatar' => $this->getUserAvatar($attribution->user),
                ],
            ];
        }

        // Trier les activités par date
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Utiliser directement le profil professionnel
        $profileData = $profile ? $profile->toArray() : null;

        return [
            'stats' => [
                'activeProjects' => $activeProjects,
                'earnings' => $earnings . ' €',
                'rating' => $rating,
                'completionRate' => $completionRate,
            ],
            'projects' => $projects,
            'activities' => $activities,
            'profile' => $profileData
        ];
    }

    /**
     * Get dashboard data for a client user
     *
     * @param User $user
     * @return array
     */
    private function getClientDashboardData(User $user): array
    {
        // Utiliser directement l'ancienne structure car la nouvelle n'est pas encore disponible
        $profile = $user->companyProfile;
        // Si aucun profil n'est trouvé, retourner des données par défaut
        if (!$profile) {
            return [
                'stats' => [
                    'activeProjects' => 0,
                    'totalSpent' => '0 €',
                    'connectedProfessionals' => 0,
                    'projectsCompleted' => 0,
                ],
                'projects' => [],
                'activities' => [],
                'recommendedProfessionals' => [],
                'profile' => null
            ];
        }

        // Récupérer les offres du client
        $offers = OpenOffer::where('user_id', $user->id)
            ->with(['applications', 'applications.freelanceProfile.user'])
            ->get();

        // Calculer les statistiques
        $activeProjects = $offers->whereIn('status', ['open', 'in_progress'])->count();
        // $totalSpent = $offers->where('status', 'completed')->sum('budget');
        $totalSpent = $offers
                    ->where('status', 'completed')
                    ->sum(function ($offer) {
                        return is_numeric($offer->budget) ? (float) $offer->budget : 0;
                    });

        $projectsCompleted = $offers->where('status', 'completed')->count();


        // Calculer le nombre de professionnels connectés
        $connectedProfessionals = DB::table('offer_applications')
            ->join('open_offers', 'offer_applications.open_offer_id', '=', 'open_offers.id')
            ->where('open_offers.user_id', $user->id)
            ->where('offer_applications.status', 'accepted')
            ->distinct('professional_profile_id')
            ->count('professional_profile_id');

        // Formater les projets pour l'affichage
        $projects = [];
        foreach ($offers as $offer) {
            // Trouver le professionnel attribué si disponible
            $professional = null;
            if ($offer->status === 'in_progress' || $offer->status === 'completed') {
                $acceptedApplication = $offer->applications->where('status', 'accepted')->first();
                if ($acceptedApplication) {
                    $professionalUser = $acceptedApplication->freelanceProfile->user;
                    $professional = [
                        'id' => $professionalUser->id,
                        'name' => $professionalUser->first_name . ' ' . $professionalUser->last_name,
                        'avatar' => $this->getUserAvatar($professionalUser),
                    ];
                }
            }

            $projects[] = [
                'id' => $offer->id,
                'title' => $offer->title,
                'description' => $offer->description,
                'budget' => $offer->budget . ' €',
                'deadline' => $offer->deadline,
                'status' => $offer->status,
                'professional' => $professional,
            ];
        }

        // Récupérer les activités récentes
        $activities = [];

        // Récupérer les candidatures récentes aux offres du client
        $recentApplications = OfferApplication::whereIn('open_offer_id', $offers->pluck('id'))
            ->with(['openOffer', 'freelanceProfile.user'])
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        foreach ($recentApplications as $application) {
            $activities[] = [
                'id' => $application->id,
                'title' => 'Nouvelle candidature pour ' . $application->openOffer->title,
                'description' => 'De: ' . $application->freelanceProfile->user->first_name . ' ' . $application->freelanceProfile->user->last_name,
                'timestamp' => $application->created_at->toISOString(),
                'icon' => 'Users',
                'iconBackground' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
                'user' => [
                    'name' => $application->freelanceProfile && $application->freelanceProfile->user ?
                        $application->freelanceProfile->user->first_name . ' ' . $application->freelanceProfile->user->last_name : 'Utilisateur inconnu',
                    'avatar' => $this->getUserAvatar($application->freelanceProfile ? $application->freelanceProfile->user : null),
                ],
            ];
        }

        // Récupérer les projets récemment complétés
        $recentCompletions = $offers->where('status', 'completed')
            ->sortByDesc('updated_at')
            ->take(3);
        //'comp_' .
        foreach ($recentCompletions as $completion) {
            $activities[] = [
                'id' => $completion->id,
                'title' => 'Projet terminé: ' . $completion->title,
                'description' => 'Budget: ' . $completion->budget . ' €',
                'timestamp' => $completion->updated_at->toISOString(),
                'icon' => 'CheckCircle',
                'iconBackground' => 'bg-green-100',
                'iconColor' => 'text-green-600',
            ];
        }

        // Trier les activités par date
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Récupérer des professionnels recommandés
        $recommendedProfessionals = FreelanceProfile::with('user')
            ->orderBy('rating', 'desc')
            ->take(3)
            ->get()
            ->map(function ($profile) {
                return [
                    'id' => $profile->user->id,
                    'name' => $profile->user->first_name . ' ' . $profile->user->last_name,
                    'title' => $profile->title ?? 'Artiste 3D',
                    'avatar' => $this->getUserAvatar($profile->user),
                    'rating' => $profile->rating ?? 0,
                    'hourlyRate' => ($profile->hourly_rate ?? 0) . ' €/h',
                ];
            });

        // Utiliser directement le profil client
        $profileData = $profile ? $profile->toArray() : null;

        return [
            'stats' => [
                'activeProjects' => $activeProjects,
                'totalSpent' => $totalSpent . ' €',
                'connectedProfessionals' => $connectedProfessionals,
                'projectsCompleted' => $projectsCompleted,
            ],
            'projects' => $projects,
            'activities' => $activities,
            'recommendedProfessionals' => $recommendedProfessionals,
            'profile' => $profileData
        ];
    }


    private function getProfessionalACtivity(User $user): array
    {
        // Utiliser directement l'ancienne structure car la nouvelle n'est pas encore disponible
        $profile = $user->freelanceProfile;

        // Si aucun profil n'est trouvé, retourner des données par défaut
        if (!$profile) {
            return [
                'activities' => [],
                'profile' => null
            ];
        }

        // Récupérer les offres attribuées au professionnel
        $attributedOffers = $user->attributedOpenOffers()
            ->with('user')
            ->get();
        // Récupérer les activités récentes (à adapter selon votre modèle de données)
        $activities = [];

        // Récupérer les candidatures récentes
        $recentApplications = OfferApplication::where('professional_profile_id', $profile->id)
            ->with(['openOffer', 'openOffer.user'])
            ->orderBy('created_at', 'desc')
            // ->take(5)
            ->get();

        foreach ($recentApplications as $application) {
            $activities[] = [
                'id' => $application->id,
                'title' => 'Candidature à ' . $application->openOffer->title,
                'description' => 'Statut: ' . $this->translateApplicationStatus($application->status),
                'timestamp' => $application->created_at->toISOString(),
                'icon' => 'Briefcase',
                'iconBackground' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
            ];
        }

        // Récupérer les offres récemment attribuées
        $recentAttributions = $attributedOffers
            ->sortByDesc('updated_at');
        // ->take(5);

        foreach ($recentAttributions as $attribution) {
            $activities[] = [
                'id' => $attribution->id,
                'title' => 'Projet attribué: ' . $attribution->title,
                'description' => 'Client: ' . $attribution->user->first_name . ' ' . $attribution->user->last_name,
                'timestamp' => $attribution->updated_at->toISOString(),
                'icon' => 'CheckCircle',
                'iconBackground' => 'bg-green-100',
                'iconColor' => 'text-green-600',
                'user' => [
                    'name' => $attribution->user->first_name . ' ' . $attribution->user->last_name,
                    'avatar' => $this->getUserAvatar($attribution->user),
                ],
            ];
        }

        // Trier les activités par date
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });

        // Utiliser directement le profil professionnel
        $profileData = $profile ? $profile->toArray() : null;

        return [
            'activities' => $activities,
            'profile' => $profileData
        ];
    }

    private function getClientActivity(User $user): array
    {
        // Utiliser directement l'ancienne structure car la nouvelle n'est pas encore disponible
        $profile = $user->companyProfile;

        // Si aucun profil n'est trouvé, retourner des données par défaut
        if (!$profile) {
            return [
                'activities' => [],
                'profile' => null
            ];
        }

        // Récupérer les offres du client
        $offers = OpenOffer::where('user_id', $user->id)
            ->with(['applications', 'applications.freelanceProfile.user'])
            ->get();
        // Récupérer les activités récentes
        $activities = [];

        // Récupérer les candidatures récentes aux offres du client
        $recentApplications = OfferApplication::whereIn('open_offer_id', $offers->pluck('id'))
            ->with(['openOffer', 'freelanceProfile.user'])
            ->orderBy('created_at', 'desc')
            // ->take(5)
            ->get();

        foreach ($recentApplications as $application) {
            $activities[] = [
                'id' => $application->id,
                'title' => 'Nouvelle candidature pour ' . $application->openOffer->title,
                'description' => 'De: ' . $application->freelanceProfile->user->first_name . ' ' . $application->freelanceProfile->user->last_name,
                'timestamp' => $application->created_at->toISOString(),
                'icon' => 'Users',
                'iconBackground' => 'bg-blue-100',
                'iconColor' => 'text-blue-600',
                'user' => [
                    'name' => $application->freelanceProfile && $application->freelanceProfile->user ?
                        $application->freelanceProfile->user->first_name . ' ' . $application->freelanceProfile->user->last_name : 'Utilisateur inconnu',
                    'avatar' => $this->getUserAvatar($application->freelanceProfile ? $application->freelanceProfile->user : null),
                ],
            ];
        }

        // Récupérer les projets récemment complétés
        $recentCompletions = $offers->where('status', 'completed')
            ->sortByDesc('updated_at');
        // ->take(3);

        foreach ($recentCompletions as $completion) {
            $activities[] = [
                'id' => $completion->id,
                'title' => 'Projet terminé: ' . $completion->title,
                'description' => 'Budget: ' . $completion->budget . ' €',
                'timestamp' => $completion->updated_at->toISOString(),
                'icon' => 'CheckCircle',
                'iconBackground' => 'bg-green-100',
                'iconColor' => 'text-green-600',
            ];
        }

        // Trier les activités par date
        usort($activities, function ($a, $b) {
            return strtotime($b['timestamp']) - strtotime($a['timestamp']);
        });
        // Utiliser directement le profil client
        $profileData = $profile ? $profile->toArray() : null;

        return [
            'activities' => $activities,
            'profile' => $profileData
        ];
    }

    /**
     * Translate application status to French
     *
     * @param string $status
     * @return string
     */
    private function translateApplicationStatus(string $status): string
    {
        $translations = [
            'pending' => 'En attente',
            'accepted' => 'Acceptée',
            'rejected' => 'Rejetée',
        ];

        return $translations[$status] ?? $status;
    }

    /**
     * Get user avatar from either new or old profile structure
     *
     * @param User|null $user
     * @return string|null
     */
    private function getUserAvatar(?User $user): ?string
    {
        // Si l'utilisateur est null, retourner null
        if (!$user) {
            return null;
        }

        // Utiliser directement l'ancienne structure
        if ($user->is_professional && $user->freelanceProfile && $user->freelanceProfile->avatar) {
            return $user->freelanceProfile->avatar;
        }

        // Essayer ensuite l'ancienne structure pour un client
        if (!$user->is_professional && $user->companyProfile && $user->companyProfile->avatar) {
            return $user->companyProfile->avatar;
        }

        return null;
    }
}
