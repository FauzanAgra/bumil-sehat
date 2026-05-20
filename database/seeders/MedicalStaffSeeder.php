<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\MidwifeArea;
use App\Models\Specialization;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MedicalStaffSeeder extends Seeder
{
    public function run(): void
    {
        // ── Bidan ─────────────────────────────────────────────────────
        $bidanSpec = Specialization::where('name', 'Bidan')->first();

        $bidan1 = User::firstOrCreate(
            ['phone' => '08111111101'],
            [
                'name'        => 'Sari Dewi',
                'password'    => Hash::make('password'),
                'role'        => 'midwife',
                'is_verified' => true,
            ]
        );

        $bidan1->medicalProfile()->firstOrCreate(
            ['user_id' => $bidan1->id],
            [
                'specialization_id' => $bidanSpec->id,
                'license_number'    => 'STR-BIDAN-001',
                'degree_suffix'     => 'A.Md.Keb',
                'availability'      => 'Senin – Jumat, 08:00 – 14:00',
            ]
        );

        $bidan2 = User::firstOrCreate(
            ['phone' => '08111111102'],
            [
                'name'        => 'Rina Pertiwi',
                'password'    => Hash::make('password'),
                'role'        => 'midwife',
                'is_verified' => true,
            ]
        );

        $bidan2->medicalProfile()->firstOrCreate(
            ['user_id' => $bidan2->id],
            [
                'specialization_id' => $bidanSpec->id,
                'license_number'    => 'STR-BIDAN-002',
                'degree_suffix'     => 'S.Tr.Keb',
                'availability'      => 'Senin – Sabtu, 08:00 – 12:00',
            ]
        );

        // ── Dokter ────────────────────────────────────────────────────
        $dokterSpec = Specialization::where('name', 'Dokter Umum')->first();

        $dokter = User::firstOrCreate(
            ['phone' => '08111111201'],
            [
                'name'        => 'Ahmad Fauzi',
                'password'    => Hash::make('password'),
                'role'        => 'doctor',
                'is_verified' => true,
            ]
        );

        $dokter->medicalProfile()->firstOrCreate(
            ['user_id' => $dokter->id],
            [
                'specialization_id' => $dokterSpec->id,
                'license_number'    => 'SIP-DOKTER-001',
                'degree_prefix'     => 'dr.',
                'availability'      => 'Senin – Jumat, 09:00 – 15:00',
            ]
        );

        // ── Nutrisionis ───────────────────────────────────────────────
        $nutriSpec = Specialization::where('name', 'Nutrisionis')->first();

        $nutri = User::firstOrCreate(
            ['phone' => '08111111202'],
            [
                'name'        => 'Lestari Handayani',
                'password'    => Hash::make('password'),
                'role'        => 'doctor',
                'is_verified' => true,
            ]
        );

        $nutri->medicalProfile()->firstOrCreate(
            ['user_id' => $nutri->id],
            [
                'specialization_id' => $nutriSpec->id,
                'license_number'    => 'SIP-NUTRI-001',
                'degree_suffix'     => 'S.Gz',
                'availability'      => 'Selasa & Kamis, 09:00 – 12:00',
            ]
        );

        // ── Assign wilayah ke Bidan ────────────────────────────────────
        // Bidan 1 → Kelurahan Maju (RW 01, RW 02) + Kelurahan Sehat (RW 01)
        // Bidan 2 → Kelurahan Maju (RW 03) + Kelurahan Sejahtera (semua RW)
        $assignments = [
            $bidan1->id => [
                ['Maju',  '01'],
                ['Maju',  '02'],
                ['Sehat', '01'],
            ],
            $bidan2->id => [
                ['Maju',    '03'],
                ['Sehat',   '02'],
                ['Sejahtera', '01'],
                ['Sejahtera', '02'],
                ['Sejahtera', '03'],
            ],
        ];

        foreach ($assignments as $midwifeId => $areas) {
            foreach ($areas as [$subdistrictName, $rwNumber]) {
                $area = Area::whereHas('subdistrict', fn($q) => $q->where('name', $subdistrictName))
                    ->where('rw_number', $rwNumber)
                    ->first();

                if (! $area) continue;

                MidwifeArea::firstOrCreate(
                    ['area_id' => $area->id],
                    [
                        'midwife_id'  => $midwifeId,
                        'is_active'   => true,
                        'assigned_at' => now(),
                    ]
                );
            }
        }
    }
}
