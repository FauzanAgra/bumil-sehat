<?php

namespace App\Filament\Resources\Pregnancies\Pages;

use App\Filament\Resources\Pregnancies\PregnancyResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPregnancy extends ViewRecord
{
    protected static string $resource = PregnancyResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
