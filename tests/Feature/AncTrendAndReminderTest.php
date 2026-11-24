<?php

namespace Tests\Feature;

use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AncTrendAndReminderTest extends TestCase
{
    use RefreshDatabase;

    private function seedMotherAndMidwife(string $suffix = '1'): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);

        $midwife = User::firstOrCreate(
            ['phone' => '08380000101'],
            [
                'name' => 'Bidan Sari',
                'password' => bcrypt('Password123'),
                'role' => 'midwife',
                'is_verified' => true,
            ]
        );

        $motherUser = User::factory()->create([
            'name' => 'Ani Lestari '.$suffix,
            'phone' => '0812345678'.$suffix,
            'role' => 'mother',
            'is_verified' => true,
        ]);

        $nikService = app(NIKService::class);
        $nik = '320401500199050'.$suffix;

        $motherProfile = MotherProfile::create([
            'user_id' => $motherUser->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1997-08-12',
            'address' => 'Jl. Dahlia '.$suffix,
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'rt' => '003',
            'assigned_midwife_id' => $midwife->id,
        ]);

        $hpht = now()->subWeeks(20);
        $pregnancy = Pregnancy::create([
            'mother_id' => $motherProfile->id,
            'hpht_date' => $hpht->toDateString(),
            'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
            'status' => 'active',
        ]);

        return [$midwife, $motherUser, $motherProfile, $pregnancy];
    }

    public function test_anc_schedule_reminder_helpers_handle_today_tomorrow_and_future(): void
    {
        [$midwife, , , $pregnancy] = $this->seedMotherAndMidwife('1');

        $todaySchedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 1,
            'schedule_date' => today()->toDateString(),
            'schedule_time' => '09:00',
            'note' => 'Poli KIA Puskesmas',
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $tomorrowSchedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->addDay()->toDateString(),
            'schedule_time' => '10:00',
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $futureSchedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 3,
            'schedule_date' => today()->addDays(5)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $doneTodaySchedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 4,
            'schedule_date' => today()->toDateString(),
            'status' => 'done',
            'created_by' => $midwife->id,
        ]);

        $this->assertTrue($todaySchedule->isReminderDue());
        $this->assertSame('Kontrol Hari Ini', $todaySchedule->reminder_label);

        $waUrl = $todaySchedule->buildWhatsAppReminderUrl();
        $this->assertNotNull($waUrl);
        $this->assertStringStartsWith('https://wa.me/628123456781?text=', $waUrl);
        $this->assertStringContainsString(rawurlencode('ANC ke-1'), $waUrl);
        $this->assertStringContainsString(rawurlencode('09:00 WIB'), $waUrl);
        $this->assertStringContainsString(rawurlencode('Poli KIA Puskesmas'), $waUrl);

        $this->assertTrue($tomorrowSchedule->isReminderDue());
        $this->assertSame('Kontrol Besok (H-1)', $tomorrowSchedule->reminder_label);

        $this->assertFalse($futureSchedule->isReminderDue());
        $this->assertNull($futureSchedule->reminder_label);

        $this->assertFalse($doneTodaySchedule->isReminderDue());
        $this->assertNull($doneTodaySchedule->reminder_label);
    }

    public function test_mother_dashboard_displays_reminder_banner_for_h_minus_1_and_today_only(): void
    {
        [$midwife, $motherUser, , $pregnancy] = $this->seedMotherAndMidwife('2');

        // Jadwal H+5 -> banner tidak tampil
        $schedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->addDays(5)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $this->actingAs($motherUser)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertDontSee('Kontrol Hari Ini')
            ->assertDontSee('Kontrol Besok (H-1)');

        // Ubah ke besok (H-1) -> banner H-1 tampil
        $schedule->update(['schedule_date' => today()->addDay()->toDateString()]);

        $this->actingAs($motherUser)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Kontrol Besok (H-1)')
            ->assertSee('Lihat Detail Jadwal');

        // Ubah ke hari ini (Hari-H) -> banner Hari-H tampil
        $schedule->update(['schedule_date' => today()->toDateString()]);

        $this->actingAs($motherUser)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Kontrol Hari Ini')
            ->assertSee('Isi Data ANC Sekarang');
    }

    public function test_midwife_schedules_and_patient_detail_render_whatsapp_reminder_button(): void
    {
        [$midwife, , $motherProfile, $pregnancy] = $this->seedMotherAndMidwife('3');

        AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 1,
            'schedule_date' => today()->addDay()->toDateString(),
            'schedule_time' => '08:00',
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $this->actingAs($midwife)
            ->get(route('midwife.schedules', ['filter' => 'tomorrow']))
            ->assertOk()
            ->assertSee('Kontrol Besok (H-1)')
            ->assertSee('Ingatkan via WA')
            ->assertSee('https://wa.me/628123456783?text=');

        $this->actingAs($midwife)
            ->get(route('midwife.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('Kontrol Besok (H-1)')
            ->assertSee('Ingatkan via WA')
            ->assertSee('https://wa.me/628123456783?text=');
    }

    public function test_anc_trend_charts_render_empty_state_and_clinical_data_across_all_three_roles(): void
    {
        [$midwife, $motherUser, $motherProfile, $pregnancy] = $this->seedMotherAndMidwife('4');

        $doctor = User::factory()->create([
            'name' => 'Dokter Grafik',
            'phone' => '08380000199',
            'role' => 'doctor',
            'is_verified' => true,
        ]);

        // Empty state sebelum ada data klinis ANC
        $this->actingAs($midwife)
            ->get(route('midwife.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('Belum ada data klinis ANC');

        $this->actingAs($doctor)
            ->get(route('doctor.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('Belum ada data klinis ANC');

        $this->actingAs($motherUser)
            ->get(route('mother.anc.history'))
            ->assertOk()
            ->assertSee('Belum ada data klinis ANC');

        // Isi ANC 1 & ANC 2 dengan data klinis
        $schedule1 = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 1,
            'schedule_date' => today()->subWeeks(8)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        (new AncRecord([
            'schedule_id' => $schedule1->id,
            'recorded_by' => $motherUser->id,
            'weight' => 54.0,
            'height' => 158.0,
            'lila' => 24.0,
            'systolic' => 120,
            'diastolic' => 80,
            'hb' => 11.8,
            'submitted_at' => now()->subWeeks(8),
        ]))->save();

        $schedule2 = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->subWeeks(2)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        (new AncRecord([
            'schedule_id' => $schedule2->id,
            'recorded_by' => $motherUser->id,
            'weight' => 55.5,
            'height' => 158.0,
            'lila' => 23.1,
            'systolic' => 142,
            'diastolic' => 92,
            'hb' => 10.6,
            'submitted_at' => now()->subWeeks(2),
        ]))->save();

        // Midwife (tema rose)
        $this->actingAs($midwife)
            ->get(route('midwife.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('data-anc-trend-chart', false)
            ->assertSee('data-theme="rose"', false)
            ->assertSee('ANC 1')
            ->assertSee('ANC 2')
            ->assertSee('55.5')
            ->assertSee('142')
            ->assertSee('10.6')
            ->assertSee('23.1');

        // Doctor (tema blue)
        $this->actingAs($doctor)
            ->get(route('doctor.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('data-anc-trend-chart', false)
            ->assertSee('data-theme="blue"', false)
            ->assertSee('ANC 2')
            ->assertSee('142');

        // Mother ANC history (tema rose)
        $this->actingAs($motherUser)
            ->get(route('mother.anc.history'))
            ->assertOk()
            ->assertSee('Perkembangan Kesehatan Bunda')
            ->assertSee('data-anc-trend-chart', false)
            ->assertSee('data-theme="rose"', false)
            ->assertSee('ANC 2')
            ->assertSee('55.5');
    }
}
