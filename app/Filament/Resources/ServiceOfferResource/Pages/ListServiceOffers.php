<?php

namespace App\Filament\Resources\ServiceOfferResource\Pages;

use App\Filament\Resources\ServiceOfferResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListServiceOffers extends ListRecords
{
    protected static string $resource = ServiceOfferResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
