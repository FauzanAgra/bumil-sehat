<?php

namespace App\Filament\Resources\AncRecords\Pages;

use App\Filament\Resources\AncRecords\AncRecordResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewAncRecord extends ViewRecord
{
    protected static string $resource = AncRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
