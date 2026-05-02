<?php

namespace App\Filament\Resources\DetectedConditions\Pages;

use App\Filament\Resources\DetectedConditions\DetectedConditionResource;
use Filament\Resources\Pages\ListRecords;

class ListDetectedConditions extends ListRecords
{
    protected static string $resource = DetectedConditionResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
