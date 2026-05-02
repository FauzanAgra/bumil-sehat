<?php

namespace App\Filament\Resources\MotherProfiles\Schemas;

use App\Services\NIKService;
// use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class MotherProfileInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Data Pribadi')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('user.name')
                            ->label('Nama Lengkap'),

                        TextEntry::make('nik_masked')
                            ->label('NIK')
                            ->getStateUsing(
                                fn($record) =>
                                app(NIKService::class)->mask($record->nik_encrypted, auth()->user()->role)
                            )
                            ->fontFamily('mono'),

                        TextEntry::make('birth_date')
                            ->label('Tanggal Lahir')
                            ->date('d M Y'),

                        TextEntry::make('rt')
                            ->label('RT')
                            ->placeholder('-'),

                        TextEntry::make('address')
                            ->label('Alamat')
                            ->columnSpanFull(),
                    ]),

                Section::make('Wilayah & Bidan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subdistrict.name')
                            ->label('Kelurahan'),

                        TextEntry::make('area.rw_number')
                            ->label('RW')
                            ->formatStateUsing(fn($state) => 'RW ' . $state),

                        TextEntry::make('assignedMidwife.name')
                            ->label('Bidan Pendamping')
                            ->placeholder('Belum di-assign'),
                    ]),

                Section::make('Kehamilan Aktif')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('activePregnancy.hpht_date')
                            ->label('HPHT')
                            ->date('d M Y')
                            ->placeholder('Tidak ada kehamilan aktif'),

                        TextEntry::make('activePregnancy.hpl_date')
                            ->label('HPL (Perkiraan Lahir)')
                            ->date('d M Y')
                            ->placeholder('-'),

                        TextEntry::make('activePregnancy.status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(?string $state) => match ($state) {
                                'active'    => 'success',
                                'completed' => 'gray',
                                default     => 'gray',
                            })
                            ->formatStateUsing(fn(?string $state) => match ($state) {
                                'active'    => 'Aktif',
                                'completed' => 'Selesai',
                                default     => '-',
                            }),
                    ]),
            ]);
    }
}
