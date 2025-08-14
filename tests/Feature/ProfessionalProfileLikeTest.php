<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ProfessionalProfile;
use App\Models\UserFavorite;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Laravel\Sanctum\Sanctum;

class ProfessionalProfileLikeTest extends TestCase
{
    use RefreshDatabase;

    protected $user;
    protected $professionalProfile;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un utilisateur de test
        $this->user = User::factory()->create([
            'is_professional' => false
        ]);

        // Créer un professionnel de test
        $professional = User::factory()->create([
            'is_professional' => true
        ]);

        $this->professionalProfile = ProfessionalProfile::factory()->create([
            'user_id' => $professional->id
        ]);
    }

    public function test_user_can_like_professional_profile(): void
    {
        Sanctum::actingAs($this->user);

        $response = $this->postJson("/api/professionals/{$this->professionalProfile->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => true,
                        'total_likes' => 1
                    ]
                ]);

        // Vérifier que le like a été enregistré
        $this->assertTrue($this->user->hasLiked($this->professionalProfile));

        // Vérifier que le profil a été ajouté aux favoris
        $this->assertTrue($this->user->hasFavorite($this->professionalProfile));
    }

    public function test_user_can_unlike_professional_profile(): void
    {
        Sanctum::actingAs($this->user);

        // D'abord liker le profil
        $this->user->like($this->professionalProfile);

        $response = $this->deleteJson("/api/professionals/{$this->professionalProfile->id}/like");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => false,
                        'total_likes' => 0
                    ]
                ]);

        // Vérifier que le like a été supprimé
        $this->assertFalse($this->user->hasLiked($this->professionalProfile));

        // Vérifier que le profil a été retiré des favoris
        $this->assertFalse($this->user->hasFavorite($this->professionalProfile));
    }

    public function test_user_can_toggle_like_professional_profile(): void
    {
        Sanctum::actingAs($this->user);

        // Premier toggle (like)
        $response = $this->postJson("/api/professionals/{$this->professionalProfile->id}/like/toggle");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => true,
                        'total_likes' => 1
                    ]
                ]);

        // Deuxième toggle (unlike)
        $response = $this->postJson("/api/professionals/{$this->professionalProfile->id}/like/toggle");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => false,
                        'total_likes' => 0
                    ]
                ]);
    }

    public function test_guest_cannot_like_professional_profile(): void
    {
        $response = $this->postJson("/api/professionals/{$this->professionalProfile->id}/like");

        $response->assertStatus(401)
                ->assertJson([
                    'message' => 'Unauthenticated.'
                ]);
    }

    public function test_like_status_endpoint(): void
    {
        Sanctum::actingAs($this->user);

        // Tester sans like
        $response = $this->getJson("/api/professionals/{$this->professionalProfile->id}/like/status");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => false,
                        'total_likes' => 0,
                        'is_favorite' => false
                    ]
                ]);

        // Liker le profil
        $this->user->like($this->professionalProfile);

        // Tester avec like
        $response = $this->getJson("/api/professionals/{$this->professionalProfile->id}/like/status");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'liked' => true,
                        'total_likes' => 1,
                        'is_favorite' => true
                    ]
                ]);
    }
}
