<?php

namespace App\Filament\Resources\OfferApplicationResource\Pages;

use App\Filament\Resources\OfferApplicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOfferApplications extends ListRecords
{
    protected static string $resource = OfferApplicationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
