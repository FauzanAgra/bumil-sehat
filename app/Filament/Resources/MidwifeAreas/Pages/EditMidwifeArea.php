<?php

namespace App\Filament\Resources\MidwifeAreas\Pages;

use App\Filament\Resources\MidwifeAreas\MidwifeAreaResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMidwifeArea extends EditRecord
{
    protected static string $resource = MidwifeAreaResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
