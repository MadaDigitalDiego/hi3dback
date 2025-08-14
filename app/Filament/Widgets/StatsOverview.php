<?php

namespace App\Filament\Widgets;

use App\Models\User;
use App\Models\OpenOffer;
use App\Models\ServiceOffer;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Utilisateurs', User::count())
                ->description('Nombre total d\'utilisateurs inscrits')
                ->descriptionIcon('heroicon-m-users')
                ->color('success'),

            Stat::make('Professionnels', User::where('is_professional', true)->count())
                ->description('Comptes professionnels actifs')
                ->descriptionIcon('heroicon-m-briefcase')
                ->color('info'),

            Stat::make('Clients', User::where('is_professional', false)->count())
                ->description('Comptes clients')
                ->descriptionIcon('heroicon-m-user')
                ->color('warning'),

            Stat::make('Offres Ouvertes', OpenOffer::where('status', 'active')->count())
                ->description('Offres actuellement ouvertes')
                ->descriptionIcon('heroicon-m-document-text')
                ->color('primary'),

            Stat::make('Services Proposés', ServiceOffer::where('status', 'active')->count())
                ->description('Services disponibles')
                ->descriptionIcon('heroicon-m-cog')
                ->color('success'),

            Stat::make('Profils Complétés', User::where('profile_completed', true)->count())
                ->description('Utilisateurs avec profil complété')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success'),

            Stat::make('Administrateurs', User::whereIn('role', ['admin', 'super_admin'])->count())
                ->description('Comptes administrateurs')
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('danger'),
        ];
    }
}
