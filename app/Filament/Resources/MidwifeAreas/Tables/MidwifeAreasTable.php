<?php

namespace App\Filament\Resources\MidwifeAreas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class MidwifeAreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('midwife.name')
                    ->label('Bidan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.subdistrict.name')
                    ->label('Kelurahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('area.rw_number')
                    ->label('RW')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn(string $state) => 'RW ' . $state),

                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),

                TextColumn::make('assigned_at')
                    ->label('Ditugaskan')
                    ->dateTime('d M Y')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),

                SelectFilter::make('area.subdistrict_id')
                    ->label('Kelurahan')
                    ->relationship('area.subdistrict', 'name')
                    ->searchable()
                    ->preload(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
