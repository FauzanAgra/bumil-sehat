<?php

namespace App\Filament\Resources\MidwifeAreas\Pages;

use App\Filament\Resources\MidwifeAreas\MidwifeAreaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMidwifeAreas extends ListRecords
{
    protected static string $resource = MidwifeAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
