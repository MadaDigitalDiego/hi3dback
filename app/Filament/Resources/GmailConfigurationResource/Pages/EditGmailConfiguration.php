<?php

namespace App\Filament\Resources\GmailConfigurationResource\Pages;

use App\Filament\Resources\GmailConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditGmailConfiguration extends EditRecord
{
    protected static string $resource = GmailConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
