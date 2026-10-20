<?php

namespace App\Filament\Resources\DetectedConditions;

use App\Filament\Resources\DetectedConditions\Pages\ListDetectedConditions;
use App\Filament\Resources\DetectedConditions\Tables\DetectedConditionsTable;
use App\Models\DetectedCondition;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DetectedConditionResource extends Resource
{
    protected static ?string $model = DetectedCondition::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $navigationLabel = 'Kondisi Risiko';

    protected static ?string $pluralModelLabel = 'Kondisi Risiko';

    protected static ?string $modelLabel = 'Kondisi Risiko';

    protected static ?int $navigationSort = 4;

    public static function getNavigationGroup(): ?string
    {
        return 'Monitoring';
    }

    public static function table(Table $table): Table
    {
        return DetectedConditionsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDetectedConditions::route('/'),
        ];
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
