<?php

namespace App\Filament\Resources\OfferApplicationResource\Pages;

use App\Filament\Resources\OfferApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOfferApplication extends EditRecord
{
    protected static string $resource = OfferApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
