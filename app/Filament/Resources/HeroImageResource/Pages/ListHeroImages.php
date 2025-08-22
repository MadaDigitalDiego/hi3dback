<?php

namespace App\Filament\Resources\HeroImageResource\Pages;

use App\Filament\Resources\HeroImageResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListHeroImages extends ListRecords
{
    protected static string $resource = HeroImageResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
