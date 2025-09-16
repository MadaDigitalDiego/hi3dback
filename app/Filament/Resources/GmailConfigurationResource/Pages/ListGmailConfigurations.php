<?php

namespace App\Filament\Resources\GmailConfigurationResource\Pages;

use App\Filament\Resources\GmailConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListGmailConfigurations extends ListRecords
{
    protected static string $resource = GmailConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
