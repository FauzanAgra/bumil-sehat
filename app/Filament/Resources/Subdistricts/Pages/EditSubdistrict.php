<?php

namespace App\Filament\Resources\Subdistricts\Pages;

use App\Filament\Resources\Subdistricts\SubdistrictResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSubdistrict extends EditRecord
{
    protected static string $resource = SubdistrictResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
