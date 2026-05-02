<?php

namespace App\Filament\Resources\MotherProfiles\Tables;

use App\Services\NIKService;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class MotherProfilesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Nama Ibu')
                    ->searchable()
                    ->sortable(),

                TextColumn::make('nik_masked')
                    ->label('NIK')
                    ->getStateUsing(
                        fn($record) =>
                        app(NIKService::class)->mask($record->nik_encrypted, auth()->user()->role)
                    )
                    ->fontFamily('mono'),

                TextColumn::make('birth_date')
                    ->label('Tanggal Lahir')
                    ->date('d M Y')
                    ->sortable(),

                TextColumn::make('subdistrict.name')
                    ->label('Kelurahan')
                    ->searchable(),

                TextColumn::make('area.rw_number')
                    ->label('RW')
                    ->badge()
                    ->color('gray')
                    ->formatStateUsing(fn($state) => 'RW ' . $state),

                TextColumn::make('assignedMidwife.name')
                    ->label('Bidan Pendamping')
                    ->placeholder('Belum di-assign')
                    ->searchable(),

                TextColumn::make('activePregnancy.status')
                    ->label('Status Kehamilan')
                    ->badge()
                    ->color(fn(?string $state) => match ($state) {
                        'active'    => 'success',
                        'completed' => 'gray',
                        default     => 'gray',
                    })
                    ->formatStateUsing(fn(?string $state) => match ($state) {
                        'active'    => 'Aktif',
                        'completed' => 'Selesai',
                        default     => 'Tidak Ada',
                    }),

                TextColumn::make('created_at')
                    ->label('Terdaftar')
                    ->date('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('assigned_midwife_id')
                    ->label('Bidan Pendamping')
                    ->relationship('assignedMidwife', 'name')
                    ->searchable()
                    ->preload(),

                SelectFilter::make('subdistrict_id')
                    ->label('Kelurahan')
                    ->relationship('subdistrict', 'name')
                    ->searchable()
                    ->preload(),

                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}
