<?php

namespace App\Filament\Resources\Specializations\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class SpecializationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('Nama Spesialisi')
                    ->required()
                    ->maxLength(100)
                    ->placeholder('contoh: Bidan, Dokter Umum'),
                Select::make('role_group')
                    ->label('Grup Role')
                    ->options(['midwife' => 'Bidan', 'doctor' => 'Dokter'])
                    ->required()
                    ->native(false),
                TextInput::make('icon')
                    ->label('Icon FontAwesome')
                    ->placeholder('contoh: fa-user-nurse')
                    ->default(null),
                TextInput::make('color')
                    ->label('Warna Tema')
                    ->placeholder('contoh: rose, blue, green')
                    ->default(null),
                TextInput::make('sort_order')
                    ->label('Urutan Tampil')
                    ->required()
                    ->numeric()
                    ->default(0),
                Textarea::make('wa_greeting')
                    ->label('Template Pesan WhatsApp')
                    ->placeholder('contoh: Halo Bu Bidan, saya ingin konsultasi.')
                    ->default(null)
                    ->columnSpanFull(),
                Toggle::make('is_active')
                    ->label('Aktif')
                    ->default(true),

            ]);
    }
}
