<?php

namespace App\Filament\Resources\Areas\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class AreaForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('subdistrict_id')
                    ->label('Kelurahan')
                    ->relationship('subdistrict', 'name')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->native(false),

                TextInput::make('rw_number')
                    ->label('Nomor RW')
                    ->required()
                    ->maxLength(10)
                    ->placeholder('contoh: 001'),
            ]);
    }
}
