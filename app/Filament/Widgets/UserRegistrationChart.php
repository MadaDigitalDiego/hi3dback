<?php

namespace App\Filament\Widgets;

use App\Models\User;
use Filament\Widgets\ChartWidget;
use Illuminate\Support\Carbon;

class UserRegistrationChart extends ChartWidget
{
    protected static ?string $heading = 'Inscriptions des 30 derniers jours';
    
    protected static ?int $sort = 2;

    protected function getData(): array
    {
        $data = [];
        $labels = [];
        
        // Générer les données pour les 30 derniers jours
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i);
            $labels[] = $date->format('d/m');
            
            $count = User::whereDate('created_at', $date->format('Y-m-d'))->count();
            $data[] = $count;
        }

        return [
            'datasets' => [
                [
                    'label' => 'Nouvelles inscriptions',
                    'data' => $data,
                    'borderColor' => 'rgb(59, 130, 246)',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
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
