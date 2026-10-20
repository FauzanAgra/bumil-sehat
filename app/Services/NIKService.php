<?php

namespace App\Services;

use App\Exceptions\InvalidNIKException;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Support\Facades\Crypt;
use RuntimeException;

class NIKService
{
    private readonly string $salt;

    public function __construct()
    {
        $salt = config('app.nik_hash_salt');

        if (empty($salt)) {
            throw new RuntimeException(
                'NIK_HASH_SALT belum diset di .env. '.
                    'Generate dengan: php artisan tinker --execute="echo bin2hex(random_bytes(32));"'
            );
        }

        $this->salt = $salt;
    }

    /**
     * Encrypt NIK
     *
     * @throws InvalidNIKException
     */
    public function encrypt(string $nik): string
    {
        $this->validate($nik);

        return Crypt::encryptString($nik);
    }

    /**
     * Decrypt Ciphertext
     *
     * Kembalikan NIK plaintext dari ciphertext.
     * Jangan panggil langsung dari Controller — gunakan
     * MotherProfile::getDecryptedNik()
     *
     * @throws DecryptException
     */
    public function decrypt(string $encryptedNik): string
    {
        try {
            return Crypt::decryptString($encryptedNik);
        } catch (DecryptException $e) {
            throw new DecryptException(
                'Gagal mendekripsi NIK. Ciphertext rusak atau kunci enkripsi berubah.',
                previous: $e
            );
        }
    }

    /**
     * Hash NIK
     *
     * Buat HMAC-SHA256 dari NIK menggunakan salt dari .env.
     * One-way — tidak bisa dikembalikan ke NIK asli.
     * Dipakai untuk unique constraint dan pencarian di DB.
     *
     * PENTING: NIK_HASH_SALT tidak boleh diubah setelah production.
     *
     * @throws InvalidNIKException
     */
    public function hashNIK(string $nik): string
    {
        $this->validate($nik);

        return hash_hmac('sha256', $nik, $this->salt);
    }

    //

    /**
     * Masking NIK
     * Tampilkan NIK dengan masking sesuai role (data minimization).
     *
     *   admin    → penuh  : 3204015001990001
     *   midwife  → 6 digit: 320401**********
     *   doctor   → 4 digit: 3204************
     *   lainnya  → semua  : ****************
     *
     * Menerima NIK plaintext atau ciphertext (otomatis dideteksi).
     */
    public function mask(string $nikOrEncrypted, string $role): string
    {
        if ($role === 'admin') {
            $nik = $this->resolvePlaintext($nikOrEncrypted);

            return $nik ?? str_repeat('*', 16);
        }

        if ($role === 'midwife') {
            $nik = $this->resolvePlaintext($nikOrEncrypted);

            return $nik ? substr($nik, 0, 6).str_repeat('*', 10) : str_repeat('*', 16);
        }

        if ($role === 'doctor') {
            $nik = $this->resolvePlaintext($nikOrEncrypted);

            return $nik ? substr($nik, 0, 4).str_repeat('*', 12) : str_repeat('*', 16);
        }

        return str_repeat('*', 16);
    }

    // Helper

    /**
     * Validasi format NIK: 16 digit angka + kode provinsi 11–99.
     *
     * @throws InvalidNIKException
     */
    private function validate(string $nik): void
    {
        if (! preg_match('/^\d{16}$/', $nik)) {
            throw InvalidNIKException::invalidFormat();
        }

        $provinceCode = (int) substr($nik, 0, 2);
        if ($provinceCode < 11 || $provinceCode > 99) {
            throw InvalidNIKException::invalidRegionCode(substr($nik, 0, 2));
        }
    }

    /**
     * Resolve input ke NIK plaintext.
     * Jika input sudah 16 digit angka → langsung return.
     * Jika ciphertext → dekripsi dulu.
     * Jika gagal → return null.
     */
    private function resolvePlaintext(string $input): ?string
    {
        if (preg_match('/^\d{16}$/', $input)) {
            return $input;
        }

        try {
            return $this->decrypt($input);
        } catch (DecryptException) {
            return null;
        }
    }
}
