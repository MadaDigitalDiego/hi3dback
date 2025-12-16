<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\ClientProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class UserAccountTypeApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function authenticated_user_can_switch_from_client_to_professional(): void
    {
        $user = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        ClientProfile::create([
            'user_id' => $user->id,
            'first_name' => 'Test',
            'last_name' => 'Client',
            'email' => $user->email,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/account-type', [
            'is_professional' => true,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'message' => 'Type de compte mis à jour avec succès.',
            ])
            ->assertJsonPath('user.is_professional', true)
            ->assertJsonPath('profile_type', 'professional');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_professional' => true,
        ]);

        $this->assertDatabaseHas('professional_profiles', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function authenticated_user_can_switch_from_professional_to_client(): void
    {
        $user = User::factory()->create([
            'is_professional' => true,
            'email_verified_at' => now(),
        ]);

        ProfessionalProfile::create([
            'user_id' => $user->id,
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => $user->email,
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/account-type', [
            'is_professional' => false,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('user.is_professional', false)
            ->assertJsonPath('profile_type', 'client');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_professional' => false,
        ]);

        $this->assertDatabaseHas('client_profiles', [
            'user_id' => $user->id,
        ]);
    }

    /** @test */
    public function request_requires_is_professional_boolean(): void
    {
        $user = User::factory()->create([
            'is_professional' => false,
            'email_verified_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->putJson('/api/user/account-type', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['is_professional']);
    }

    /** @test */
    public function unauthenticated_users_cannot_switch_account_type(): void
    {
        $response = $this->putJson('/api/user/account-type', [
            'is_professional' => true,
        ]);

        $response->assertStatus(401);
    }
}

