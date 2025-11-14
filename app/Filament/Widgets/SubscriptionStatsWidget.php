<?php

namespace App\Filament\Widgets;

use App\Models\Subscription;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SubscriptionStatsWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $activeSubscriptions = Subscription::where('stripe_status', 'active')->count();
        $totalRevenue = Subscription::where('stripe_status', 'active')
            ->with('plan')
            ->get()
            ->sum(fn($sub) => $sub->plan->price ?? 0);
        $premiumUsers = User::whereHas('subscriptions', function ($query) {
            $query->where('stripe_status', 'active');
        })->count();
        $trialingSubscriptions = Subscription::where('stripe_status', 'trialing')->count();

        return [
            Stat::make('Active Subscriptions', $activeSubscriptions)
                ->description('Currently active subscriptions')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Monthly Revenue', '€' . number_format($totalRevenue, 2))
                ->description('From active subscriptions')
                ->descriptionIcon('heroicon-m-currency-euro')
                ->color('info'),

            Stat::make('Premium Users', $premiumUsers)
                ->description('Users with active subscriptions')
                ->descriptionIcon('heroicon-m-users')
                ->color('warning'),

            Stat::make('Trialing Users', $trialingSubscriptions)
                ->description('Users on trial period')
                ->descriptionIcon('heroicon-m-clock')
                ->color('primary'),
        ];
    }
}

