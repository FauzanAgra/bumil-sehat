<?php

namespace App\Filament\Resources\Areas\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class AreasTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subdistrict.name')
                    ->label('Kelurahan')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('rw_number')
                    ->label('RW')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('gray'),

                TextColumn::make('activeMidwifeArea.midwife.name')
                    ->label('Bidan Aktif')
                    ->placeholder('Belum ada bidan')
                    ->searchable(),

                TextColumn::make('motherProfiles_count')
                    ->label('Ibu Hamil')
                    ->counts('motherProfiles')
                    ->badge()
                    ->color('success'),

                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('subdistrict_id')
                    ->label('Kelurahan')
                    ->relationship('subdistrict', 'name')
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
