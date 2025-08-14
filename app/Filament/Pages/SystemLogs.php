<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class SystemLogs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    
    protected static string $view = 'filament.pages.system-logs';
    
    protected static ?string $navigationLabel = 'Logs Système';
    
    protected static ?string $title = 'Logs du Système';
    
    protected static ?string $navigationGroup = 'Outils d\'administration';
    
    protected static ?int $navigationSort = 2;

    public $selectedLog = 'laravel.log';
    public $logContent = '';

    public function mount()
    {
        $this->loadLogContent();
    }

    public function loadLogContent()
    {
        $logPath = storage_path('logs/' . $this->selectedLog);
        
        if (File::exists($logPath)) {
            $content = File::get($logPath);
            // Prendre seulement les 50 dernières lignes pour éviter les problèmes de performance
            $lines = explode("\n", $content);
            $this->logContent = implode("\n", array_slice($lines, -50));
        } else {
            $this->logContent = 'Fichier de log non trouvé.';
        }
    }

    public function getAvailableLogs()
    {
        $logPath = storage_path('logs');
        $logs = [];
        
        if (File::exists($logPath)) {
            $files = File::files($logPath);
            foreach ($files as $file) {
                if ($file->getExtension() === 'log') {
                    $logs[] = $file->getFilename();
                }
            }
        }
        
        return $logs;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Actualiser')
                ->icon('heroicon-o-arrow-path')
                ->action(function () {
                    $this->loadLogContent();
                    Notification::make()
                        ->title('Logs actualisés')
                        ->success()
                        ->send();
                }),
                
            Action::make('clear_logs')
                ->label('Vider les logs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Vider les logs')
                ->modalDescription('Cette action va supprimer le contenu du fichier de log sélectionné.')
                ->action(function () {
                    $logPath = storage_path('logs/' . $this->selectedLog);
                    if (File::exists($logPath)) {
                        File::put($logPath, '');
                        $this->loadLogContent();
                        Notification::make()
                            ->title('Logs vidés')
                            ->success()
                            ->send();
                    }
                }),
                
            Action::make('download')
                ->label('Télécharger')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function () {
                    $logPath = storage_path('logs/' . $this->selectedLog);
                    if (File::exists($logPath)) {
                        return response()->download($logPath);
                    }
                }),
        ];
    }
}
