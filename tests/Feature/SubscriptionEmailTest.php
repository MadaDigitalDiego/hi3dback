<?php

namespace Tests\Feature;

use App\Mail\SubscriptionConfirmation;
use App\Models\Plan;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SubscriptionEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_subscription_confirmation_email_can_be_sent_without_error(): void
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
        ]);

        $plan = Plan::factory()->create([
            'is_active' => true,
        ]);

        $subscription = Subscription::factory()->create([
            'user_id' => $user->id,
            'plan_id' => $plan->id,
        ]);

        // We do not fake Mail here because we want to ensure the mailable
        // (including its Blade view) can be built and sent without throwing.
        Mail::to($user->email)->send(new SubscriptionConfirmation($user, $subscription));

        $this->assertTrue(true);
    }
}

