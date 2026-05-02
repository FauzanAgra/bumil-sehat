<?php

namespace App\Filament\Resources\MidwifeAreas;

use App\Filament\Resources\MidwifeAreas\Pages\CreateMidwifeArea;
use App\Filament\Resources\MidwifeAreas\Pages\EditMidwifeArea;
use App\Filament\Resources\MidwifeAreas\Pages\ListMidwifeAreas;
use App\Filament\Resources\MidwifeAreas\Schemas\MidwifeAreaForm;
use App\Filament\Resources\MidwifeAreas\Tables\MidwifeAreasTable;
use App\Models\MidwifeArea;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class MidwifeAreaResource extends Resource
{
    protected static ?string $model = MidwifeArea::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?int $navigationSort = 2;
    protected static ?string $navigationLabel = 'Area Bidan';
    protected static ?string $pluralModelLabel = 'Area Bidan';
    protected static ?string $modelLabel = 'Area Bidan';

    public static function form(Schema $schema): Schema
    {
        return MidwifeAreaForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MidwifeAreasTable::configure($table);
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
            'index' => ListMidwifeAreas::route('/'),
            'create' => CreateMidwifeArea::route('/create'),
            'edit' => EditMidwifeArea::route('/{record}/edit'),
        ];
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Manajemen User';
    }
}
