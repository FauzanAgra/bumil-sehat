<?php

namespace Tests\Feature;

use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\TtdStatus;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MotherDashboardTest extends TestCase
{
    use RefreshDatabase;

    private function createMotherAtWeek(int $weeksAgo, string $phone, string $nik): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);
        $midwife = User::firstOrCreate(
            ['phone' => '08370000099'],
            ['name' => 'Bidan Dashboard', 'password' => bcrypt('Password123'), 'role' => 'midwife', 'is_verified' => true]
        );

        $mother = User::factory()->create(['phone' => $phone, 'role' => 'mother', 'is_verified' => true]);
        $nikService = app(NIKService::class);
        $profile = MotherProfile::create([
            'user_id' => $mother->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1996-05-15',
            'address' => 'Jl. Melati',
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

        return [$mother, $pregnancy, $midwife];
    }

    public function test_dashboard_shows_early_pregnancy_message_before_10_weeks(): void
    {
        [$mother] = $this->createMotherAtWeek(8, '08370000001', '3204015001990051');

        $this->actingAs($mother)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Fase awal kehamilan')
            ->assertSee('Rutin TTD dimulai saat ≥ 10 minggu');
    }

    public function test_dashboard_shows_waiting_anc_validation_for_37_weeks_without_ttd(): void
    {
        [$mother] = $this->createMotherAtWeek(37, '08370000002', '3204015001990052');

        $this->actingAs($mother)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Trimester 3')
            ->assertSee('Menunggu validasi ANC Bidan')
            ->assertDontSee('Fase awal kehamilan');
    }

    public function test_dashboard_shows_deactivated_by_midwife_message_when_ttd_disabled(): void
    {
        [$mother, $pregnancy] = $this->createMotherAtWeek(37, '08370000003', '3204015001990053');

        TtdStatus::create([
            'pregnancy_id' => $pregnancy->id,
            'is_active' => false,
            'started_at' => now()->subDays(10),
            'activated_by' => 'midwife',
        ]);

        $this->actingAs($mother)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Dinonaktifkan oleh Bidan')
            ->assertDontSee('Fase awal kehamilan');
    }

    public function test_dashboard_displays_upcoming_anc_schedule_time(): void
    {
        [$mother, $pregnancy, $midwife] = $this->createMotherAtWeek(20, '08370000004', '3204015001990054');

        AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->addDays(2)->toDateString(),
            'schedule_time' => '09:00',
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $this->actingAs($mother)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('09:00 WIB');
    }
}
