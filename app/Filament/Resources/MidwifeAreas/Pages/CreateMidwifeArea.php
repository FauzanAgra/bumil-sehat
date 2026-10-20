<?php

namespace App\Filament\Resources\MidwifeAreas\Pages;

use App\Filament\Resources\MidwifeAreas\MidwifeAreaResource;
use App\Models\MidwifeArea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateMidwifeArea extends CreateRecord
{
    protected static string $resource = MidwifeAreaResource::class;

    protected function beforeCreate(): void
    {
        $midwifeId = $this->data['midwife_id'];
        $areaId = $this->data['area_id'];

        // Cek apakah kombinasi bidan + area sudah ada
        $exists = MidwifeArea::where('midwife_id', $midwifeId)
            ->where('area_id', $areaId)
            ->exists();

        if ($exists) {
            Notification::make()
                ->title('Duplikat Assignment')
                ->body('Bidan ini sudah pernah di-assign ke wilayah tersebut.')
                ->warning()
                ->persistent()
                ->send();

            $this->halt(); // Hentikan proses create
        }

        // Cek apakah area sudah punya bidan aktif lain
        $activeExists = MidwifeArea::where('area_id', $areaId)
            ->where('is_active', true)
            ->exists();

        if ($activeExists) {
            Notification::make()
                ->title('Area Sudah Memiliki Bidan Aktif')
                ->body('Wilayah ini sudah memiliki bidan aktif. Nonaktifkan bidan sebelumnya terlebih dahulu.')
                ->warning()
                ->persistent()
                ->send();

            $this->halt();
        }
    }
}
