<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Artisan;

class ArtisanCommands extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-command-line';

    protected static string $view = 'filament.pages.artisan-commands';

    protected static ?string $navigationLabel = 'Commandes Artisan';

    protected static ?string $title = 'Commandes Artisan';

    protected static ?string $navigationGroup = 'Outils d\'administration';

    protected static ?int $navigationSort = 3;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cache_clear')
                ->label('Vider le cache')
                ->icon('heroicon-o-trash')
                ->color('warning')
                ->action(function () {
                    try {
                        Artisan::call('cache:clear');
                        Notification::make()
                            ->title('Cache vidé')
                            ->body('Le cache de l\'application a été vidé avec succès.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('config_cache')
                ->label('Cache config')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('info')
                ->action(function () {
                    try {
                        Artisan::call('config:cache');
                        Notification::make()
                            ->title('Configuration mise en cache')
                            ->body('La configuration a été mise en cache avec succès.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('route_cache')
                ->label('Cache routes')
                ->icon('heroicon-o-map')
                ->color('info')
                ->action(function () {
                    try {
                        Artisan::call('route:cache');
                        Notification::make()
                            ->title('Routes mises en cache')
                            ->body('Les routes ont été mises en cache avec succès.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('optimize')
                ->label('Optimiser')
                ->icon('heroicon-o-rocket-launch')
                ->color('success')
                ->action(function () {
                    try {
                        Artisan::call('optimize');
                        Notification::make()
                            ->title('Application optimisée')
                            ->body('L\'application a été optimisée avec succès.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Action::make('migrate')
                ->label('Migrer la DB')
                ->icon('heroicon-o-circle-stack')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Exécuter les migrations')
                ->modalDescription('Cette action va exécuter toutes les migrations en attente. Assurez-vous d\'avoir une sauvegarde de la base de données.')
                ->action(function () {
                    try {
                        Artisan::call('migrate', ['--force' => true]);
                        Notification::make()
                            ->title('Migrations exécutées')
                            ->body('Les migrations ont été exécutées avec succès.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur lors des migrations')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}
