<?php

namespace Database\Factories;

use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class SubscriptionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = Subscription::class;

    /**
     * Define the model's default state.
     */
    public function definition(): array
    {
        $now = now();

        return [
            'user_id' => User::factory(),
            'plan_id' => Plan::factory(),
            'stripe_id' => 'sub_' . Str::random(24),
            'stripe_subscription_id' => 'sub_' . Str::random(24),
            'stripe_status' => 'active',
            'quantity' => 1,
            'trial_ends_at' => null,
            'ends_at' => null,
            'current_period_start' => $now,
            'current_period_end' => (clone $now)->addMonth(),
            'coupon_id' => null,
            'discount_amount' => 0,
            'notes' => null,
        ];
    }

    /**
     * Indicate that the subscription is in trialing status.
     */
    public function trialing(): self
    {
        $now = now();

        return $this->state(function () use ($now) {
            return [
                'stripe_status' => 'trialing',
                'trial_ends_at' => (clone $now)->addDays(config('subscription.trial.days', 14)),
            ];
        });
    }
}
