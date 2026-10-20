<?php

namespace App\Filament\Resources\MotherProfiles;

use App\Filament\Resources\MotherProfiles\Pages\ListMotherProfiles;
use App\Filament\Resources\MotherProfiles\Pages\ViewMotherProfile;
use App\Filament\Resources\MotherProfiles\Schemas\MotherProfileInfolist;
use App\Filament\Resources\MotherProfiles\Tables\MotherProfilesTable;
use App\Models\MotherProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class MotherProfileResource extends Resource
{
    protected static ?string $model = MotherProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHeart;

    protected static ?string $recordTitleAttribute = 'user.name';

    protected static ?string $navigationLabel = 'Ibu Hamil';

    protected static ?string $pluralModelLabel = 'Ibu Hamil';

    protected static ?string $modelLabel = 'Ibu Hamil';

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoring';
    }

    protected static ?int $navigationSort = 1;

    // Read-only — tidak ada form, hanya infolist dan table
    public static function infolist(Schema $schema): Schema
    {
        return MotherProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MotherProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMotherProfiles::route('/'),
            'view' => ViewMotherProfile::route('/{record}'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    // Nonaktifkan create & edit
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
