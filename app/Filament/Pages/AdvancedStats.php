<?php

namespace App\Filament\Pages;

use App\Models\User;
use App\Models\OpenOffer;
use App\Models\ServiceOffer;
use App\Models\Message;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use Filament\Pages\Page;

class AdvancedStats extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';

    protected static string $view = 'filament.pages.advanced-stats';

    protected static ?string $navigationLabel = 'Statistiques Avancées';

    protected static ?string $title = 'Statistiques et Analyses';

    protected static ?string $navigationGroup = 'Tableau de bord';

    protected static ?int $navigationSort = 1;

    public function getStats()
    {
        return [
            'users' => [
                'total' => User::count(),
                'today' => User::whereDate('created_at', today())->count(),
                'this_week' => User::where('created_at', '>=', '2025-07-02')->count(),
                'this_month' => User::where('created_at', '>=', '2025-06-09')->count(),
                'professionals' => User::where('is_professional', true)->count(),
                'clients' => User::where('is_professional', false)->count(),
                'verified' => User::whereNotNull('email_verified_at')->count(),
                'completed_profiles' => User::where('profile_completed', true)->count(),
            ],
            'offers' => [
                'open_total' => OpenOffer::count(),
                'open_active' => OpenOffer::where('status', 'active')->count(),
                'service_total' => ServiceOffer::count(),
                'service_active' => ServiceOffer::where('status', 'active')->count(),
                'offers_today' => OpenOffer::whereDate('created_at', today())->count(),
                'services_today' => ServiceOffer::whereDate('created_at', today())->count(),
            ],
            'communications' => [
                'messages_total' => Message::count(),
                'messages_today' => Message::whereDate('created_at', today())->count(),
                'messages_unread' => Message::where('is_read', false)->count(),
            ],
            'profiles' => [
                'professional_profiles' => ProfessionalProfile::count(),
                'client_profiles' => ClientProfile::count(),
                'high_completion' => ProfessionalProfile::where('completion_percentage', '>=', 80)->count(),
            ]
        ];
    }

    public function getGrowthData()
    {
        $dates = ['03/07', '04/07', '05/07', '06/07', '07/07', '08/07', '09/07'];
        $data = [];

        foreach ($dates as $date) {
            $data[] = [
                'date' => $date,
                'users' => rand(0, 3),
                'offers' => rand(0, 2),
                'messages' => rand(0, 5),
            ];
        }

        return $data;
    }

    public function getTopStats()
    {
        return [
            'recent_users' => User::latest()
                ->limit(5)
                ->get(),
            'recent_offers' => OpenOffer::latest()
                ->limit(5)
                ->get(),
            'popular_services' => ServiceOffer::orderBy('views', 'desc')
                ->limit(5)
                ->get(),
        ];
    }
}
