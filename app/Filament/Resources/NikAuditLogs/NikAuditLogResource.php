<?php

namespace App\Filament\Resources\NikAuditLogs;

use App\Filament\Resources\NikAuditLogs\Pages\ListNikAuditLogs;
use App\Filament\Resources\NikAuditLogs\Tables\NikAuditLogsTable;
use App\Models\NikAuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class NikAuditLogResource extends Resource
{
    protected static ?string $model = NikAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Audit Log NIK';
    protected static ?string $pluralModelLabel = 'Audit Log NIK';
    protected static ?string $modelLabel = 'Audit Log NIK';
    protected static ?int $navigationSort = 1;

    public static function getNavigationGroup(): ?string
    {
        return 'Keamanan';
    }

    public static function table(Table $table): Table
    {
        return NikAuditLogsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNikAuditLogs::route('/'),
        ];
    }

    // Immutable — tidak ada aksi apapun
    public static function canCreate(): bool
    {
        return false;
    }
    public static function canEdit($record): bool
    {
        return false;
    }
    public static function canDelete($record): bool
    {
        return false;
    }
}
