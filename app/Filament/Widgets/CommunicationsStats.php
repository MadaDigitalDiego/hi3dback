<?php

namespace App\Filament\Widgets;

use App\Models\Message;
use App\Models\Contact;
use App\Models\OfferApplication;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CommunicationsStats extends BaseWidget
{
    protected static ?int $sort = 4;

    protected function getStats(): array
    {
        return [
            Stat::make('Messages Aujourd\'hui', Message::whereDate('created_at', today())->count())
                ->description('Messages échangés aujourd\'hui')
                ->descriptionIcon('heroicon-m-chat-bubble-left-right')
                ->color('info'),

            Stat::make('Candidatures Actives', OfferApplication::where('status', 'pending')->count())
                ->description('Candidatures en attente')
                ->descriptionIcon('heroicon-m-paper-airplane')
                ->color('warning'),

            Stat::make('Total Contacts', Contact::count())
                ->description('Nombre total de contacts')
                ->descriptionIcon('heroicon-m-envelope')
                ->color('success'),

            Stat::make('Messages Non Lus', Message::where('is_read', false)->count())
                ->description('Messages non lus')
                ->descriptionIcon('heroicon-m-eye-slash')
                ->color('warning'),
        ];
    }
}
