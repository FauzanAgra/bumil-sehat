<?php

namespace App\Filament\Resources\AncRecords\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class AncRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Informasi Kunjungan')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('mother.user.name')
                            ->label('Nama Ibu'),

                        TextEntry::make('schedule.anc_number')
                            ->label('ANC ke-')
                            ->formatStateUsing(fn($state) => 'ANC ' . $state),

                        TextEntry::make('recordedBy.name')
                            ->label('Diinput Oleh'),

                        TextEntry::make('submitted_at')
                            ->label('Waktu Submit')
                            ->dateTime('d M Y, H:i'),

                        IconEntry::make('is_locked')
                            ->label('Status Kunci')
                            ->boolean(),
                    ]),

                Section::make('Antropometri')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('weight')
                            ->label('Berat Badan')
                            ->formatStateUsing(fn($state) => $state ? $state . ' kg' : '-'),

                        TextEntry::make('height')
                            ->label('Tinggi Badan')
                            ->formatStateUsing(fn($state) => $state ? $state . ' cm' : '-'),

                        TextEntry::make('lila')
                            ->label('LILA')
                            ->formatStateUsing(fn($state) => $state ? $state . ' cm' : '-')
                            ->color(fn($state) => $state && $state < 23.5 ? 'danger' : null),
                    ]),

                Section::make('Tanda Vital & Laboratorium')
                    ->columns(3)
                    ->schema([
                        TextEntry::make('blood_pressure')
                            ->label('Tekanan Darah')
                            ->getStateUsing(
                                fn($record) =>
                                $record->systolic && $record->diastolic
                                    ? $record->systolic . '/' . $record->diastolic . ' mmHg'
                                    : '-'
                            )
                            ->color(
                                fn($record) =>
                                $record->systolic >= 140 || $record->diastolic >= 90
                                    ? 'danger' : null
                            ),

                        TextEntry::make('hb')
                            ->label('Hemoglobin (HB)')
                            ->formatStateUsing(fn($state) => $state ? $state . ' g/dL' : '-')
                            ->color(fn($state) => $state && $state < 11 ? 'danger' : null),

                        TextEntry::make('fetal_heart_rate')
                            ->label('DJJ')
                            ->formatStateUsing(fn($state) => $state ? $state . ' bpm' : '-'),

                        TextEntry::make('tt_status')
                            ->label('Status TT')
                            ->badge()
                            ->color(fn(?string $state) => match ($state) {
                                'done'     => 'success',
                                'not_done' => 'warning',
                                default    => 'gray',
                            })
                            ->formatStateUsing(fn(?string $state) => match ($state) {
                                'done'     => 'Sudah',
                                'not_done' => 'Belum',
                                default    => '-',
                            }),

                        TextEntry::make('fetal_position')
                            ->label('Letak Janin')
                            ->badge()
                            ->formatStateUsing(fn(?string $state) => match ($state) {
                                'head_down'  => 'Kepala',
                                'transverse' => 'Lintang',
                                'breech'     => 'Sungsang',
                                default      => '-',
                            }),
                    ]),

                Section::make('Kondisi Risiko Terdeteksi')
                    ->schema([
                        TextEntry::make('detectedConditions')
                            ->label('')
                            ->getStateUsing(
                                fn($record) => $record->detectedConditions->isEmpty()
                                    ? 'Tidak ada kondisi risiko'
                                    : $record->detectedConditions->map(
                                        fn($c) =>
                                        $c->label . ' — ' . $c->status_label
                                    )->join(', ')
                            ),
                    ]),
            ]);
    }
}
