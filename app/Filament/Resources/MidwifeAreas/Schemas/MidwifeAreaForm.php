<?php

namespace App\Filament\Resources\MidwifeAreas\Schemas;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MidwifeAreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('midwife_id')
                    ->label('Bidan')
                    ->options(
                        User::where('role', 'midwife')
                            ->where('is_verified', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->searchable()
                    ->required()
                    ->native(false),

                Select::make('area_id')
                    ->label('Wilayah (RW)')
                    ->options(function () {
                        return \App\Models\Area::with('subdistrict')
                            ->get()
                            ->mapWithKeys(fn($area) => [
                                $area->id => 'RW ' . $area->rw_number . ' — ' . $area->subdistrict->name
                            ]);
                    })
                    ->searchable()
                    ->required()
                    ->native(false),

                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true)
                    ->columnSpanFull(),
            ]);
    }
}
