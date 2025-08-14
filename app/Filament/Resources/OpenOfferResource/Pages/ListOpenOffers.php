<?php

namespace App\Filament\Resources\OpenOfferResource\Pages;

use App\Filament\Resources\OpenOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOpenOffers extends ListRecords
{
    protected static string $resource = OpenOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
