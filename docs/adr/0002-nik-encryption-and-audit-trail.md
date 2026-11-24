# ADR-0002: Enkripsi Dual-Storage NIK & Audit Log Immutable (UU PDP)

- **Status**: Accepted
- **Date**: 2026-11-24

## Context
BumilSehat memproses Nomor Induk Kependudukan (NIK) dan data kesehatan kehamilan yang termasuk kategori **Data Pribadi Spesifik** berdasarkan **UU No. 27 Tahun 2022 tentang Perlindungan Data Pribadi (UU PDP)**. Menyimpan NIK dalam bentuk plaintext di database sangat berisiko jika terjadi kebocoran basis data atau backup, sedangkan enkripsi acak (AES dengan IV unik) saja tidak memungkinkan pengecekan duplikasi (`UNIQUE`) maupun pencarian tepat (`WHERE nik = ?`) secara efisien di database.

## Decision
1. **Dual-Storage NIK** pada tabel `mother_profiles`:
   - `nik_encrypted` (`TEXT`): ciphertext **AES-256-CBC** melalui Laravel `Crypt::encryptString()`.
   - `nik_hash` (`VARCHAR(64) UNIQUE`): **HMAC-SHA256** menggunakan secret salt khusus `NIK_HASH_SALT` (via `config('app.nik_hash_salt')`) untuk *uniqueness constraint* dan pencarian exact melalui `MotherProfile::byNik()`.
2. **Sentralisasi Akses**: Semua operasi enkripsi, dekripsi, hash, validasi format (16 digit + kode provinsi `11–99`), dan *masking* terpusat di singleton `App\Services\NIKService` dan trait `App\Traits\HasNIKEncryption`. Kolom `nik_encrypted` & `nik_hash` dimasukkan ke `$hidden` pada `MotherProfile`.
3. **Data Minimization (Masking per Role)**:
   - `admin`: 16 digit penuh.
   - `midwife`: 6 digit awal + 10 `*` (diizinkan dekripsi penuh melalui `getDecryptedNik()`).
   - `doctor`: 4 digit awal + 12 `*` (tidak diizinkan dekripsi penuh).
   - `mother` / lainnya: 16 `*`.
4. **Immutable Audit Log (`nik_audit_logs`)**: Setiap operasi `READ`, `DECRYPT`, dan `FAILED` dicatat bersama `actor_id`, `actor_role`, `ip_address`, `user_agent`, `metadata`, dan flag `is_anomaly`. Model `NikAuditLog` menolak `updating`/`deleting` dengan `LogicException`, dan migrasi melarang `dropIfExists` pada environment `production`.

## Alternatives
1. **Menyimpan NIK plaintext dengan enkripsi disk/database saja (TDE)**: Tidak melindungi terhadap kebocoran SQL dump, akses DBA, atau query aplikasi tidak sah, dan tidak menyediakan kontrol masking per role.
2. **Enkripsi deterministik tanpa HMAC terpisah**: Lebih lemah terhadap analisis pola ciphertext dibanding kombinasi AES IV-acak + HMAC-SHA256.

## Consequences
- **Positif**: NIK tidak pernah tersimpan plaintext di database; mendukung pencarian exact & unique check cepat via index `nik_hash`; kepatuhan audit dan *data minimization* kuat.
- **Negatif / Kewajiban**: `APP_KEY` dan `NIK_HASH_SALT` wajib dijaga permanen setelah data production masuk; seluruh tampilan Blade dan Filament wajib memakai `MotherProfile::getMaskedNik()` agar tidak melewati audit log (perbaikan di Wave 2 `SEC-007`), dan registrasi tidak boleh menyimpan NIK mentah di session (`SEC-002`).
