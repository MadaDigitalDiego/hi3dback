<?php

namespace App\Filament\Resources\ClientProfileResource\Pages;

use App\Filament\Resources\ClientProfileResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditClientProfile extends EditRecord
{
    protected static string $resource = ClientProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
