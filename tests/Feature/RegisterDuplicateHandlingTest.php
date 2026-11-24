<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegisterDuplicateHandlingTest extends TestCase
{
    use RefreshDatabase;

    public function test_duplicate_unique_constraint_on_mother_registration_redirects_cleanly(): void
    {
        config(['app.nik_hash_salt' => str_repeat('a', 64)]);

        $nikService = app(NIKService::class);
        $subdistrict = Subdistrict::create(['name' => 'Maju']);
        $area = Area::create([
            'subdistrict_id' => $subdistrict->id,
            'rw_number' => '01',
        ]);

        // Simulasikan nomor HP sudah terdaftar tepat sebelum step akhir dieksekusi
        User::factory()->create([
            'phone' => '08220000099',
            'role' => 'mother',
            'is_verified' => true,
        ]);

        $response = $this->withSession([
            'reg_mother' => [
                'name' => 'Ibu Duplikat',
                'phone' => '08220000099',
                'password' => Hash::make('Password123'),
                'nik_encrypted' => $nikService->encrypt('3201010101010099'),
                'nik_hash' => $nikService->hashNIK('3201010101010099'),
                'birth_date' => '1998-05-10',
                'address' => 'Jl. Melati No. 1',
                'subdistrict_id' => $subdistrict->id,
                'area_id' => $area->id,
                'rt' => '01',
            ],
        ])->post(route('register.mother.hpht.store'), [
            'hpht_date' => now()->subWeeks(12)->toDateString(),
        ]);

        $response->assertRedirect(route('register.mother'))
            ->assertSessionHasErrors('nik')
            ->assertSessionMissing('reg_mother');
    }
}
