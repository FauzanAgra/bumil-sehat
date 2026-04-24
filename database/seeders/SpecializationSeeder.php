<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SpecializationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('specializations')->insert([
            [
                'name' => 'Bidan',
                'role_group' => 'midwife',
                'icon' => 'fa-user-nurse',
                'wa_greeting' => 'Halo Bu Bidan, saya ingin konsultasi.',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Dokter Umum',
                'role_group' => 'doctor',
                'icon' => 'fa-user-doctor',
                'wa_greeting' => 'Halo Dokter, saya ingin bertanya.',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Nutrisionis',
                'role_group' => 'doctor',
                'icon' => 'fa-apple-whole',
                'wa_greeting' => 'Halo, saya ingin bertanya terkait nutrisi.',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Psikolog',
                'role_group' => 'doctor',
                'icon' => 'fa-brain',
                'wa_greeting' => 'Halo, saya ingin konsultasi kesehatan mental.',
                'is_active' => true,
                'sort_order' => 4,
            ]
        ]);
    }
}
