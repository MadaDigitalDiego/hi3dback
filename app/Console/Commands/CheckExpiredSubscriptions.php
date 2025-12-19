<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpiringSoonNotification;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CheckExpiredSubscriptions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'subscriptions:check-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Vérifier les abonnements arrivant à échéance et marquer ceux qui sont expirés';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $now = Carbon::now();

        // Rappels J-7 et J-1
        $this->sendExpiringSoonNotifications($now, 7);
        $this->sendExpiringSoonNotifications($now, 1);

        // Marquer les abonnements expirés et notifier
        $this->markExpiredSubscriptions($now);

        $this->info('Vérification des abonnements terminée.');

        return Command::SUCCESS;
    }

    /**
     * Send "expiring soon" notifications N days before current_period_end.
     */
    protected function sendExpiringSoonNotifications(Carbon $now, int $days): void
    {
        $targetDate = $now->copy()->addDays($days)->toDateString();

        Subscription::query()
            ->where('stripe_status', 'active')
            ->whereNotNull('current_period_end')
            ->whereDate('current_period_end', $targetDate)
            ->with(['user', 'plan'])
            ->chunkById(100, function ($subscriptions) use ($days) {
                foreach ($subscriptions as $subscription) {
                    if (!$subscription->user) {
                        continue;
                    }

                    $subscription->user->notify(
                        new SubscriptionExpiringSoonNotification($subscription, $days)
                    );
                }
            });

        $this->info("Notifications d'échéance envoyées pour les abonnements à J-{$days}.");
    }

    /**
     * Mark subscriptions whose period has ended as expired and notify users.
     */
    protected function markExpiredSubscriptions(Carbon $now): void
    {
        Subscription::query()
            ->where('stripe_status', 'active')
            ->whereNotNull('current_period_end')
            ->where('current_period_end', '<', $now)
            ->with(['user', 'plan'])
            ->chunkById(100, function ($subscriptions) {
                foreach ($subscriptions as $subscription) {
                    $subscription->update(['stripe_status' => 'expired']);

                    if ($subscription->user) {
                        $subscription->user->notify(
                            new SubscriptionExpiredNotification($subscription)
                        );
                    }

                    $this->info("Abonnement {$subscription->id} marqué comme expiré.");
                }
            });
    }
}
