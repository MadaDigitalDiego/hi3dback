<?php

namespace App\Console\Commands;

use App\Models\ProfessionalProfile;
use App\Models\ServiceOffer;
use App\Models\Achievement;
use Illuminate\Console\Command;

class ReindexMeilisearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'meilisearch:reindex {--model=} {--chunk=100} {--show-progress}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Réindexer les modèles dans Meilisearch avec gestion d\'erreurs améliorée';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $models = [
            'professional_profiles' => [
                'class' => ProfessionalProfile::class,
                'name' => 'Profils professionnels'
            ],
            'service_offers' => [
                'class' => ServiceOffer::class,
                'name' => 'Offres de service'
            ],
            'achievements' => [
                'class' => Achievement::class,
                'name' => 'Réalisations'
            ]
        ];

        $modelToIndex = $this->option('model');
        $chunkSize = (int) $this->option('chunk');
        $verbose = $this->option('show-progress');

        // Si un modèle spécifique est demandé
        if ($modelToIndex) {
            if (!isset($models[$modelToIndex])) {
                $this->error("Modèle '{$modelToIndex}' non reconnu. Modèles disponibles: " . implode(', ', array_keys($models)));
                return 1;
            }
            $modelsToProcess = [$modelToIndex => $models[$modelToIndex]];
        } else {
            $modelsToProcess = $models;
        }

        $this->info('🚀 Début de la réindexation Meilisearch');
        $this->newLine();

        // Test de connexion Meilisearch
        if (!$this->testMeilisearchConnection()) {
            $this->error('❌ Impossible de se connecter à Meilisearch. Vérifiez votre configuration.');
            return 1;
        }

        $totalSuccess = 0;
        $totalErrors = 0;

        foreach ($modelsToProcess as $key => $modelInfo) {
            $modelClass = $modelInfo['class'];
            $modelName = $modelInfo['name'];

            $this->info("📊 Traitement: {$modelName}");

            try {
                if (!class_exists($modelClass)) {
                    $this->error("   ❌ Classe {$modelClass} non trouvée");
                    $totalErrors++;
                    continue;
                }

                $modelInstance = new $modelClass;
                if (!method_exists($modelInstance, 'searchableAs')) {
                    $this->error("   ❌ Le modèle n'utilise pas le trait Searchable");
                    $totalErrors++;
                    continue;
                }

                $totalRecords = $modelClass::count();
                if ($totalRecords === 0) {
                    $this->warn("   ⚠️  Aucun enregistrement à indexer");
                    continue;
                }

                $this->info("   📈 {$totalRecords} enregistrements à indexer");

                $indexed = 0;
                $errors = 0;

                $modelClass::chunk($chunkSize, function ($records) use (&$indexed, &$errors, $verbose, $modelName) {
                    try {
                        $records->searchable();
                        $indexed += $records->count();

                        if ($verbose) {
                            $this->info("      ✓ Chunk de {$records->count()} enregistrements indexé");
                        }
                    } catch (\Exception $e) {
                        $errors++;
                        if ($verbose) {
                            $this->error("      ❌ Erreur chunk: " . $e->getMessage());
                        }
                    }
                });

                if ($errors === 0) {
                    $this->info("   ✅ {$indexed}/{$totalRecords} enregistrements indexés avec succès");
                    $totalSuccess++;
                } else {
                    $this->warn("   ⚠️  {$indexed}/{$totalRecords} enregistrements indexés avec {$errors} erreurs");
                    $totalErrors++;
                }

            } catch (\Exception $e) {
                $this->error("   ❌ Erreur: " . $e->getMessage());
                $totalErrors++;
            }

            $this->newLine();
        }

        // Résumé
        $this->info('📋 Résumé de la réindexation:');
        $this->info("   ✅ Succès: {$totalSuccess}");
        if ($totalErrors > 0) {
            $this->error("   ❌ Erreurs: {$totalErrors}");
        }

        return $totalErrors > 0 ? 1 : 0;
    }

    private function testMeilisearchConnection(): bool
    {
        try {
            $host = config('scout.meilisearch.host');
            $key = config('scout.meilisearch.key');

            if (empty($host)) {
                $this->error('MEILISEARCH_HOST non configuré');
                return false;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, rtrim($host, '/') . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $key,
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if (!empty($curlError)) {
                $this->error("Erreur cURL: {$curlError}");
                return false;
            }

            if ($httpCode !== 200) {
                $this->error("Code HTTP: {$httpCode}");
                return false;
            }

            $this->info('✅ Connexion Meilisearch OK');
            return true;

        } catch (\Exception $e) {
            $this->error('Erreur de connexion: ' . $e->getMessage());
            return false;
        }
    }
}
