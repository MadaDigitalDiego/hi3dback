<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Matching des offres - toutes les heures
        $schedule->command('offers:match')->hourly();

        // Indexation complète quotidienne à 2h du matin
        $schedule->command('forge:index --full')
                 ->dailyAt('02:00')
                 ->withoutOverlapping()
                 ->runInBackground();

        // Indexation incrémentale toutes les 6 heures
        $schedule->command('forge:index:incremental')
                 ->everySixHours()
                 ->withoutOverlapping()
                 ->runInBackground();

        // Health check uniquement (4x par jour: 6h, 12h, 18h, 00h)
        $schedule->command('meilisearch:monitor --check-only')
                 ->cron('0 6,12,18,0 * * *')
                 ->withoutOverlapping();

        // Vérification quotidienne des abonnements (rappels et expiration)
        $schedule->command('subscriptions:check-expired')->daily();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
