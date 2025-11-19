<?php

namespace App\Filament\Resources\StripeConfigurationResource\Pages;

use App\Filament\Resources\StripeConfigurationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListStripeConfigurations extends ListRecords
{
    protected static string $resource = StripeConfigurationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

