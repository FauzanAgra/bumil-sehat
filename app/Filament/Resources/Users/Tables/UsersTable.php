<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('email')
                    ->label('Email')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('phone')
                    ->label('Nomor HP')
                    ->searchable(),

                TextColumn::make('role')
                    ->label('Role')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'admin' => 'primary',
                        'midwife' => 'warning',
                        'doctor' => 'info',
                        'mother' => 'success',
                    })
                    ->formatStateUsing(fn (string $state) => match ($state) {
                        'admin' => 'Admin',
                        'midwife' => 'Bidan',
                        'doctor' => 'Dokter',
                        'mother' => 'Ibu Hamil',
                    }),

                IconColumn::make('is_verified')
                    ->label('Terverifikasi')
                    ->boolean(),

                TextColumn::make('created_at')
                    ->label('Didaftarkan')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->label('Role')
                    ->options([
                        'mother' => 'Ibu Hamil',
                        'midwife' => 'Bidan',
                        'doctor' => 'Dokter',
                        'admin' => 'Admin',
                    ]),

                SelectFilter::make('is_verified')
                    ->label('Status Verifikasi')
                    ->options([
                        '1' => 'Sudah Diverifikasi',
                        '0' => 'Belum Diverifikasi',
                    ]),

                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
