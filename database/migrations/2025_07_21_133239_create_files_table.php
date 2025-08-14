<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('files', function (Blueprint $table) {
            $table->id();

            // Informations de base du fichier
            $table->string('original_name');
            $table->string('filename'); // Nom du fichier stocké
            $table->string('mime_type');
            $table->bigInteger('size'); // Taille en bytes
            $table->string('extension', 10);

            // Stockage
            $table->enum('storage_type', ['local', 'swisstransfer'])->default('local');
            $table->string('local_path')->nullable(); // Chemin local si stockage local
            $table->string('swisstransfer_url')->nullable(); // URL SwissTransfer
            $table->string('swisstransfer_download_url')->nullable(); // URL de téléchargement
            $table->string('swisstransfer_delete_url')->nullable(); // URL de suppression
            $table->timestamp('swisstransfer_expires_at')->nullable(); // Date d'expiration SwissTransfer

            // Métadonnées
            $table->enum('status', ['uploading', 'completed', 'failed', 'expired'])->default('uploading');
            $table->text('error_message')->nullable();
            $table->json('metadata')->nullable(); // Métadonnées supplémentaires

            // Relations
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
           
            $table->nullableMorphs('fileable'); // Relation polymorphique (crée automatiquement l'index)

            // Index
            $table->index(['user_id', 'status']);
            $table->index(['storage_type', 'status']);

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('files');
    }
};
