<?php

namespace Tests\Feature;

use App\Models\AncRecord;
use App\Models\AncSchedule;
use App\Models\Area;
use App\Models\Article;
use App\Models\MotherProfile;
use App\Models\NikAuditLog;
use App\Models\Pregnancy;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AncAndSecurityFlowTest extends TestCase
{
    use RefreshDatabase;

    private function seedPatient(string $phone, string $nik, User $midwife, int $weeksAgo = 16, string $status = 'active'): array
    {
        $subdistrict = Subdistrict::firstOrCreate(['name' => 'Maju']);
        $area = Area::firstOrCreate(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);

        $motherUser = User::factory()->create(['phone' => $phone, 'role' => 'mother', 'is_verified' => true]);
        $nikService = app(NIKService::class);
        $profile = MotherProfile::create([
            'user_id' => $motherUser->id,
            'nik_encrypted' => $nikService->encrypt($nik),
            'nik_hash' => $nikService->hashNIK($nik),
            'birth_date' => '1997-02-02',
            'address' => 'Jl. Dahlia',
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

        return [$motherUser, $profile, $pregnancy];
    }

    public function test_idor_boundaries_for_mother_midwife_and_doctor(): void
    {
        $midwifeA = User::factory()->create(['phone' => '08350000001', 'role' => 'midwife', 'is_verified' => true]);
        $midwifeB = User::factory()->create(['phone' => '08350000002', 'role' => 'midwife', 'is_verified' => true]);
        $doctor = User::factory()->create(['phone' => '08350000003', 'role' => 'doctor', 'is_verified' => true]);

        [$motherA, $profileA, $pregnancyA] = $this->seedPatient('08350000011', '3204015001990031', $midwifeA);
        [$motherB, $profileB, $pregnancyB] = $this->seedPatient('08350000012', '3204015001990032', $midwifeB);
        [, $inactiveProfile] = $this->seedPatient('08350000013', '3204015001990033', $midwifeA, 20, 'completed');

        $scheduleB = AncSchedule::create([
            'pregnancy_id' => $pregnancyB->id,
            'anc_number' => 2,
            'schedule_date' => today()->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwifeB->id,
        ]);

        // Mother A tidak boleh akses jadwal Mother B
        $this->actingAs($motherA)->get(route('mother.anc.detail', $scheduleB))->assertStatus(403);
        $this->actingAs($motherA)->get(route('mother.anc.form', $scheduleB))->assertStatus(403);
        $this->actingAs($motherA)->post(route('mother.anc.store', $scheduleB), ['weight' => 55])->assertStatus(403);

        // Midwife A tidak boleh akses pasien, jadwal, atau TTD milik Midwife B
        $this->actingAs($midwifeA)->get(route('midwife.patients.show', $profileB))->assertStatus(403);
        $this->actingAs($midwifeA)->patch(route('midwife.schedules.missed', $scheduleB))->assertStatus(403);
        $this->actingAs($midwifeA)->patch(route('midwife.pregnancies.toggle-ttd', $pregnancyB))->assertStatus(403);

        // Dokter dapat melihat ibu aktif (Single-Puskesmas) dengan 4 digit NIK, namun 404 jika tidak ada kehamilan aktif
        $this->actingAs($doctor)->get(route('doctor.patients.show', $profileA))->assertOk();
        $this->actingAs($doctor)->get(route('doctor.patients.show', $inactiveProfile))->assertNotFound();
    }

    public function test_mother_cannot_submit_to_ineligible_or_historical_schedules(): void
    {
        $midwife = User::factory()->create(['phone' => '08350000004', 'role' => 'midwife', 'is_verified' => true]);
        [$mother, $profile, $activePregnancy] = $this->seedPatient('08350000014', '3204015001990034', $midwife);

        $cancelled = AncSchedule::create([
            'pregnancy_id' => $activePregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->toDateString(),
            'status' => 'cancelled',
            'created_by' => $midwife->id,
        ]);

        $missed = AncSchedule::create([
            'pregnancy_id' => $activePregnancy->id,
            'anc_number' => 3,
            'schedule_date' => today()->toDateString(),
            'status' => 'missed',
            'created_by' => $midwife->id,
        ]);

        $doneWithoutRecord = AncSchedule::create([
            'pregnancy_id' => $activePregnancy->id,
            'anc_number' => 4,
            'schedule_date' => today()->toDateString(),
            'status' => 'done',
            'created_by' => $midwife->id,
        ]);

        $historicPregnancy = Pregnancy::create([
            'mother_id' => $profile->id,
            'hpht_date' => now()->subMonths(15)->toDateString(),
            'hpl_date' => now()->subMonths(6)->toDateString(),
            'status' => 'completed',
        ]);
        $historicSchedule = AncSchedule::create([
            'pregnancy_id' => $historicPregnancy->id,
            'anc_number' => 2,
            'schedule_date' => now()->subMonths(10)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        foreach ([$cancelled, $missed, $doneWithoutRecord, $historicSchedule] as $ineligible) {
            $this->actingAs($mother)
                ->post(route('mother.anc.store', $ineligible), ['weight' => 58, 'hb' => 12.0])
                ->assertStatus(422);
        }

        // Pastikan tidak ada mutasi status jadwal maupun record ANC yang tercipta
        $this->assertSame(0, AncRecord::count());
        $this->assertSame('cancelled', $cancelled->fresh()->status);
        $this->assertSame('missed', $missed->fresh()->status);
        $this->assertSame('scheduled', $historicSchedule->fresh()->status);
    }

    public function test_mandatory_usg_is_enforced_server_side_for_anc_1_and_5(): void
    {
        Storage::fake('public');

        $midwife = User::factory()->create(['phone' => '08350000005', 'role' => 'midwife', 'is_verified' => true]);
        [$mother, , $pregnancy] = $this->seedPatient('08350000015', '3204015001990035', $midwife);

        $anc1 = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 1,
            'schedule_date' => today()->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        $anc2 = AncSchedule::create([
            'pregnancy_id' => $pregnancy->id,
            'anc_number' => 2,
            'schedule_date' => today()->addWeeks(4)->toDateString(),
            'status' => 'scheduled',
            'created_by' => $midwife->id,
        ]);

        // ANC ke-1 tanpa USG ditolak oleh server (SEC-012)
        $this->actingAs($mother)
            ->from(route('mother.anc.form', $anc1))
            ->post(route('mother.anc.store', $anc1), ['weight' => 56, 'hb' => 12.1])
            ->assertRedirect(route('mother.anc.form', $anc1))
            ->assertSessionHasErrors('usg_image');

        $this->assertSame('scheduled', $anc1->fresh()->status);
        $this->assertNull($anc1->fresh()->ancRecord);

        // ANC ke-1 dengan file USG berhasil disimpan
        $this->actingAs($mother)
            ->post(route('mother.anc.store', $anc1), [
                'weight' => 56,
                'hb' => 12.1,
                'usg_image' => UploadedFile::fake()->image('usg-anc1.jpg'),
            ])
            ->assertRedirect(route('mother.anc.detail', $anc1));

        $record1 = $anc1->fresh()->ancRecord;
        $this->assertNotNull($record1);
        $this->assertNotNull($record1->usgImage);
        Storage::disk('public')->assertExists($record1->usgImage->image_url);

        // Edit ulang ANC ke-1 sebelum lock tidak wajib upload ulang USG karena sudah ada
        $this->actingAs($mother)
            ->post(route('mother.anc.store', $anc1), ['weight' => 57, 'hb' => 12.2])
            ->assertRedirect(route('mother.anc.detail', $anc1));
        $this->assertEquals(57.0, (float) $record1->fresh()->weight);

        // ANC ke-2 tanpa USG tetap diperbolehkan
        $this->actingAs($mother)
            ->post(route('mother.anc.store', $anc2), ['weight' => 58, 'hb' => 12.0])
            ->assertRedirect(route('mother.anc.detail', $anc2));
        $this->assertNotNull($anc2->fresh()->ancRecord);
    }

    public function test_mother_profile_views_log_nik_audit_read_and_never_expose_plaintext(): void
    {
        $midwife = User::factory()->create(['phone' => '08350000006', 'role' => 'midwife', 'is_verified' => true]);
        $rawNik = '3204015001990036';
        [$mother, $profile] = $this->seedPatient('08350000016', $rawNik, $midwife);

        $this->actingAs($mother)
            ->get(route('mother.setting'))
            ->assertOk()
            ->assertSee('****************')
            ->assertDontSee($rawNik);

        $this->actingAs($mother)
            ->get(route('mother.profile.edit'))
            ->assertOk()
            ->assertSee('****************')
            ->assertDontSee($rawNik);

        $this->assertSame(
            2,
            NikAuditLog::where('mother_profile_id', $profile->id)
                ->where('action', NikAuditLog::ACTION_READ)
                ->where('actor_id', $mother->id)
                ->count()
        );
    }

    public function test_article_detail_sanitizes_xss_payload_and_keeps_safe_html(): void
    {
        $admin = User::factory()->create(['phone' => '08350000007', 'role' => 'admin', 'is_verified' => true]);
        $midwife = User::factory()->create(['phone' => '08350000008', 'role' => 'midwife', 'is_verified' => true]);
        [$mother] = $this->seedPatient('08350000017', '3204015001990037', $midwife, 20);

        $article = Article::create([
            'title' => 'Tips Sehat Ibu Hamil',
            'slug' => 'tips-sehat-ibu-hamil',
            'content' => '<h2>Panduan Aman</h2><p>Minum <strong>TTD</strong> rutin.</p><script>alert("xss")</script><img src=x onerror="alert(1)"><a href="javascript:alert(2)">Klik</a>',
            'min_week' => 1,
            'max_week' => 40,
            'status' => 'published',
            'created_by' => $admin->id,
            'published_by' => $admin->id,
            'published_at' => now(),
        ]);

        $response = $this->actingAs($mother)->get(route('mother.articles.show', $article->slug));

        $response->assertOk()
            ->assertSee('<h2>Panduan Aman</h2>', false)
            ->assertSee('<strong>TTD</strong>', false)
            ->assertDontSee('<script>', false)
            ->assertDontSee('onerror=', false)
            ->assertDontSee('javascript:', false);
    }
}
