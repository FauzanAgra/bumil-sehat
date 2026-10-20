<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\MidwifeArea;
use App\Models\User;
use App\Services\NIKService;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MotherSeeder extends Seeder
{
    public function run(): void
    {
        $nikService = app(NIKService::class);

        // Data ibu hamil demo
        // Variasi: trimester 1, 2, dan 3 — HPHT dibuat realistis
        $mothers = [
            [
                'name' => 'Ani Rahayu',
                'phone' => '08222222201',
                'nik' => '3201010101010001',
                'birth_date' => '1997-05-10',
                'address' => 'Jl. Melati No. 12',
                'subdistrict' => 'Maju',
                'rw' => '01',
                'rt' => '02',
                'hpht_offset' => -10, // 10 minggu lalu → trimester 1 akhir
            ],
            [
                'name' => 'Budi Wati',
                'phone' => '08222222202',
                'nik' => '3201010101010002',
                'birth_date' => '1995-11-22',
                'address' => 'Jl. Mawar No. 5',
                'subdistrict' => 'Maju',
                'rw' => '02',
                'rt' => '01',
                'hpht_offset' => -20, // 20 minggu → trimester 2
            ],
            [
                'name' => 'Citra Lestari',
                'phone' => '08222222203',
                'nik' => '3201010101010003',
                'birth_date' => '1999-03-15',
                'address' => 'Jl. Kenanga No. 8',
                'subdistrict' => 'Sehat',
                'rw' => '01',
                'rt' => '03',
                'hpht_offset' => -32, // 32 minggu → trimester 3
            ],
            [
                'name' => 'Dewi Susanti',
                'phone' => '08222222204',
                'nik' => '3201010101010004',
                'birth_date' => '1998-07-30',
                'address' => 'Jl. Anggrek No. 3',
                'subdistrict' => 'Sejahtera',
                'rw' => '01',
                'rt' => '01',
                'hpht_offset' => -16, // 16 minggu → trimester 2 awal
            ],
            [
                'name' => 'Eka Putri',
                'phone' => '08222222205',
                'nik' => '3201010101010005',
                'birth_date' => '2000-01-20',
                'address' => 'Jl. Dahlia No. 7',
                'subdistrict' => 'Sejahtera',
                'rw' => '02',
                'rt' => '02',
                'hpht_offset' => -6,  // 6 minggu → trimester 1 awal
            ],
        ];

        foreach ($mothers as $data) {
            // Skip jika nomor HP sudah ada
            if (User::where('phone', $data['phone'])->exists()) {
                continue;
            }

            // Cari area
            $area = Area::whereHas(
                'subdistrict',
                fn ($q) => $q->where('name', $data['subdistrict'])
            )->where('rw_number', $data['rw'])->first();

            if (! $area) {
                $this->command->warn("Area tidak ditemukan: {$data['subdistrict']} {$data['rw']} — skip {$data['name']}");

                continue;
            }

            // Cari bidan pendamping
            $midwifeArea = MidwifeArea::where('area_id', $area->id)
                ->where('is_active', true)
                ->first();

            // Hitung HPHT & HPL
            $hpht = Carbon::today()->addWeeks($data['hpht_offset']);
            $hpl = $hpht->copy()->addDays(280);

            // Buat user
            $user = User::create([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'password' => Hash::make('password'),
                'role' => 'mother',
                'is_verified' => true,
            ]);

            // Buat mother profile
            $user->motherProfile()->create([
                'nik_encrypted' => $nikService->encrypt($data['nik']),
                'nik_hash' => $nikService->hashNIK($data['nik']),
                'birth_date' => $data['birth_date'],
                'address' => $data['address'],
                'subdistrict_id' => $area->subdistrict_id,
                'area_id' => $area->id,
                'rt' => $data['rt'],
                'assigned_midwife_id' => $midwifeArea?->midwife_id,
            ]);

            // Buat kehamilan aktif
            $user->motherProfile->pregnancies()->create([
                'hpht_date' => $hpht,
                'hpl_date' => $hpl,
                'status' => 'active',
            ]);
        }
    }
}
