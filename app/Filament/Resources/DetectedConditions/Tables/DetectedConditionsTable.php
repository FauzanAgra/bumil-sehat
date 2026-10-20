<?php

namespace App\Filament\Resources\DetectedConditions\Tables;

use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class DetectedConditionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('detected_at', 'desc')
            ->columns([
                TextColumn::make('ancRecord.mother.user.name')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('ancRecord.schedule.anc_number')
                    ->label('ANC ke-')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn ($state) => 'ANC '.$state),

                TextColumn::make('condition_type')
                    ->label('Kondisi')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'kek' => 'warning',
                        'preeclampsia' => 'danger',
                        'anemia' => 'rose',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'kek' => 'KEK',
                        'preeclampsia' => 'Preeklampsia',
                        'anemia' => 'Anemia',
                    }),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'detected' => 'warning',
                        'confirmed' => 'danger',
                        'rejected' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'detected' => 'Terdeteksi',
                        'confirmed' => 'Dikonfirmasi',
                        'rejected' => 'Ditolak',
                    }),

                TextColumn::make('validatedBy.name')
                    ->label('Divalidasi Oleh')
                    ->placeholder('Belum divalidasi'),

                TextColumn::make('detected_at')
                    ->label('Terdeteksi')
                    ->dateTime('d M Y, H:i')
                    ->sortable(),

                TextColumn::make('validated_at')
                    ->label('Divalidasi')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('condition_type')
                    ->label('Kondisi')
                    ->options([
                        'kek' => 'KEK',
                        'preeclampsia' => 'Preeklampsia',
                        'anemia' => 'Anemia',
                    ]),

                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'detected' => 'Terdeteksi',
                        'confirmed' => 'Dikonfirmasi',
                        'rejected' => 'Ditolak',
                    ]),
            ]);
    }
}
