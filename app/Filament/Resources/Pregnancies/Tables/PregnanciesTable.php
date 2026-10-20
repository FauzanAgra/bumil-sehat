<?php

namespace App\Filament\Resources\Pregnancies\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PregnanciesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('mother.user.name')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('hpht_date')
                    ->label('HPHT')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('hpl_date')
                    ->label('HPL')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('gestational_week')
                    ->label('Usia Kehamilan')
                    ->getStateUsing(fn ($record) => $record->gestational_week.' minggu')
                    ->badge()
                    ->color('info'),

                TextColumn::make('trimester')
                    ->label('Trimester')
                    ->getStateUsing(fn ($record) => 'Trimester '.$record->trimester)
                    ->badge()
                    ->color(fn ($record) => match ($record->trimester) {
                        1 => 'success',
                        2 => 'warning',
                        3 => 'danger',
                    }),

                TextColumn::make('completed_anc_count')
                    ->label('ANC Selesai')
                    ->getStateUsing(fn ($record) => $record->completed_anc_count.' / 6')
                    ->badge()
                    ->color('gray'),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'active' => 'success',
                        'completed' => 'gray',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    }),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'completed' => 'Selesai',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
