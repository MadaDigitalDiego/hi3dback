<?php

namespace App\Filament\Widgets;

use App\Models\OpenOffer;
use App\Models\ServiceOffer;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class OffersChart extends ChartWidget
{
    protected static ?string $heading = 'Évolution des offres (30 derniers jours)';
    
    protected static ?int $sort = 3;

    protected function getData(): array
    {
        $openOffersData = [];
        $serviceOffersData = [];
        $labels = [];
        
        // Générer les données pour les 30 derniers jours
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            
            $openCount = OpenOffer::whereDate('created_at', $date->format('Y-m-d'))->count();
            $serviceCount = ServiceOffer::whereDate('created_at', $date->format('Y-m-d'))->count();
            
            $openOffersData[] = $openCount;
            $serviceOffersData[] = $serviceCount;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Offres ouvertes',
                    'data' => $openOffersData,
                    'borderColor' => 'rgb(34, 197, 94)',
                    'backgroundColor' => 'rgba(34, 197, 94, 0.1)',
                    'fill' => true,
                ],
                [
                    'label' => 'Services proposés',
                    'data' => $serviceOffersData,
                    'borderColor' => 'rgb(168, 85, 247)',
                    'backgroundColor' => 'rgba(168, 85, 247, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
