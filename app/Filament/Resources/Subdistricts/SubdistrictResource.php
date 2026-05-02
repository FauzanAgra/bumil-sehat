<?php

namespace App\Filament\Resources\Subdistricts;

use App\Filament\Resources\Subdistricts\Pages\CreateSubdistrict;
use App\Filament\Resources\Subdistricts\Pages\EditSubdistrict;
use App\Filament\Resources\Subdistricts\Pages\ListSubdistricts;
use App\Filament\Resources\Subdistricts\Schemas\SubdistrictForm;
use App\Filament\Resources\Subdistricts\Tables\SubdistrictsTable;
use App\Models\Subdistrict;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SubdistrictResource extends Resource
{
    protected static ?string $model = Subdistrict::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Kelurahan';

    protected static ?string $pluralModelLabel = 'Kelurahan';

    protected static ?string $modelLabel = 'Kelurahan';

    public static function form(Schema $schema): Schema
    {
        return SubdistrictForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SubdistrictsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSubdistricts::route('/'),
            'create' => CreateSubdistrict::route('/create'),
            'edit' => EditSubdistrict::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Master Data';
    }
}
