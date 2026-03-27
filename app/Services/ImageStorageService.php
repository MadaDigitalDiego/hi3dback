<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;
use Intervention\Image\Drivers\Imagick\Driver as ImagickDriver;
use Intervention\Image\ImageManager;

class ImageStorageService
{
    public function storeAsWebp(UploadedFile $file, string $directory, string $disk = 'public', int $quality = 80): string
    {
        $mimeType = $file->getMimeType() ?? '';

        if (!str_starts_with($mimeType, 'image/')) {
            return $file->store($directory, $disk);
        }

        if (in_array($mimeType, ['image/svg+xml', 'image/gif'], true)) {
            return $file->store($directory, $disk);
        }

        $raw = file_get_contents($file->getRealPath());
        if ($raw === false) {
            return $file->store($directory, $disk);
        }

        if (class_exists(ImageManager::class)) {
            try {
                $manager = null;
                if (class_exists(ImagickDriver::class)) {
                    try {
                        $manager = new ImageManager(new ImagickDriver());
                    } catch (\Throwable $e) {
                        $manager = null;
                    }
                }

                if ($manager === null && class_exists(GdDriver::class)) {
                    $manager = new ImageManager(new GdDriver());
                }

                if ($manager !== null) {
                    $webpData = $manager->read($raw)->toWebp($quality)->toString();
                    if (is_string($webpData) && $webpData !== '') {
                        $filename = (string) str()->uuid() . '.webp';
                        $path = trim($directory, '/') . '/' . $filename;
                        Storage::disk($disk)->put($path, $webpData);
                        return $path;
                    }
                }
            } catch (\Throwable $e) {
                // Fall through to GD/original storage
            }
        }

        if (!function_exists('imagecreatefromstring') || !function_exists('imagewebp')) {
            return $file->store($directory, $disk);
        }

        $image = @\imagecreatefromstring($raw);
        if ($image === false) {
            return $file->store($directory, $disk);
        }

        if (function_exists('imagepalettetotruecolor')) {
            @imagepalettetotruecolor($image);
        }

        if (function_exists('imagealphablending')) {
            @imagealphablending($image, true);
        }

        if (function_exists('imagesavealpha')) {
            @imagesavealpha($image, true);
        }

        ob_start();
        $encoded = @\imagewebp($image, null, $quality);
        $webpData = ob_get_clean();

        if (function_exists('imagedestroy')) {
            \imagedestroy($image);
        }

        if ($encoded !== true || $webpData === false || $webpData === '') {
            return $file->store($directory, $disk);
        }

        $filename = (string) str()->uuid() . '.webp';
        $path = trim($directory, '/') . '/' . $filename;

        Storage::disk($disk)->put($path, $webpData);

        return $path;
    }
}
