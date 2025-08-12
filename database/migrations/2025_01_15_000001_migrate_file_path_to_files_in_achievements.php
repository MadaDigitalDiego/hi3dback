<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Migrer les données existantes de file_path vers files
        $achievements = DB::table('achievements')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->get();

        foreach ($achievements as $achievement) {
            // Créer la structure de fichier pour le nouveau format
            $fileInfo = [
                'path' => $achievement->file_path,
                'original_name' => basename($achievement->file_path),
                'mime_type' => $this->getMimeTypeFromPath($achievement->file_path),
                'size' => $this->getFileSizeFromPath($achievement->file_path)
            ];

            // Mettre à jour l'enregistrement avec le nouveau format
            DB::table('achievements')
                ->where('id', $achievement->id)
                ->update([
                    'files' => json_encode([$fileInfo])
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Restaurer les données de files vers file_path
        $achievements = DB::table('achievements')
            ->whereNotNull('files')
            ->get();

        foreach ($achievements as $achievement) {
            $files = json_decode($achievement->files, true);
            
            if (is_array($files) && count($files) > 0) {
                // Prendre le premier fichier pour la rétrocompatibilité
                $firstFile = $files[0];
                
                if (isset($firstFile['path'])) {
                    DB::table('achievements')
                        ->where('id', $achievement->id)
                        ->update([
                            'file_path' => $firstFile['path']
                        ]);
                }
            }
        }

        // Vider la colonne files
        DB::table('achievements')->update(['files' => null]);
    }

    /**
     * Déterminer le type MIME à partir du chemin du fichier
     */
    private function getMimeTypeFromPath(string $filePath): string
    {
        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml',
            'webp' => 'image/webp',
        ];

        return $mimeTypes[$extension] ?? 'application/octet-stream';
    }

    /**
     * Obtenir la taille du fichier à partir du chemin
     */
    private function getFileSizeFromPath(string $filePath): int
    {
        try {
            if (Storage::disk('public')->exists($filePath)) {
                return Storage::disk('public')->size($filePath);
            }
        } catch (\Exception $e) {
            // Si on ne peut pas obtenir la taille, retourner 0
        }
        
        return 0;
    }
};
