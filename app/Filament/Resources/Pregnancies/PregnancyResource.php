<?php

namespace App\Filament\Resources\Pregnancies;

use App\Filament\Resources\Pregnancies\Pages\ListPregnancies;
use App\Filament\Resources\Pregnancies\Pages\ViewPregnancy;
use App\Filament\Resources\Pregnancies\Schemas\PregnancyInfolist;
use App\Filament\Resources\Pregnancies\Tables\PregnanciesTable;
use App\Models\Pregnancy;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PregnancyResource extends Resource
{
    protected static ?string $model = Pregnancy::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendarDays;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Kehamilan';
    protected static ?string $pluralModelLabel = 'Kehamilan';
    protected static ?string $modelLabel = 'Kehamilan';
    protected static ?int $navigationSort = 2;

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoring';
    }

    public static function infolist(Schema $schema): Schema
    {
        return PregnancyInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PregnanciesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPregnancies::route('/'),
            'view'  => ViewPregnancy::route('/{record}'),
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
