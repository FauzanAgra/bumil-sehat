<?php

namespace App\Filament\Resources\Pregnancies\Schemas;

use Filament\Infolists\Components\Section;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class PregnancyInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kehamilan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mother.user.name')
                            ->label('Nama Ibu'),

                        TextEntry::make('status')
                            ->label('Status')
                            ->badge()
                            ->color(fn(string $state) => match ($state) {
                                'active'    => 'success',
                                'completed' => 'gray',
                            })
                            ->formatStateUsing(fn(string $state) => match ($state) {
                                'active'    => 'Aktif',
                                'completed' => 'Selesai',
                            }),

                        TextEntry::make('hpht_date')
                            ->label('HPHT')
                            ->date('d M Y'),

                        TextEntry::make('hpl_date')
                            ->label('HPL (Perkiraan Lahir)')
                            ->date('d M Y'),

                        TextEntry::make('gestational_week')
                            ->label('Usia Kehamilan')
                            ->getStateUsing(fn($record) => $record->gestational_week . ' minggu'),

                        TextEntry::make('trimester')
                            ->label('Trimester')
                            ->getStateUsing(fn($record) => 'Trimester ' . $record->trimester),
                    ]),

                Section::make('Progress ANC')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('completed_anc_count')
                            ->label('ANC Selesai')
                            ->getStateUsing(fn($record) => $record->completed_anc_count . ' dari 6'),

                        TextEntry::make('nextSchedule.scheduled_date')
                            ->label('Jadwal ANC Berikutnya')
                            ->date('d M Y')
                            ->placeholder('Tidak ada jadwal'),
                    ]),
            ]);
    }
}
