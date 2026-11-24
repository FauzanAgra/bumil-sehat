<?php

namespace Tests\Feature;

use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\TtdStatus;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class PregnancyLifecycleTest extends TestCase
{
    use RefreshDatabase;

    private function seedClinicalFixture(int $weeksAgo = 37): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);

        $assignedMidwife = User::factory()->create([
            'name' => 'Bidan Sari',
            'phone' => '08360000101',
            'role' => 'midwife',
            'is_verified' => true,
        ]);

        $otherMidwife = User::factory()->create([
            'name' => 'Bidan Lain',
            'phone' => '08360000102',
            'role' => 'midwife',
            'is_verified' => true,
        ]);

        $motherUser = User::factory()->create([
            'name' => 'Budi Wati',
            'phone' => '08360000201',
            'role' => 'mother',
            'is_verified' => true,
        ]);

        $nikService = app(NIKService::class);
        $nik = '3204015001990301';

        $motherProfile = MotherProfile::create([
            'user_id' => $motherUser->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1997-05-10',
            'address' => 'Jl. Melati No. 12',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'rt' => '001',
            'assigned_midwife_id' => $assignedMidwife->id,
        ]);

        $hpht = now()->subWeeks($weeksAgo);
        $pregnancy = Pregnancy::create([
            'mother_id' => $motherProfile->id,
            'hpht_date' => $hpht->toDateString(),
            'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
            'status' => 'active',
        ]);

        return [$assignedMidwife, $otherMidwife, $motherUser, $motherProfile, $pregnancy];
    }

    public function test_assigned_midwife_can_complete_pregnancy_freezing_gestational_week_and_deactivating_ttd(): void
    {
        [$midwife, , , $motherProfile, $pregnancy] = $this->seedClinicalFixture(37);

        $ttd = TtdStatus::create([
            'pregnancy_id' => $pregnancy->id,
            'is_active' => true,
            'started_at' => now()->subWeeks(10),
            'activated_by' => 'system',
        ]);

        $response = $this->actingAs($midwife)->patch(
            route('midwife.pregnancies.complete', $pregnancy->id),
            ['completion_note' => 'Persalinan normal di Puskesmas, ibu dan bayi sehat.']
        );

        $response->assertRedirect(route('midwife.patients.show', $motherProfile->id));
        $response->assertSessionHas('success');

        $pregnancy->refresh();
        $this->assertSame('completed', $pregnancy->status);
        $this->assertNotNull($pregnancy->completed_at);
        $this->assertSame($midwife->id, $pregnancy->completed_by);
        $this->assertSame('Persalinan normal di Puskesmas, ibu dan bayi sehat.', $pregnancy->completion_note);
        $this->assertFalse($ttd->fresh()->is_active);

        // Usia kehamilan tetap membeku pada completed_at meskipun waktu maju 12 minggu
        Carbon::setTestNow(now()->addWeeks(12));
        try {
            $this->assertSame(37, $pregnancy->fresh()->gestational_week);
        } finally {
            Carbon::setTestNow();
        }

        // Detail pasien Bidan tetap render aman (200) tanpa kehamilan aktif & menampilkan arsip selesai
        $this->actingAs($midwife)
            ->get(route('midwife.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('Tidak Ada Kehamilan Aktif')
            ->assertSee('Mulai Kehamilan Baru')
            ->assertSee('Riwayat Kehamilan Selesai')
            ->assertSee('Persalinan normal di Puskesmas, ibu dan bayi sehat.');

        // Kehamilan selesai tidak bisa di-toggle TTD atau ditutup ulang
        $this->actingAs($midwife)
            ->patchJson(route('midwife.pregnancies.toggle-ttd', $pregnancy->id))
            ->assertStatus(422);

        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.complete', $pregnancy->id))
            ->assertStatus(422);
    }

    public function test_unassigned_midwife_cannot_complete_or_start_pregnancy_for_other_patient(): void
    {
        [, $otherMidwife, , $motherProfile, $pregnancy] = $this->seedClinicalFixture(35);

        $this->actingAs($otherMidwife)
            ->patch(route('midwife.pregnancies.complete', $pregnancy->id), [
                'completion_note' => 'Coba tutup pasien bidan lain',
            ])
            ->assertForbidden();

        $this->assertSame('active', $pregnancy->fresh()->status);

        $this->actingAs($otherMidwife)
            ->post(route('midwife.patients.pregnancies.store', $motherProfile->id), [
                'hpht_date' => now()->subWeeks(6)->toDateString(),
            ])
            ->assertForbidden();
    }

    public function test_cannot_start_new_pregnancy_while_active_and_can_start_after_completion(): void
    {
        [$midwife, , $motherUser, $motherProfile, $pregnancy] = $this->seedClinicalFixture(38);

        $newHpht = now()->subWeeks(6)->toDateString();

        // Ditolak saat masih ada kehamilan aktif (baik oleh Bidan maupun Ibu)
        $this->actingAs($midwife)
            ->post(route('midwife.patients.pregnancies.store', $motherProfile->id), [
                'hpht_date' => $newHpht,
            ])
            ->assertStatus(422);

        $this->actingAs($motherUser)
            ->post(route('mother.pregnancies.store'), [
                'hpht_date' => $newHpht,
            ])
            ->assertStatus(422);

        // Tutup kehamilan pertama oleh Bidan pendamping
        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.complete', $pregnancy->id))
            ->assertRedirect();

        // Ibu melihat form kehamilan baru & riwayat kehamilan sebelumnya di Dashboard
        $this->actingAs($motherUser)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Belum Ada Kehamilan Aktif')
            ->assertSee('Mulai Kehamilan Baru')
            ->assertSee('Riwayat Kehamilan Sebelumnya');

        // Ibu memulai kehamilan ke-2
        $this->actingAs($motherUser)
            ->post(route('mother.pregnancies.store'), [
                'hpht_date' => $newHpht,
            ])
            ->assertRedirect(route('mother.dashboard'))
            ->assertSessionHas('success');

        $secondPregnancy = $motherProfile->fresh()->activePregnancy;
        $this->assertNotNull($secondPregnancy);
        $this->assertNotSame($pregnancy->id, $secondPregnancy->id);
        $this->assertSame($newHpht, $secondPregnancy->hpht_date->toDateString());
        $this->assertSame(
            Carbon::parse($newHpht)->addDays(280)->toDateString(),
            $secondPregnancy->hpl_date->toDateString()
        );
        $this->assertSame(6, $secondPregnancy->gestational_week);

        // Tutup kehamilan ke-2 lalu Bidan memulai kehamilan ke-3
        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.complete', $secondPregnancy->id))
            ->assertRedirect();

        $thirdHpht = now()->subWeeks(8)->toDateString();
        $this->actingAs($midwife)
            ->post(route('midwife.patients.pregnancies.store', $motherProfile->id), [
                'hpht_date' => $thirdHpht,
            ])
            ->assertRedirect(route('midwife.patients.show', $motherProfile->id))
            ->assertSessionHas('success');

        $this->assertSame(3, $motherProfile->pregnancies()->count());
        $this->assertSame(1, $motherProfile->pregnancies()->active()->count());
        $this->assertSame(2, $motherProfile->pregnancies()->completed()->count());
    }
}
