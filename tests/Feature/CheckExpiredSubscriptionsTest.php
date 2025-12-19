<?php

namespace Tests\Feature;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use App\Notifications\SubscriptionExpiredNotification;
use App\Notifications\SubscriptionExpiringSoonNotification;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class CheckExpiredSubscriptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_command_sends_reminders_and_marks_expired_subscriptions(): void
    {
        Notification::fake();

        $now = Carbon::create(2025, 1, 1, 12, 0, 0);
        Carbon::setTestNow($now);

        $user = User::factory()->create();
        $plan = Plan::factory()->create(['is_active' => true]);

        // Abonnement qui expire dans 7 jours
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
            'current_period_end' => $now->copy()->addDays(7),
        ]);

        // Abonnement qui expire dans 1 jour
        Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
            'current_period_end' => $now->copy()->addDay(),
        ]);

        // Abonnement déjà expiré
        $expired = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
            'stripe_status' => 'active',
            'current_period_end' => $now->copy()->subDay(),
        ]);

        $this->artisan('subscriptions:check-expired')
            ->assertExitCode(0);

        // 2 rappels (J-7 et J-1)
        Notification::assertSentToTimes($user, SubscriptionExpiringSoonNotification::class, 2);

        // 1 notification d'expiration
        Notification::assertSentTo($user, SubscriptionExpiredNotification::class);

        // L'abonnement expiré est bien marqué comme tel
        $this->assertDatabaseHas('subscriptions', [
            'id' => $expired->id,
            'stripe_status' => 'expired',
        ]);
    }
}
