<?php

namespace Tests\Feature;

use App\Models\File;
use App\Models\User;
use App\Services\FileManagerService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class FileManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // Fake the storage
        Storage::fake('public');
    }

    /** @test */
    public function user_can_upload_small_file_to_local_storage()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create a small test file (< 10MB)
        $file = UploadedFile::fake()->image('test.jpg', 800, 600)->size(5000); // 5MB

        $response = $this->postJson('/api/files/upload', [
            'files' => [$file],
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'storage_type' => 'local',
                        'status' => 'completed',
                    ]
                ]);

        // Check file was created in database
        $this->assertDatabaseHas('files', [
            'user_id' => $this->user->id,
            'original_name' => 'test.jpg',
            'storage_type' => 'local',
            'status' => 'completed',
        ]);

        // Check file exists in storage
        $fileRecord = File::where('user_id', $this->user->id)->first();
        $this->assertTrue(Storage::disk('public')->exists($fileRecord->local_path));
    }

    /** @test */
    public function user_can_upload_multiple_files()
    {
        $this->actingAs($this->user, 'sanctum');

        $files = [
            UploadedFile::fake()->image('test1.jpg')->size(1000),
            UploadedFile::fake()->create('document.pdf', 2000, 'application/pdf'),
            UploadedFile::fake()->image('test2.png')->size(1500),
        ];

        $response = $this->postJson('/api/files/upload', [
            'files' => $files,
        ]);

        $response->assertStatus(201)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'statistics' => [
                            'total' => 3,
                            'successful' => 3,
                            'failed' => 0,
                        ]
                    ]
                ]);

        // Check all files were created
        $this->assertEquals(3, File::where('user_id', $this->user->id)->count());
    }

    /** @test */
    public function user_can_get_list_of_their_files()
    {
        $this->actingAs($this->user, 'sanctum');

        // Create some test files
        File::factory()->count(5)->create(['user_id' => $this->user->id]);

        $response = $this->getJson('/api/files');

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ])
                ->assertJsonStructure([
                    'data' => [
                        'files' => [
                            '*' => [
                                'id',
                                'original_name',
                                'mime_type',
                                'size',
                                'storage_type',
                                'status',
                                'created_at',
                            ]
                        ],
                        'pagination'
                    ]
                ]);
    }

    /** @test */
    public function user_can_get_file_details()
    {
        $this->actingAs($this->user, 'sanctum');

        $file = File::factory()->create([
            'user_id' => $this->user->id,
            'status' => 'completed',
        ]);

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $file->id,
                        'original_name' => $file->original_name,
                    ]
                ]);
    }

    /** @test */
    public function user_can_delete_their_file()
    {
        $this->actingAs($this->user, 'sanctum');

        $file = File::factory()->create([
            'user_id' => $this->user->id,
            'storage_type' => 'local',
            'local_path' => 'uploads/test.jpg',
        ]);

        // Create the file in storage
        Storage::disk('public')->put($file->local_path, 'test content');

        $response = $this->deleteJson("/api/files/{$file->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                ]);

        // Check file was deleted from database
        $this->assertDatabaseMissing('files', ['id' => $file->id]);

        // Check file was deleted from storage
        $this->assertFalse(Storage::disk('public')->exists($file->local_path));
    }

    /** @test */
    public function user_cannot_access_other_users_files()
    {
        $this->actingAs($this->user, 'sanctum');

        $otherUser = User::factory()->create();
        $file = File::factory()->create(['user_id' => $otherUser->id]);

        $response = $this->getJson("/api/files/{$file->id}");

        $response->assertStatus(403)
                ->assertJson([
                    'success' => false,
                    'message' => 'Access denied',
                ]);
    }

    /** @test */
    public function unauthenticated_user_cannot_upload_files()
    {
        $file = UploadedFile::fake()->image('test.jpg');

        $response = $this->postJson('/api/files/upload', [
            'files' => [$file],
        ]);

        $response->assertStatus(401);
    }
}
