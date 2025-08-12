<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Vérifier si la table freelance_profiles existe
        if (Schema::hasTable('freelance_profiles')) {
            try {
                // Récupérer tous les profils freelance
                $freelanceProfiles = DB::table('freelance_profiles')->get();

                foreach ($freelanceProfiles as $freelanceProfile) {
                    // Vérifier si un profil professionnel existe déjà pour cet utilisateur
                    $existingProfile = DB::table('professional_profiles')
                        ->where('user_id', $freelanceProfile->user_id)
                        ->first();

                    if ($existingProfile) {
                        // Mettre à jour le profil professionnel existant
                        DB::table('professional_profiles')
                            ->where('user_id', $freelanceProfile->user_id)
                            ->update([
                                'first_name' => $freelanceProfile->first_name ?? $existingProfile->first_name,
                                'last_name' => $freelanceProfile->last_name ?? $existingProfile->last_name,
                                'phone' => $freelanceProfile->phone ?? $existingProfile->phone,
                                'address' => $freelanceProfile->address ?? $existingProfile->address,
                                'city' => $freelanceProfile->city ?? $existingProfile->city,
                                'country' => $freelanceProfile->country ?? $existingProfile->country,
                                'bio' => $freelanceProfile->bio ?? $existingProfile->bio,
                                'avatar' => $freelanceProfile->avatar ?? $existingProfile->avatar,
                                'title' => $freelanceProfile->title ?? $existingProfile->title,
                                'skills' => $freelanceProfile->skills ?? $existingProfile->skills,
                                'portfolio' => $freelanceProfile->portfolio ?? $existingProfile->portfolio,
                                'availability_status' => $freelanceProfile->availability_status ?? $existingProfile->availability_status,
                                'languages' => $freelanceProfile->languages ?? $existingProfile->languages,
                                'services_offered' => $freelanceProfile->services_offered ?? $existingProfile->services_offered,
                                'rating' => $freelanceProfile->rating ?? $existingProfile->rating,
                                'completion_percentage' => $freelanceProfile->completion_percentage ?? $existingProfile->completion_percentage,
                            ]);
                    } else {
                        // Créer un nouveau profil professionnel
                        DB::table('professional_profiles')->insert([
                            'user_id' => $freelanceProfile->user_id,
                            'first_name' => $freelanceProfile->first_name ?? null,
                            'last_name' => $freelanceProfile->last_name ?? null,
                            'phone' => $freelanceProfile->phone ?? null,
                            'address' => $freelanceProfile->address ?? null,
                            'city' => $freelanceProfile->city ?? null,
                            'country' => $freelanceProfile->country ?? null,
                            'bio' => $freelanceProfile->bio ?? null,
                            'avatar' => $freelanceProfile->avatar ?? null,
                            'title' => $freelanceProfile->title ?? null,
                            'skills' => $freelanceProfile->skills ?? null,
                            'portfolio' => $freelanceProfile->portfolio ?? null,
                            'availability_status' => $freelanceProfile->availability_status ?? 'available',
                            'languages' => $freelanceProfile->languages ?? null,
                            'services_offered' => $freelanceProfile->services_offered ?? null,
                            'rating' => $freelanceProfile->rating ?? 0,
                            'completion_percentage' => $freelanceProfile->completion_percentage ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                Log::info('Migration des profils freelance vers les profils professionnels terminée avec succès.');
            } catch (\Exception $e) {
                Log::error('Erreur lors de la migration des profils freelance: ' . $e->getMessage());
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Cette migration ne peut pas être annulée
    }
};
