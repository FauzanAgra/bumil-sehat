<?php

namespace Tests\Unit;

use App\Exceptions\InvalidNIKException;
use App\Models\Area;
use App\Models\MotherProfile;
use App\Models\NikAuditLog;
use App\Models\Subdistrict;
use App\Models\User;
use App\Services\NIKService;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use LogicException;
use Tests\TestCase;

class NIKServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_encrypt_decrypt_and_hash_nik_roundtrip(): void
    {
        $service = app(NIKService::class);
        $nik = '3204015001990001';

        $encrypted = $service->encrypt($nik);
        $hash1 = $service->hashNIK($nik);
        $hash2 = $service->hashNIK($nik);
        $otherHash = $service->hashNIK('3204015001990002');

        $this->assertNotSame($nik, $encrypted);
        $this->assertSame($nik, $service->decrypt($encrypted));
        $this->assertSame(64, strlen($hash1));
        $this->assertSame($hash1, $hash2);
        $this->assertNotSame($hash1, $otherHash);
    }

    public function test_invalid_nik_format_and_region_code_throw_exception(): void
    {
        $service = app(NIKService::class);

        $this->expectException(InvalidNIKException::class);
        $service->encrypt('12345');
    }

    public function test_invalid_province_code_throws_exception(): void
    {
        $service = app(NIKService::class);

        $this->expectException(InvalidNIKException::class);
        $service->hashNIK('0504015001990001');
    }

    public function test_role_based_nik_masking_rules(): void
    {
        $service = app(NIKService::class);
        $encrypted = $service->encrypt('3204015001990001');

        $this->assertSame('3204015001990001', $service->mask($encrypted, 'admin'));
        $this->assertSame('320401**********', $service->mask($encrypted, 'midwife'));
        $this->assertSame('3204************', $service->mask($encrypted, 'doctor'));
        $this->assertSame('****************', $service->mask($encrypted, 'mother'));
        $this->assertSame('****************', $service->mask($encrypted, 'publik'));
    }

    public function test_has_nik_encryption_audit_logs_and_immutability(): void
    {
        $subdistrict = Subdistrict::create(['name' => 'Maju']);
        $area = Area::create(['subdistrict_id' => $subdistrict->id, 'rw_number' => '01']);

        $motherUser = User::factory()->create(['phone' => '08310000001', 'role' => 'mother', 'is_verified' => true]);
        $doctor = User::factory()->create(['phone' => '08310000002', 'role' => 'doctor', 'is_verified' => true]);
        $midwife = User::factory()->create(['phone' => '08310000003', 'role' => 'midwife', 'is_verified' => true]);

        $service = app(NIKService::class);
        $profile = MotherProfile::create([
            'user_id' => $motherUser->id,
            'nik_encrypted' => $service->encrypt('3204015001990001'),
            'nik_hash' => $service->hashNIK('3204015001990001'),
            'birth_date' => '1999-01-10',
            'address' => 'Jl. Mawar',
            'subdistrict_id' => $subdistrict->id,
            'area_id' => $area->id,
        ]);

        // Masked read oleh dokter tercatat sebagai READ
        $this->actingAs($doctor);
        $this->assertSame('3204************', $profile->getMaskedNik());
        $this->assertDatabaseHas('nik_audit_logs', [
            'mother_profile_id' => $profile->id,
            'action' => NikAuditLog::ACTION_READ,
            'actor_id' => $doctor->id,
            'is_anomaly' => false,
        ]);

        // Dekripsi oleh dokter ditolak dan dicatat sebagai FAILED + anomaly
        try {
            $profile->getDecryptedNik();
            $this->fail('Dokter tidak boleh mendekripsi NIK penuh.');
        } catch (AuthenticationException) {
            $this->assertDatabaseHas('nik_audit_logs', [
                'mother_profile_id' => $profile->id,
                'action' => NikAuditLog::ACTION_FAILED,
                'actor_id' => $doctor->id,
                'is_anomaly' => true,
            ]);
        }

        // Dekripsi oleh bidan diizinkan dan dicatat sebagai DECRYPT
        $this->actingAs($midwife);
        $this->assertSame('3204015001990001', $profile->getDecryptedNik());
        $this->assertDatabaseHas('nik_audit_logs', [
            'mother_profile_id' => $profile->id,
            'action' => NikAuditLog::ACTION_DECRYPT,
            'actor_id' => $midwife->id,
        ]);

        // Audit log bersifat immutable (update & delete melempar LogicException)
        $log = NikAuditLog::firstOrFail();

        try {
            $log->update(['action' => NikAuditLog::ACTION_DECRYPT]);
            $this->fail('NikAuditLog tidak boleh bisa di-update.');
        } catch (LogicException) {
            $this->assertTrue(true);
        }

        $this->expectException(LogicException::class);
        $log->delete();
    }
}
