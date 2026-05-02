<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                TextInput::make('name')
                    ->label('Nama Lengkap')
                    ->required()
                    ->maxLength(255),

                TextInput::make('email')
                    ->label('Email')
                    ->email()
                    ->nullable()
                    ->maxLength(255),

                TextInput::make('phone')
                    ->label('Nomor HP')
                    ->tel()
                    ->required()
                    ->maxLength(20),

                Select::make('role')
                    ->label('Role')
                    ->options([
                        'mother'  => 'Ibu Hamil',
                        'midwife' => 'Bidan',
                        'doctor'  => 'Dokter',
                        'admin'   => 'Admin',
                    ])
                    ->required()
                    ->native(false),

                TextInput::make('password')
                    ->label('Password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation) => $operation === 'create')
                    ->dehydrated(fn(?string $state) => filled($state))
                    ->dehydrateStateUsing(fn(string $state) => bcrypt($state))
                    ->confirmed()
                    ->maxLength(255),

                TextInput::make('password_confirmation')
                    ->label('Konfirmasi Password')
                    ->password()
                    ->revealable()
                    ->required(fn(string $operation) => $operation === 'create')
                    ->dehydrated(false),

                Toggle::make('is_verified')
                    ->label('Sudah Diverifikasi')
                    ->default(false)
                    ->columnSpanFull(),
            ]);
    }
}
