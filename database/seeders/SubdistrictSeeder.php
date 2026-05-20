<?php

namespace Database\Seeders;

use App\Models\Area;
use App\Models\Subdistrict;
use Illuminate\Database\Seeder;

class SubdistrictSeeder extends Seeder
{
    public function run(): void
    {
        $data = [
            'Maju' => ['01', '02', '03'],
            'Sehat' => ['01', '02'],
            'Sejahtera' => ['01', '02', '03'],
        ];

        foreach ($data as $subdistrictName => $rws) {
            $subdistrict = Subdistrict::firstOrCreate(['name' => $subdistrictName]);

            foreach ($rws as $rw) {
                Area::firstOrCreate([
                    'subdistrict_id' => $subdistrict->id,
                    'rw_number'      => $rw,
                ]);
            }
        }
    }
}
