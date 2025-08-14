<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class MeilisearchManagement extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-magnifying-glass';

    protected static string $view = 'filament.pages.meilisearch-management';

    protected static ?string $navigationLabel = 'Gestion Meilisearch';

    protected static ?string $title = 'Gestion de Meilisearch';

    protected static ?string $navigationGroup = 'Outils d\'administration';

    protected static ?int $navigationSort = 1;

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill([
            'meilisearch_host' => config('scout.meilisearch.host'),
            'meilisearch_key' => config('scout.meilisearch.key'),
        ]);
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Configuration Meilisearch')
                    ->description('Gérez les paramètres de connexion à Meilisearch')
                    ->schema([
                        TextInput::make('meilisearch_host')
                            ->label('Host Meilisearch')
                            ->required()
                            ->url()
                            ->placeholder('https://ms-xxxxx.meilisearch.io')
                            ->helperText('URL complète du serveur Meilisearch'),

                        TextInput::make('meilisearch_key')
                            ->label('Clé API Meilisearch')
                            ->required()
                            ->password()
                            ->placeholder('Votre clé API Meilisearch')
                            ->helperText('Clé API pour l\'authentification'),
                    ])
                    ->columns(1),
            ])
            ->statePath('data');
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save_config')
                ->label('Sauvegarder la configuration')
                ->icon('heroicon-o-check')
                ->color('success')
                ->action(function () {
                    $this->saveConfiguration();
                }),

            Action::make('test_connection')
                ->label('Tester la connexion')
                ->icon('heroicon-o-signal')
                ->color('info')
                ->action(function () {
                    $this->testConnection();
                }),

            Action::make('diagnostic')
                ->label('Diagnostic complet')
                ->icon('heroicon-o-wrench-screwdriver')
                ->color('warning')
                ->action(function () {
                    $this->runDiagnostic();
                }),

            Action::make('reindex_all')
                ->label('Réindexer tout')
                ->icon('heroicon-o-arrow-path')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Réindexer tous les modèles')
                ->modalDescription('Cette action va réindexer tous les modèles dans Meilisearch. Cela peut prendre plusieurs minutes.')
                ->action(function () {
                    try {
                        // Utiliser notre commande personnalisée
                        $exitCode = Artisan::call('meilisearch:reindex');
                        $output = Artisan::output();

                        if ($exitCode === 0) {
                            Notification::make()
                                ->title('Réindexation terminée avec succès')
                                ->body('Tous les modèles ont été réindexés. Consultez les logs pour plus de détails.')
                                ->success()
                                ->send();
                        } else {
                            Notification::make()
                                ->title('Réindexation terminée avec des erreurs')
                                ->body('Certains modèles n\'ont pas pu être réindexés. Vérifiez les logs pour plus de détails.')
                                ->warning()
                                ->send();
                        }

                    } catch (\Exception $e) {
                        $errorMessage = $e->getMessage();
                        $diagnostic = '';

                        if (strpos($errorMessage, 'Could not resolve host') !== false) {
                            $diagnostic = "\n\n🔍 Diagnostic: L'URL Meilisearch n'est pas accessible. Utilisez 'Diagnostic complet' pour plus d'informations.";
                        }

                        Notification::make()
                            ->title('Erreur lors de la réindexation')
                            ->body('Erreur: ' . $errorMessage . $diagnostic)
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('flush_indexes')
                ->label('Vider les index')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Vider tous les index')
                ->modalDescription('Cette action va supprimer tous les documents des index Meilisearch. Cette action est irréversible.')
                ->action(function () {
                    try {
                        $models = [
                            'App\\Models\\ProfessionalProfile' => 'Profils professionnels',
                            'App\\Models\\ServiceOffer' => 'Offres de service',
                            'App\\Models\\Achievement' => 'Réalisations'
                        ];

                        $results = [];
                        foreach ($models as $model => $name) {
                            try {
                                if (!class_exists($model)) {
                                    $results[] = "❌ {$name}: classe non trouvée";
                                    continue;
                                }

                                $modelInstance = new $model;
                                if (!method_exists($modelInstance, 'searchableAs')) {
                                    $results[] = "❌ {$name}: le modèle n'utilise pas le trait Searchable";
                                    continue;
                                }

                                // Utiliser la méthode unsearchable() pour vider l'index
                                $model::query()->unsearchable();
                                $results[] = "✓ {$name}: index vidé";

                            } catch (\Exception $e) {
                                $results[] = "❌ {$name}: erreur - " . $e->getMessage();
                            }
                        }

                        Notification::make()
                            ->title('Vidage des index terminé')
                            ->body(implode("\n", $results))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur lors du vidage')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }

    public function saveConfiguration(): void
    {
        $data = $this->form->getState();

        try {
            $this->updateEnvFile([
                'MEILISEARCH_HOST' => $data['meilisearch_host'],
                'MEILISEARCH_KEY' => $data['meilisearch_key'],
            ]);

            Notification::make()
                ->title('Configuration sauvegardée')
                ->body('Les paramètres Meilisearch ont été mis à jour avec succès.')
                ->success()
                ->send();

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors de la sauvegarde')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function testConnection(): void
    {
        $data = $this->form->getState();

        try {
            // Test de connexion basique avec cURL
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, rtrim($data['meilisearch_host'], '/') . '/health');
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Authorization: Bearer ' . $data['meilisearch_key'],
                'Content-Type: application/json'
            ]);

            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($httpCode === 200) {
                $responseData = json_decode($response, true);

                Notification::make()
                    ->title('Connexion réussie')
                    ->body('La connexion à Meilisearch fonctionne correctement. Status: ' . ($responseData['status'] ?? 'OK'))
                    ->success()
                    ->send();
            } else {
                Notification::make()
                    ->title('Erreur de connexion')
                    ->body("Impossible de se connecter à Meilisearch. Code HTTP: {$httpCode}")
                    ->danger()
                    ->send();
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Erreur lors du test')
                ->body('Erreur: ' . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function runDiagnostic(): void
    {
        $host = config('scout.meilisearch.host');
        $key = config('scout.meilisearch.key');

        $diagnosticResults = [];

        // 1. Configuration
        $diagnosticResults[] = "📋 Configuration:";
        $diagnosticResults[] = "   - Host: {$host}";
        $diagnosticResults[] = "   - Key: " . substr($key, 0, 10) . "..." . substr($key, -4);

        // 2. Test DNS
        $parsedUrl = parse_url($host);
        $hostname = $parsedUrl['host'] ?? 'unknown';
        $ip = gethostbyname($hostname);

        $diagnosticResults[] = "\n🌐 Test DNS:";
        if ($ip !== $hostname) {
            $diagnosticResults[] = "   ✓ {$hostname} résolu vers {$ip}";
        } else {
            $diagnosticResults[] = "   ❌ Échec de résolution DNS pour {$hostname}";
        }

        // 3. Test connexion
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

        $diagnosticResults[] = "\n🔗 Test connexion HTTP:";
        if (empty($curlError) && $httpCode === 200) {
            $healthData = json_decode($response, true);
            $diagnosticResults[] = "   ✓ Connexion réussie (HTTP {$httpCode})";
            $diagnosticResults[] = "   - Status: " . ($healthData['status'] ?? 'unknown');
        } else {
            $diagnosticResults[] = "   ❌ Connexion échouée";
            $diagnosticResults[] = "   - Code HTTP: {$httpCode}";
            if (!empty($curlError)) {
                $diagnosticResults[] = "   - Erreur cURL: {$curlError}";
            }
        }

        // 4. Modèles indexables
        $models = [
            'App\\Models\\ProfessionalProfile' => 'Profils professionnels',
            'App\\Models\\ServiceOffer' => 'Offres de service',
            'App\\Models\\Achievement' => 'Réalisations'
        ];

        $diagnosticResults[] = "\n📊 Modèles indexables:";
        foreach ($models as $model => $name) {
            if (class_exists($model)) {
                $count = $model::count();
                $traits = class_uses($model);
                $hasSearchable = in_array('Laravel\\Scout\\Searchable', $traits);
                $diagnosticResults[] = "   - {$name}: {$count} enregistrements, Searchable: " . ($hasSearchable ? '✓' : '❌');
            } else {
                $diagnosticResults[] = "   - {$name}: ❌ Classe non trouvée";
            }
        }

        // 5. Recommandations
        if (!empty($curlError) || $httpCode !== 200) {
            $diagnosticResults[] = "\n🔧 Recommandations:";
            $diagnosticResults[] = "   1. Vérifiez que votre serveur Meilisearch cloud est actif";
            $diagnosticResults[] = "   2. Vérifiez votre connexion internet";
            $diagnosticResults[] = "   3. Contactez votre fournisseur Meilisearch cloud";
            $diagnosticResults[] = "   4. Ou configurez un serveur Meilisearch local";
        } else {
            $diagnosticResults[] = "\n✅ Tout semble fonctionner correctement!";
        }

        Notification::make()
            ->title('Diagnostic Meilisearch')
            ->body(implode("\n", $diagnosticResults))
            ->info()
            ->send();
    }

    private function updateEnvFile(array $data): void
    {
        $envPath = base_path('.env');

        if (!File::exists($envPath)) {
            throw new \Exception('Fichier .env non trouvé');
        }

        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            $pattern = "/^{$key}=.*$/m";
            $replacement = "{$key}=\"{$value}\"";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);

        // Vider le cache de configuration pour que les nouveaux paramètres soient pris en compte
        Artisan::call('config:clear');
    }

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }
}
