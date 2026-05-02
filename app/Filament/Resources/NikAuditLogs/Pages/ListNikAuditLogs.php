<?php

namespace App\Filament\Resources\NikAuditLogs\Pages;

use App\Filament\Resources\NikAuditLogs\NikAuditLogResource;
use Filament\Resources\Pages\ListRecords;

class ListNikAuditLogs extends ListRecords
{
    protected static string $resource = NikAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
