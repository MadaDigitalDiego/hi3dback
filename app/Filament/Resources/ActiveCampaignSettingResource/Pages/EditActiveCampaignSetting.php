<?php

namespace App\Filament\Resources\ActiveCampaignSettingResource\Pages;

use App\Filament\Resources\ActiveCampaignSettingResource;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditActiveCampaignSetting extends EditRecord
{
    protected static string $resource = ActiveCampaignSettingResource::class;

    protected function getActions(): array
    {
        return [
            \Filament\Pages\Actions\Action::make('testConnection')
                ->label('Test connection')
                ->action('testConnection')
                ->color('primary')
                ->icon('heroicon-o-rocket-launch'),
        ];
    }

    public function testConnection()
    {
        $record = $this->record;

        $service = app(\App\Services\ActiveCampaignService::class);
        $service->setCredentials($record->api_url, $record->api_key, (bool) $record->is_enabled);

        $ok = false;
        try {
            $ok = $service->testConnection();
        } catch (\Exception $e) {
            Notification::make()
                ->danger()
                ->title('Erreur')
                ->body($e->getMessage())
                ->send();
            return;
        }

        if ($ok) {
            Notification::make()
                ->success()
                ->title('Succès')
                ->body('Connexion ActiveCampaign OK')
                ->send();
        } else {
            Notification::make()
                ->danger()
                ->title('Échec')
                ->body('Échec de la connexion — vérifiez l’URL et la clé API')
                ->send();
        }
    }
}
