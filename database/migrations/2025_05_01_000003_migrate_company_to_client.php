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
        // Vérifier si la table company_profiles existe
        if (Schema::hasTable('company_profiles')) {
            try {
                // Récupérer tous les profils d'entreprise
                $companyProfiles = DB::table('company_profiles')->get();

                foreach ($companyProfiles as $companyProfile) {
                    // Vérifier si un profil client existe déjà pour cet utilisateur
                    $existingProfile = DB::table('client_profiles')
                        ->where('user_id', $companyProfile->user_id)
                        ->first();

                    if ($existingProfile) {
                        // Mettre à jour le profil client existant
                        DB::table('client_profiles')
                            ->where('user_id', $companyProfile->user_id)
                            ->update([
                                'type' => 'entreprise',
                                'company_name' => $companyProfile->company_name ?? $existingProfile->company_name,
                                'company_size' => $companyProfile->company_size ?? $existingProfile->company_size,
                                'industry' => $companyProfile->industry ?? $existingProfile->industry,
                                'first_name' => $companyProfile->first_name ?? $existingProfile->first_name,
                                'last_name' => $companyProfile->last_name ?? $existingProfile->last_name,
                                'phone' => $companyProfile->phone ?? $existingProfile->phone,
                                'address' => $companyProfile->address ?? $existingProfile->address,
                                'city' => $companyProfile->city ?? $existingProfile->city,
                                'country' => $companyProfile->country ?? $existingProfile->country,
                                'bio' => $companyProfile->bio ?? $existingProfile->bio,
                                'avatar' => $companyProfile->avatar ?? $existingProfile->avatar,
                                'description' => $companyProfile->description ?? $existingProfile->description,
                                'completion_percentage' => $companyProfile->completion_percentage ?? $existingProfile->completion_percentage,
                            ]);
                    } else {
                        // Créer un nouveau profil client
                        DB::table('client_profiles')->insert([
                            'user_id' => $companyProfile->user_id,
                            'type' => 'entreprise',
                            'company_name' => $companyProfile->company_name ?? null,
                            'company_size' => $companyProfile->company_size ?? null,
                            'industry' => $companyProfile->industry ?? null,
                            'first_name' => $companyProfile->first_name ?? null,
                            'last_name' => $companyProfile->last_name ?? null,
                            'phone' => $companyProfile->phone ?? null,
                            'address' => $companyProfile->address ?? null,
                            'city' => $companyProfile->city ?? null,
                            'country' => $companyProfile->country ?? null,
                            'bio' => $companyProfile->bio ?? null,
                            'avatar' => $companyProfile->avatar ?? null,
                            'description' => $companyProfile->description ?? null,
                            'completion_percentage' => $companyProfile->completion_percentage ?? 0,
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]);
                    }
                }

                Log::info('Migration des profils d\'entreprise vers les profils clients terminée avec succès.');
            } catch (\Exception $e) {
                Log::error('Erreur lors de la migration des profils d\'entreprise: ' . $e->getMessage());
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
