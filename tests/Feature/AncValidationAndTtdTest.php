<?php

namespace Tests\Feature;

use App\Events\AncValidationCreated;
use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\TtdStatus;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Tests\TestCase;

class AncValidationAndTtdTest extends TestCase
{
    use RefreshDatabase;

    private function createScenario(int $weeksAgo, string $phone, string $nik, string $status = 'active'): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);
        $midwife = User::firstOrCreate(
            ['phone' => '08360000099'],
            ['name' => 'Bidan Validasi', 'password' => bcrypt('Password123'), 'role' => 'midwife', 'is_verified' => true]
        );

        $mother = User::factory()->create(['phone' => $phone, 'role' => 'mother', 'is_verified' => true]);
        $nikService = app(NIKService::class);
        $profile = MotherProfile::create([
            'user_id' => $mother->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1997-08-12',
            'address' => 'Jl. Anggrek',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'assigned_midwife_id' => $midwife->id,
        ]);

        $hpht = now()->subWeeks($weeksAgo);
        $pregnancy = Pregnancy::create([
            'mother_id' => $profile->id,
            'hpht_date' => $hpht->toDateString(),
            'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
            'status' => $status,
        ]);

        $schedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $record = new AncRecord([
            'schedule_id' => $schedule->id,
            'recorded_by' => $mother->id,
            'weight' => 55,
            'height' => 158,
            'lila' => 22.0,
            'hb' => 10.5,
            'submitted_at' => now(),
        ]);
        $record->save();

        return [$mother, $profile, $pregnancy, $schedule, $record, $midwife];
    }

    public function test_midwife_validation_locks_record_activates_ttd_at_10_plus_weeks_and_blocks_subsequent_mother_edit(): void
    {
        [$mother, $profile, $pregnancy, $schedule, $record, $midwife] = $this->createScenario(12, '08360000001', '3204015001990041');

        $kek = $record->detectedConditions()->where('condition_type', 'kek')->firstOrFail();
        $anemia = $record->detectedConditions()->where('condition_type', 'anemia')->firstOrFail();

        $this->actingAs($midwife)
            ->post(route('midwife.anc.validate.store', $record), [
                'notes' => 'Perlu pantau nutrisi dan TTD rutin.',
                'conditions' => [
                    ['id' => $kek->id, 'action' => 'confirm', 'notes' => 'LILA di bawah ambang'],
                    ['id' => $anemia->id, 'action' => 'reject', 'notes' => 'Salah input lab'],
                ],
            ])
            ->assertRedirect(route('midwife.patients.show', $profile->id));

        $this->assertTrue($record->fresh()->is_locked);
        $this->assertSame('confirmed', $kek->fresh()->status);
        $this->assertSame('rejected', $anemia->fresh()->status);
        $this->assertDatabaseHas('ttd_statuses', [
            'pregnancy_id' => $pregnancy->id,
            'is_active' => true,
            'activated_by' => 'system',
        ]);

        // Ibu tidak dapat menimpa data setelah dikunci Bidan (SEC-004)
        $this->actingAs($mother)
            ->post(route('mother.anc.store', $schedule), ['weight' => 99, 'hb' => 15.0])
            ->assertStatus(422);

        $this->assertEquals(55.0, (float) $record->fresh()->weight);
        $this->assertEquals(10.5, (float) $record->fresh()->hb);

        // Validasi ulang pada record terkunci ditolak
        $this->actingAs($midwife)
            ->post(route('midwife.anc.validate.store', $record), ['notes' => 'Double'])
            ->assertStatus(403);
    }

    public function test_auto_ttd_does_not_activate_before_10_weeks(): void
    {
        [, $profile, $pregnancy, , $record, $midwife] = $this->createScenario(8, '08360000002', '3204015001990042');

        $this->actingAs($midwife)
            ->post(route('midwife.anc.validate.store', $record), ['notes' => 'Kontrol awal'])
            ->assertRedirect(route('midwife.patients.show', $profile->id));

        $this->assertTrue($record->fresh()->is_locked);
        $this->assertDatabaseMissing('ttd_statuses', ['pregnancy_id' => $pregnancy->id]);
    }

    public function test_validating_historic_pregnancy_anc_does_not_activate_ttd_on_newer_active_pregnancy(): void
    {
        [, $profile, $historicPregnancy, , $historicRecord, $midwife] = $this->createScenario(30, '08360000003', '3204015001990043', 'completed');

        // Ibu punya kehamilan baru yang aktif (>= 10 minggu)
        $newHpht = now()->subWeeks(14);
        $newerActivePregnancy = Pregnancy::create([
            'mother_id' => $profile->id,
            'hpht_date' => $newHpht->toDateString(),
            'hpl_date' => $newHpht->copy()->addDays(280)->toDateString(),
            'status' => 'active',
        ]);

        $this->actingAs($midwife)
            ->post(route('midwife.anc.validate.store', $historicRecord), ['notes' => 'Validasi arsip'])
            ->assertRedirect(route('midwife.patients.show', $profile->id));

        // Tidak boleh mengaktifkan TTD pada kehamilan lama maupun kehamilan baru (SEC-006)
        $this->assertDatabaseMissing('ttd_statuses', ['pregnancy_id' => $historicPregnancy->id]);
        $this->assertDatabaseMissing('ttd_statuses', ['pregnancy_id' => $newerActivePregnancy->id]);
    }

    public function test_validation_rolls_back_atomically_if_ttd_activation_listener_fails(): void
    {
        [, , $pregnancy, , $record, $midwife] = $this->createScenario(16, '08360000004', '3204015001990044');

        Event::listen(AncValidationCreated::class, function (): void {
            throw new RuntimeException('Simulated TTD activation failure');
        });

        $response = $this->actingAs($midwife)
            ->post(route('midwife.anc.validate.store', $record), ['notes' => 'Test rollback']);

        $response->assertStatus(500);

        // Karena atomik, record tidak boleh terkunci dan AncValidation tidak boleh tersisa (SEC-010)
        $this->assertFalse($record->fresh()->is_locked);
        $this->assertDatabaseMissing('anc_validations', ['anc_record_id' => $record->id]);
        $this->assertDatabaseMissing('ttd_statuses', ['pregnancy_id' => $pregnancy->id]);
    }

    public function test_midwife_toggle_ttd_and_mother_daily_intake_logging(): void
    {
        [$mother, , $pregnancy, , , $midwife] = $this->createScenario(15, '08360000005', '3204015001990045');

        // Toggle pertama membuat & mengaktifkan TTD
        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.toggle-ttd', $pregnancy))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => true]);

        $ttd = TtdStatus::where('pregnancy_id', $pregnancy->id)->firstOrFail();
        $startedAt = $ttd->started_at;

        // Ibu mencatat konsumsi TTD harian (idempoten per hari)
        $this->actingAs($mother)->post(route('mother.ttd.take'))->assertRedirect();
        $this->actingAs($mother)->post(route('mother.ttd.take'))->assertRedirect();
        $this->assertSame(1, $ttd->logs()->where('is_taken', true)->count());

        // Toggle nonaktif lalu aktif kembali mempertahankan started_at awal
        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.toggle-ttd', $pregnancy))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => false]);

        $this->actingAs($midwife)
            ->patch(route('midwife.pregnancies.toggle-ttd', $pregnancy))
            ->assertOk()
            ->assertJson(['success' => true, 'is_active' => true]);

        $this->assertSame($startedAt->toDateTimeString(), $ttd->fresh()->started_at->toDateTimeString());
    }
}
