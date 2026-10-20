<?php

namespace App\Filament\Resources\AncRecords\Pages;

use App\Filament\Resources\AncRecords\AncRecordResource;
use Filament\Resources\Pages\ListRecords;

class ListAncRecords extends ListRecords
{
    protected static string $resource = AncRecordResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
