<?php

namespace App\Filament\Resources\OpenOfferResource\Pages;

use App\Filament\Resources\OpenOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOpenOffer extends EditRecord
{
    protected static string $resource = OpenOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
