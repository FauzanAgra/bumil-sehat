<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\MidwifeArea;
use App\Models\MotherProfile;
use App\Models\Specialization;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RegistrationSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_mother_registration_encrypts_nik_in_session_rotates_session_id_and_creates_records_atomically(): void
    {
        $subdistrict = Subdistrict::create(['name' => 'Maju']);
        $area = Area::create(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);
        $midwife = User::factory()->create(['phone' => '08340000001', 'role' => 'midwife', 'is_verified' => true]);
        MidwifeArea::create(['midwife_id' => $midwife->id, 'area_id' => $area->id, 'is_active' => true]);

        // Step 1: password < 8 karakter ditolak (SEC-014)
        $this->post(route('register.mother.store'), [
            'name' => 'Ani Test',
            'phone' => '08340000010',
            'password' => '123456',
            'password_confirmation' => '123456',
        ])->assertSessionHasErrors('password');

        // Step 1 valid
        $this->post(route('register.mother.store'), [
            'name' => 'Ani Test',
            'phone' => '08340000010',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
        ])->assertRedirect(route('register.mother.identity'));

        // Step 2: NIK plaintext tidak boleh masuk session maupun _old_input (SEC-002)
        $rawNik = '3204015001990020';
        $this->post(route('register.mother.identity.store'), [
            'nik' => $rawNik,
            'birth_date' => '1999-01-10',
            'address' => 'Jl. Melati No. 1',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'rt' => '02',
        ])->assertRedirect(route('register.mother.hpht'));

        $this->assertNull(session('reg_mother.nik'));
        $this->assertNotEmpty(session('reg_mother.nik_encrypted'));
        $this->assertNotSame($rawNik, session('reg_mother.nik_encrypted'));
        $this->assertSame(app(NIKService::class)->hashNIK($rawNik), session('reg_mother.nik_hash'));

        $sessionIdBeforeLogin = session()->getId();

        // Step 3: HPHT -> atomic create + session ID regeneration (SEC-003, SEC-009)
        $hpht = now()->subWeeks(14)->toDateString();
        $response = $this->post(route('register.mother.hpht.store'), [
            'hpht_date' => $hpht,
        ]);

        $response->assertRedirect(route('mother.dashboard'))
            ->assertSessionMissing('reg_mother');

        $this->assertNotSame($sessionIdBeforeLogin, session()->getId());

        $user = User::where('phone', '08340000010')->firstOrFail();
        $this->assertAuthenticatedAs($user);
        $this->assertSame($midwife->id, $user->motherProfile->assigned_midwife_id);
        $this->assertSame(1, $user->motherProfile->pregnancies()->count());
    }

    public function test_failed_identity_step_never_flashes_raw_nik_to_old_input(): void
    {
        $subdistrict = Subdistrict::create(['name' => 'Maju']);
        $otherSubdistrict = Subdistrict::create(['name' => 'Sehat']);
        $otherArea = Area::create(['subdistrict_id' => $otherSubdistrict->id, 'rw_number' => '02']);

        $rawNik = '3204015001990021';

        // Mismatched area_id vs subdistrict_id ditolak (SEC-008) & NIK tidak di-flash (SEC-002)
        $response = $this->withSession(['reg_mother' => ['name' => 'Test', 'phone' => '08340000011', 'password' => Hash::make('Password123')]])
            ->from(route('register.mother.identity'))
            ->post(route('register.mother.identity.store'), [
                'nik' => $rawNik,
                'birth_date' => '1999-01-10',
                'address' => 'Jl. Mawar',
                'subdistrict_id' => $subdistrict->id,
                'area_id' => $otherArea->id,
            ]);

        $response->assertRedirect(route('register.mother.identity'))
            ->assertSessionHasErrors('area_id');

        $this->assertArrayNotHasKey('nik', session('_old_input', []));
    }

    public function test_duplicate_nik_on_final_step_rolls_back_user_atomically(): void
    {
        $subdistrict = Subdistrict::create(['name' => 'Maju']);
        $area = Area::create(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);
        $nikService = app(NIKService::class);
        $existingUser = User::factory()->create(['phone' => '08340000012', 'role' => 'mother', 'is_verified' => true]);

        MotherProfile::create([
            'user_id' => $existingUser->id,
            'nik_encrypted' => $nikService->encrypt('3204015001990022'),
            'nik_hash' => $nikService->hashNIK('3204015001990022'),
            'birth_date' => '1998-01-01',
            'address' => 'Jl. Lama',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
        ]);

        // Cek duplikat di step 2 tidak mem-flash NIK
        $response = $this->withSession(['reg_mother' => ['name' => 'Dup', 'phone' => '08340000013', 'password' => Hash::make('Password123')]])
            ->from(route('register.mother.identity'))
            ->post(route('register.mother.identity.store'), [
                'nik' => '3204015001990022',
                'birth_date' => '1998-01-01',
                'address' => 'Jl. Baru',
                'subdistrict_id' => $subdistrict->id,
                'area_id' => $area->id,
            ]);

        $response->assertRedirect(route('register.mother.identity'))
            ->assertSessionHasErrors('nik');
        $this->assertArrayNotHasKey('nik', session('_old_input', []));
        $this->assertDatabaseMissing('users', ['phone' => '08340000013']);
    }

    public function test_medical_registration_enforces_password_policy_and_regenerates_session(): void
    {
        $spec = Specialization::create([
            'name' => 'Bidan',
            'role_group' => 'midwife',
            'is_active' => true,
            'sort_order' => 1,
        ]);

        // Password pendek ditolak
        $this->post(route('register.medical.store'), [
            'name' => 'Bidan Baru',
            'phone' => '08340000014',
            'password' => '123456',
            'password_confirmation' => '123456',
            'specialization_id' => $spec->id,
            'license_number' => 'STR-001',
        ])->assertSessionHasErrors('password');

        $sessionIdBefore = session()->getId();

        $response = $this->post(route('register.medical.store'), [
            'name' => 'Bidan Baru',
            'phone' => '08340000014',
            'password' => 'Password123',
            'password_confirmation' => 'Password123',
            'specialization_id' => $spec->id,
            'license_number' => 'STR-001',
        ]);

        $response->assertRedirect(route('waiting-approval'));
        $this->assertNotSame($sessionIdBefore, session()->getId());
        $this->assertDatabaseHas('users', [
            'phone' => '08340000014',
            'role' => 'midwife',
            'is_verified' => false,
        ]);
    }

    public function test_mother_profile_update_rejects_mismatched_area_and_short_password(): void
    {
        $sub1 = Subdistrict::create(['name' => 'Maju']);
        $sub2 = Subdistrict::create(['name' => 'Sehat']);
        $area1 = Area::create(['subdistrict_id' => $sub1->id, 'rw_number' => '01']);
        $area2 = Area::create(['subdistrict_id' => $sub2->id, 'rw_number' => '01']);

        $user = User::factory()->create([
            'phone' => '08340000015',
            'role' => 'mother',
            'is_verified' => true,
            'password' => Hash::make('Password123'),
        ]);
        $nikService = app(NIKService::class);
        MotherProfile::create([
            'user_id' => $user->id,
            'nik_encrypted' => $nikService->encrypt('3204015001990025'),
            'nik_hash' => $nikService->hashNIK('3204015001990025'),
            'birth_date' => '1998-01-01',
            'address' => 'Jl. Asal',
            'subdistrict_id' => $sub1->id,
            'area_id' => $area1->id,
        ]);

        // Tolak area dari kelurahan lain (SEC-008)
        $this->actingAs($user)
            ->from(route('mother.profile.edit'))
            ->post(route('mother.profile.update'), [
                'name' => 'Ibu Update',
                'phone' => '08340000015',
                'birth_date' => '1998-01-01',
                'address' => 'Jl. Baru',
                'subdistrict_id' => $sub1->id,
                'area_id' => $area2->id,
            ])
            ->assertRedirect(route('mother.profile.edit'))
            ->assertSessionHasErrors('area_id');

        // Tolak password baru < 8 karakter (SEC-014)
        $this->actingAs($user)
            ->from(route('mother.profile.password'))
            ->post(route('mother.profile.password.update'), [
                'old_password' => 'Password123',
                'password' => '123456',
                'password_confirmation' => '123456',
            ])
            ->assertRedirect(route('mother.profile.password'))
            ->assertSessionHasErrors('password');
    }

    public function test_mother_profile_edit_renders_rw_subdistrict_filter_and_reassigns_midwife_on_valid_relocation(): void
    {
        $sub1 = Subdistrict::create(['name' => 'Maju']);
        $sub2 = Subdistrict::create(['name' => 'Sehat']);
        $area1 = Area::create(['subdistrict_id' => $sub1->id, 'rw_number' => '01']);
        $area2 = Area::create(['subdistrict_id' => $sub2->id, 'rw_number' => '02']);

        $midwife1 = User::factory()->create(['phone' => '08340000021', 'role' => 'midwife', 'is_verified' => true]);
        $midwife2 = User::factory()->create(['phone' => '08340000022', 'role' => 'midwife', 'is_verified' => true]);
        MidwifeArea::create(['midwife_id' => $midwife1->id, 'area_id' => $area1->id, 'is_active' => true]);
        MidwifeArea::create(['midwife_id' => $midwife2->id, 'area_id' => $area2->id, 'is_active' => true]);

        $user = User::factory()->create([
            'phone' => '08340000023',
            'role' => 'mother',
            'is_verified' => true,
            'password' => Hash::make('Password123'),
        ]);
        $nikService = app(NIKService::class);
        MotherProfile::create([
            'user_id' => $user->id,
            'nik_encrypted' => $nikService->encrypt('3204015001990026'),
            'nik_hash' => $nikService->hashNIK('3204015001990026'),
            'birth_date' => '1998-01-01',
            'address' => 'Jl. Asal',
            'subdistrict_id' => $sub1->id,
            'area_id' => $area1->id,
            'assigned_midwife_id' => $midwife1->id,
        ]);

        // Halaman edit profil merender filter RW berbasis Kelurahan
        $this->actingAs($user)
            ->get(route('mother.profile.edit'))
            ->assertOk()
            ->assertSee('handleSubdistrictChange()', false)
            ->assertSee('filterAreasForSubdistrict', false)
            ->assertSee('data-subdistrict="'.$area2->subdistrict_id.'"', false);

        // Pindah ke Kelurahan Sehat RW 02 sukses dan otomatis reassign ke Bidan 2
        $this->actingAs($user)
            ->post(route('mother.profile.update'), [
                'name' => 'Ibu Pindah Wilayah',
                'phone' => '08340000023',
                'birth_date' => '1998-01-01',
                'address' => 'Jl. Tujuan No. 9',
                'subdistrict_id' => $sub2->id,
                'area_id' => $area2->id,
                'rt' => '03',
            ])
            ->assertRedirect(route('mother.setting'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('mother_profiles', [
            'user_id' => $user->id,
            'subdistrict_id' => $sub2->id,
            'area_id' => $area2->id,
            'assigned_midwife_id' => $midwife2->id,
        ]);
    }
}
