<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\File>
 */
class FileFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $extensions = ['jpg', 'png', 'pdf', 'docx', 'zip'];
        $extension = $this->faker->randomElement($extensions);
        $mimeTypes = [
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'pdf' => 'application/pdf',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'zip' => 'application/zip',
        ];

        $originalName = $this->faker->word . '.' . $extension;
        $filename = pathinfo($originalName, \PATHINFO_FILENAME) . '_' . uniqid() . '.' . $extension;

        return [
            'original_name' => $originalName,
            'filename' => $filename,
            'mime_type' => $mimeTypes[$extension],
            'size' => $this->faker->numberBetween(1024, 10 * 1024 * 1024), // 1KB to 10MB
            'extension' => $extension,
            'storage_type' => $this->faker->randomElement(['local', 'swisstransfer']),
            'local_path' => 'uploads/' . $filename,
            'swisstransfer_url' => null,
            'swisstransfer_download_url' => null,
            'swisstransfer_delete_url' => null,
            'swisstransfer_expires_at' => null,
            'status' => 'completed',
            'error_message' => null,
            'metadata' => null,
            'user_id' => \App\Models\User::factory(),
            'fileable_type' => null,
            'fileable_id' => null,
        ];
    }
}
