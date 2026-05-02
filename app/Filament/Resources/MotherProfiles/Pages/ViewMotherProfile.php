<?php

namespace App\Filament\Resources\MotherProfiles\Pages;

use App\Filament\Resources\MotherProfiles\MotherProfileResource;
use Filament\Resources\Pages\ViewRecord;

class ViewMotherProfile extends ViewRecord
{
    protected static string $resource = MotherProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
