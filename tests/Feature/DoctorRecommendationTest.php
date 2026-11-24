<?php

namespace Tests\Feature;

use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\DoctorRecommendation;
use App\Models\MedicalProfile;
use App\Models\MotherProfile;
use App\Models\Pregnancy;
use App\Models\Specialization;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorRecommendationTest extends TestCase
{
    use RefreshDatabase;

    private function seedActorsAndPatient(string $suffix = '1'): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);

        $doctorSpec = Specialization::firstOrCreate(
            ['name' => 'Dokter Umum'],
            ['role_group' => 'doctor', 'is_active' => true, 'sort_order' => 1]
        );

        $doctor = User::firstOrCreate(
            ['phone' => '08370000101'],
            [
                'name' => 'Ahmad Fauzi',
                'password' => bcrypt('Password123'),
                'role' => 'doctor',
                'is_verified' => true,
            ]
        );

        MedicalProfile::firstOrCreate(
            ['user_id' => $doctor->id],
            [
                'specialization_id' => $doctorSpec->id,
                'license_number' => 'SIP-TEST-001',
                'degree_prefix' => 'dr.',
            ]
        );

        $midwife = User::firstOrCreate(
            ['phone' => '08370000102'],
            [
                'name' => 'Sari Dewi',
                'password' => bcrypt('Password123'),
                'role' => 'midwife',
                'is_verified' => true,
            ]
        );

        $motherUser = User::factory()->create([
            'name' => 'Ibu Pasien '.$suffix,
            'phone' => '0837000020'.$suffix,
            'role' => 'mother',
            'is_verified' => true,
        ]);

        $nikService = app(NIKService::class);
        $nik = '320401500199040'.$suffix;

        $motherProfile = MotherProfile::create([
            'user_id' => $motherUser->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1996-03-14',
            'address' => 'Jl. Anggrek '.$suffix,
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
            'rt' => '002',
            'assigned_midwife_id' => $midwife->id,
        ]);

        $hpht = now()->subWeeks(24);
        $pregnancy = Pregnancy::create([
            'mother_id' => $motherProfile->id,
            'hpht_date' => $hpht->toDateString(),
            'hpl_date' => $hpht->copy()->addDays(280)->toDateString(),
            'status' => 'active',
        ]);

        $schedule = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 1,
            'schedule_date' => today()->subWeeks(4)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $record = new AncRecord([
            'schedule_id' => $schedule->id,
            'recorded_by' => $motherUser->id,
            'weight' => 58.0,
            'height' => 158.0,
            'lila' => 24.5,
            'systolic' => 142,
            'diastolic' => 92,
            'hb' => 10.8,
            'submitted_at' => now(),
        ]);
        $record->save();

        return [$doctor, $midwife, $motherUser, $motherProfile, $pregnancy, $record];
    }

    public function test_verified_doctor_can_store_recommendation_visible_to_doctor_midwife_and_mother(): void
    {
        [$doctor, $midwife, $motherUser, $motherProfile, $pregnancy, $record] = $this->seedActorsAndPatient('1');

        $note = 'Pantau tekanan darah setiap 3 hari, kurangi konsumsi garam, dan siapkan rujukan bila TD tetap tinggi.';

        $response = $this->actingAs($doctor)->post(
            route('doctor.patients.recommendations.store', $motherProfile->id),
            [
                'follow_up_level' => DoctorRecommendation::LEVEL_MONITOR,
                'anc_record_id' => $record->id,
                'recommendation' => $note,
            ]
        );

        $response->assertRedirect(route('doctor.patients.show', $motherProfile->id));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('doctor_recommendations', [
            'pregnancy_id' => $pregnancy->id,
            'anc_record_id' => $record->id,
            'doctor_id' => $doctor->id,
            'follow_up_level' => 'monitor',
            'recommendation' => $note,
        ]);

        // Tampil di Detail Pasien Dokter
        $this->actingAs($doctor)
            ->get(route('doctor.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('dr. Ahmad Fauzi')
            ->assertSee('Perlu Pemantauan')
            ->assertSee('ANC ke-1')
            ->assertSee($note);

        // Tampil di Detail Pasien Bidan Pendamping
        $this->actingAs($midwife)
            ->get(route('midwife.patients.show', $motherProfile->id))
            ->assertOk()
            ->assertSee('Rekomendasi Dokter')
            ->assertSee('dr. Ahmad Fauzi')
            ->assertSee('Perlu Pemantauan')
            ->assertSee($note);

        // Tampil di Dashboard Ibu Hamil
        $this->actingAs($motherUser)
            ->get(route('mother.dashboard'))
            ->assertOk()
            ->assertSee('Catatan dari Dokter / Nakes')
            ->assertSee('dr. Ahmad Fauzi')
            ->assertSee('Perlu Pemantauan')
            ->assertSee($note);
    }

    public function test_doctor_recommendation_rejects_anc_record_from_another_patient(): void
    {
        [$doctor, , , $motherProfileA] = $this->seedActorsAndPatient('2');
        [, , , , , $recordB] = $this->seedActorsAndPatient('3');

        $this->actingAs($doctor)
            ->postJson(
                route('doctor.patients.recommendations.store', $motherProfileA->id),
                [
                    'follow_up_level' => DoctorRecommendation::LEVEL_REFER,
                    'anc_record_id' => $recordB->id,
                    'recommendation' => 'Coba tautkan ke ANC pasien lain.',
                ]
            )
            ->assertStatus(422)
            ->assertJsonValidationErrors(['anc_record_id']);

        $this->assertDatabaseCount('doctor_recommendations', 0);
    }

    public function test_non_doctor_and_unverified_doctor_cannot_store_recommendation(): void
    {
        [, $midwife, $motherUser, $motherProfile] = $this->seedActorsAndPatient('4');

        $unverifiedDoctor = User::factory()->create([
            'name' => 'Dokter Belum Verified',
            'phone' => '08370000199',
            'role' => 'doctor',
            'is_verified' => false,
        ]);

        $payload = [
            'follow_up_level' => DoctorRecommendation::LEVEL_ROUTINE,
            'recommendation' => 'Kontrol rutin 2 minggu lagi.',
        ];

        $this->actingAs($midwife)
            ->post(route('doctor.patients.recommendations.store', $motherProfile->id), $payload)
            ->assertRedirect(route('midwife.dashboard'));

        $this->actingAs($motherUser)
            ->post(route('doctor.patients.recommendations.store', $motherProfile->id), $payload)
            ->assertRedirect(route('mother.dashboard'));

        $this->actingAs($unverifiedDoctor)
            ->post(route('doctor.patients.recommendations.store', $motherProfile->id), $payload)
            ->assertRedirect(route('waiting-approval'));

        $this->assertDatabaseCount('doctor_recommendations', 0);
    }
}
