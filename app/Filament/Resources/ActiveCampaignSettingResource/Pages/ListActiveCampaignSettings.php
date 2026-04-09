<?php

namespace App\Filament\Resources\ActiveCampaignSettingResource\Pages;

use App\Filament\Resources\ActiveCampaignSettingResource;
use Filament\Resources\Pages\ListRecords;
use Filament\Pages\Actions\CreateAction;


class ListActiveCampaignSettings extends ListRecords
{
    protected static string $resource = ActiveCampaignSettingResource::class;

    protected function getActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
