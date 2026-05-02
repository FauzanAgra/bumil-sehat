<?php

namespace App\Filament\Resources\Subdistricts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class SubdistrictForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Kelurahan')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('contoh: Pamekaran'),
            ]);
    }
}
