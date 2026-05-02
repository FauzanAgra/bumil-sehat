<?php

namespace App\Filament\Resources\AncRecords;

use App\Filament\Resources\AncRecords\Pages\ListAncRecords;
use App\Filament\Resources\AncRecords\Pages\ViewAncRecord;
use App\Filament\Resources\AncRecords\Schemas\AncRecordInfolist;
use App\Filament\Resources\AncRecords\Tables\AncRecordsTable;
use App\Models\AncRecord;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AncRecordResource extends Resource
{
    protected static ?string $model = AncRecord::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Data ANC';
    protected static ?string $pluralModelLabel = 'Data ANC';
    protected static ?string $modelLabel = 'Data ANC';
    protected static ?int $navigationSort = 3;

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoring';
    }

    public static function infolist(Schema $schema): Schema
    {
        return AncRecordInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return AncRecordsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListAncRecords::route('/'),
            'view'  => ViewAncRecord::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

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
