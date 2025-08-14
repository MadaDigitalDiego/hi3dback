<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Artisan;

class EmailManagement extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-envelope';
    
    protected static string $view = 'filament.pages.email-management';
    
    protected static ?string $navigationLabel = 'Gestion des Emails';
    
    protected static ?string $title = 'Gestion des Emails et Notifications';
    
    protected static ?string $navigationGroup = 'Communications';
    
    protected static ?int $navigationSort = 1;

    public $testEmail = '';

    protected function getHeaderActions(): array
    {
        return [
            Action::make('test_email')
                ->label('Test d\'envoi')
                ->icon('heroicon-o-paper-airplane')
                ->color('primary')
                ->form([
                    \Filament\Forms\Components\TextInput::make('email')
                        ->label('Email de test')
                        ->email()
                        ->required()
                        ->default('admin@hi3d.com'),
                ])
                ->action(function (array $data) {
                    try {
                        Mail::raw('Ceci est un email de test depuis le back-office Hi3D.', function ($message) use ($data) {
                            $message->to($data['email'])
                                    ->subject('Test Email - Hi3D Back-Office');
                        });
                        
                        Notification::make()
                            ->title('Email de test envoyé')
                            ->body("Email envoyé avec succès à {$data['email']}")
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur d\'envoi')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('queue_work')
                ->label('Traiter la queue')
                ->icon('heroicon-o-queue-list')
                ->color('info')
                ->action(function () {
                    try {
                        Artisan::call('queue:work', ['--once' => true]);
                        Notification::make()
                            ->title('Queue traitée')
                            ->body('Les tâches en queue ont été traitées.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        Notification::make()
                            ->title('Erreur de queue')
                            ->body('Erreur: ' . $e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
                
            Action::make('clear_failed')
                ->label('Vider les échecs')
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function () {
                    try {
                        Artisan::call('queue:flush');
                        Notification::make()
                            ->title('Échecs vidés')
                            ->body('Les tâches échouées ont été supprimées.')
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
        ];
    }
}
