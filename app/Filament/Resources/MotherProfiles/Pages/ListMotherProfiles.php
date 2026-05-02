<?php

namespace App\Filament\Resources\MotherProfiles\Pages;

use App\Filament\Resources\MotherProfiles\MotherProfileResource;
use Filament\Resources\Pages\ListRecords;

class ListMotherProfiles extends ListRecords
{
    protected static string $resource = MotherProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
