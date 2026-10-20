<?php

namespace App\Filament\Resources\Specializations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SpecializationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('sort_order')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Nama Spesialisasi')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('role_group')
                    ->label('Grup')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'midwife' => 'rose',
                        'doctor' => 'blue',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'midwife' => 'Bidan',
                        'doctor' => 'Dokter',
                    }),
                TextColumn::make('wa_greeting')
                    ->label('Pesan WA')
                    ->limit(40)
                    ->tooltip(fn (string $state) => $state),
                IconColumn::make('is_active')
                    ->label('Aktif')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role_group')
                    ->label('Group Role')
                    ->options([
                        'midwife' => 'Bidan',
                        'doctor' => 'Dokter',
                    ]),

                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        '1' => 'Aktif',
                        '0' => 'Tidak Aktif',
                    ]),
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
