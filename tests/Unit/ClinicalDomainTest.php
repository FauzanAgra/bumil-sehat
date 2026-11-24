<?php

namespace Tests\Unit;

use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class ClinicalDomainTest extends TestCase
{
    use RefreshDatabase;

    private function createMotherWithPregnancy(int $weeksAgo, string $phone, string $nik): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);
        $midwife = User::firstOrCreate(
            ['phone' => '08320000099'],
            ['name' => 'Bidan Test', 'password' => bcrypt('Password123'), 'role' => 'midwife', 'is_verified' => true]
        );

        $user = User::factory()->create(['phone' => $phone, 'role' => 'mother', 'is_verified' => true]);
        $service = app(NIKService::class);
        $profile = MotherProfile::create([
            'user_id' => $user->id,
            'nik_encrypted' => $service->encrypt($nik),
            'nik_hash' => $service->hashNIK($nik),
            'birth_date' => '1997-04-01',
            'address' => 'Jl. Kenanga',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'assigned_midwife_id' => $midwife->id,
        ]);

        $hpht = now()->subWeeks($weeksAgo);
        $pregnancy = Pregnancy::create([
            'mother_id' => $profile->id,
            'hpht_date' => $hpht->toDateString(),
            'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
            'status' => 'active',
        ]);

        return [$user, $profile, $pregnancy, $midwife];
    }

    public function test_pregnancy_gestational_week_and_trimester_calculation(): void
    {
        [, , $t1] = $this->createMotherWithPregnancy(10, '08320000001', '3204015001990011');
        [, , $t2] = $this->createMotherWithPregnancy(20, '08320000002', '3204015001990012');
        [, , $t3] = $this->createMotherWithPregnancy(32, '08320000003', '3204015001990013');

        $this->assertSame(10, $t1->gestational_week);
        $this->assertSame(1, $t1->trimester);
        $this->assertSame(20, $t2->gestational_week);
        $this->assertSame(2, $t2->trimester);
        $this->assertSame(32, $t3->gestational_week);
        $this->assertSame(3, $t3->trimester);
    }

    public function test_anc_record_auto_detects_conditions_computes_bmi_and_respects_final_decisions(): void
    {
        [$user, $profile, $pregnancy, $midwife] = $this->createMotherWithPregnancy(16, '08320000004', '3204015001990014');

        $schedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        // Input dengan KEK, Preeklampsia, dan Anemia
        $record = new AncRecord([
            'schedule_id' => $schedule->id,
            'recorded_by' => $user->id,
            'weight' => 60.0,
            'height' => 160.0,
            'lila' => 22.0,
            'systolic' => 145,
            'diastolic' => 92,
            'hb' => 10.2,
            'submitted_at' => now(),
        ]);
        $record->save();

        // mother_id otomatis diturunkan dari schedule->pregnancy->mother_id (SEC-015)
        $this->assertSame($profile->id, $record->mother_id);
        $this->assertSame(23.4, $record->bmi);
        $this->assertSame('done', $schedule->fresh()->status);

        $types = $record->detectedConditions()->pluck('condition_type')->sort()->values()->all();
        $this->assertSame(['anemia', 'kek', 'preeclampsia'], $types);

        // Konfirmasi KEK oleh bidan -> tidak boleh hilang saat record diperbarui sebelum lock
        $kek = $record->detectedConditions()->where('condition_type', 'kek')->firstOrFail();
        $kek->confirm($midwife, 'Confirmed KEK');

        // Ibu perbaiki nilai Hb & TD jadi normal
        $record->update([
            'systolic' => 115,
            'diastolic' => 75,
            'hb' => 12.5,
        ]);

        $remaining = $record->detectedConditions()->get();
        $this->assertCount(1, $remaining);
        $this->assertSame('kek', $remaining->first()->condition_type);
        $this->assertSame('confirmed', $remaining->first()->status);

        // Setelah di-lock, update (bahkan mencoba unlock) dan delete ditolak
        $record->lock();

        try {
            $record->is_locked = false;
            $record->save();
            $this->fail('Record terkunci tidak boleh bisa di-unlock.');
        } catch (LogicException) {
            $this->assertTrue($record->fresh()->is_locked);
        }

        $this->expectException(LogicException::class);
        $record->delete();
    }

    public function test_anc_record_rejects_mismatched_mother_id_invariant(): void
    {
        [$userA, , $pregnancyA, $midwife] = $this->createMotherWithPregnancy(14, '08320000005', '3204015001990015');
        [, $profileB] = $this->createMotherWithPregnancy(14, '08320000006', '3204015001990016');

        $scheduleA = AncSchedule::create([
            'pregnancy_id' => $pregnancyA->id,
            'anc_number' => 2,
            'schedule_date' => today()->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $this->expectException(LogicException::class);

        $record = new AncRecord([
            'schedule_id' => $scheduleA->id,
            'recorded_by' => $userA->id,
            'submitted_at' => now(),
        ]);
        $record->mother_id = $profileB->id;
        $record->save();
    }
}
