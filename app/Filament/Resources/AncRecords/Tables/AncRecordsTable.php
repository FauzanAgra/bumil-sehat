<?php

namespace App\Filament\Resources\AncRecords\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class AncRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('submitted_at', 'desc')
            ->columns([
                TextColumn::make('mother.user.name')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('schedule.anc_number')
                    ->label('ANC ke-')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => 'ANC '.$state),

                TextColumn::make('weight')
                    ->label('BB (kg)')
                    ->numeric(1)
                    ->placeholder('-'),

                TextColumn::make('lila')
                    ->label('LILA (cm)')
                    ->numeric(1)
                    ->placeholder('-')
                    ->color(fn ($state) => $state && $state < 23.5 ? 'danger' : null),

                TextColumn::make('systolic')
                    ->label('TD')
                    ->getStateUsing(
                        fn ($record) => $record->systolic && $record->diastolic
                            ? $record->systolic.'/'.$record->diastolic
                            : '-'
                    ),

                TextColumn::make('hb')
                    ->label('HB (g/dL)')
                    ->numeric(1)
                    ->placeholder('-')
                    ->color(fn ($state) => $state && $state < 11 ? 'danger' : null),

                TextColumn::make('detectedConditions_count')
                    ->label('Kondisi Risiko')
                    ->counts('detectedConditions')
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'danger' : 'success')
                    ->formatStateUsing(fn ($state) => $state > 0 ? $state.' kondisi' : 'Normal'),

                IconColumn::make('is_locked')
                    ->label('Dikunci')
                    ->boolean(),

                TextColumn::make('submitted_at')
                    ->label('Disubmit')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_locked')
                    ->label('Status Kunci')
                    ->options([
                        '1' => 'Sudah Dikunci',
                        '0' => 'Belum Dikunci',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
