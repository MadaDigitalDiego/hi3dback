<?php

namespace App\Filament\Resources\ServiceOfferResource\Pages;

use App\Filament\Resources\ServiceOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditServiceOffer extends EditRecord
{
    protected static string $resource = ServiceOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
