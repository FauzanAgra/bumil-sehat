<?php

namespace App\Filament\Resources\NikAuditLogs\Tables;

use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class NikAuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('motherProfile.user.name')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->placeholder('—'),

                TextColumn::make('action')
                    ->label('Aksi')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'READ'    => 'info',
                        'DECRYPT' => 'warning',
                        'WRITE'   => 'success',
                        'UPDATE'  => 'warning',
                        'DELETE'  => 'danger',
                        'FAILED'  => 'danger',
                        default   => 'gray',
                    }),

                TextColumn::make('actor.name')
                    ->label('Dilakukan Oleh')
                    ->searchable()
                    ->placeholder('Sistem'),

                TextColumn::make('actor_role')
                    ->label('Role')
                    ->badge()
                    ->color(fn(string $state) => match ($state) {
                        'admin'   => 'danger',
                        'midwife' => 'rose',
                        'doctor'  => 'info',
                        'mother'  => 'success',
                        'system'  => 'gray',
                        default   => 'gray',
                    })
                    ->formatStateUsing(fn(string $state) => match ($state) {
                        'admin'   => 'Admin',
                        'midwife' => 'Bidan',
                        'doctor'  => 'Dokter',
                        'mother'  => 'Ibu Hamil',
                        'system'  => 'Sistem',
                        default   => $state,
                    }),

                TextColumn::make('ip_address')
                    ->label('IP Address')
                    ->searchable()
                    ->fontFamily('mono'),

                IconColumn::make('is_anomaly')
                    ->label('Anomali')
                    ->boolean()
                    ->trueColor('danger')
                    ->falseColor('success'),

                TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y, H:i:s')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('action')
                    ->label('Aksi')
                    ->options([
                        'READ'    => 'READ',
                        'DECRYPT' => 'DECRYPT',
                        'WRITE'   => 'WRITE',
                        'UPDATE'  => 'UPDATE',
                        'DELETE'  => 'DELETE',
                        'FAILED'  => 'FAILED',
                    ]),

                SelectFilter::make('actor_role')
                    ->label('Role')
                    ->options([
                        'admin'   => 'Admin',
                        'midwife' => 'Bidan',
                        'doctor'  => 'Dokter',
                        'mother'  => 'Ibu Hamil',
                        'system'  => 'Sistem',
                    ]),

                SelectFilter::make('is_anomaly')
                    ->label('Anomali')
                    ->options([
                        '1' => 'Anomali',
                        '0' => 'Normal',
                    ]),
            ]);
    }
}
