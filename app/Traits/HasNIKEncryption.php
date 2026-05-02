<?php

namespace App\Traits;

use App\Models\NikAuditLog;
use App\Services\NIKService;
use Illuminate\Auth\AuthenticationException;

trait HasNIKEncryption
{

    public function setNikAttribute(string $nik): void
    {
        $service = app(NIKService::class);

        $this->attributes['nik_encrypted'] = $service->encrypt($nik);
        $this->atrributes['nik_hash'] = $service->hashNIK($nik);
    }

    // ── Dekripsi NIK penuh (hanya role terotorisasi) ──────────────────
    public function getDecryptedNik(): string
    {
        if (! $this->canAccessNIK()) {
            $this->logNikAccess('FAILED', ['reason' => 'unauthorized']);

            throw new AuthenticationException(
                'Tidak memiliki izin untuk mendekripsi NIK.'
            );
        }

        $this->logNikAccess('DECRYPT');

        return app(NIKService::class)->decrypt($this->nik_encrypted);
    }

    // ── NIK dengan masking berdasarkan role ───────────────────────────
    public function getMaskedNik(?string $role = null): string
    {
        $role ??= auth()->user()?->role ?? 'publik';

        if ($role !== 'publik') {
            $this->logNikAccess('READ', ['masked' => true, 'role' => $role]);
        }

        return app(NIKService::class)->mask($this->nik_encrypted, $role);
    }

    // ── Cek otorisasi akses NIK ───────────────────────────────────────
    // Hanya admin & midwife yang boleh dekripsi NIK penuh.
    // doctor & mother tidak perlu lihat NIK plaintext.
    protected function canAccessNIK(): bool
    {
        $user = auth()->user();

        if (! $user) {
            return false;
        }

        return in_array($user->role, ['admin', 'midwife']);
    }

    // ── Catat akses ke audit log ──────────────────────────────────────
    protected function logNikAccess(string $action, array $meta = []): void
    {
        // Guard: hanya log jika model sudah tersimpan (ada ID)
        if (! $this->exists) {
            return;
        }

        NikAuditLog::record(
            motherProfileId: $this->id,
            action: $action,
            metadata: $meta,
            isAnomaly: $action === 'FAILED',
        );
    }
}
