# ADR-0003: Autentikasi Nomor HP, Otorisasi Peran & Cakupan Single-Puskesmas

- **Status**: Accepted
- **Date**: 2026-11-24

## Context
Pengguna utama BumilSehat adalah Ibu Hamil dan tenaga kesehatan Puskesmas yang jauh lebih familiar menggunakan **nomor HP** dibandingkan email. Selain itu, terdapat 4 peran dengan batas kewenangan berbeda (`mother`, `midwife`, `doctor`, `admin`), dan audit keamanan (`SEC-RISK-001`) membutuhkan kejelasan kebijakan apakah peran `doctor` boleh melihat seluruh ibu hamil di aplikasi.

## Decision
1. **Login Berbasis Nomor HP**: Autentikasi pengguna akhir menggunakan pasangan `phone` (unik) dan `password` dengan Laravel Session Auth serta rate limiting `throttle:10,1`.
2. **Pemisahan Alur Verifikasi (`EnsureRole`)**:
   - Akun `mother` langsung aktif (`is_verified = true`) setelah menyelesaikan registrasi 3 langkah.
   - Akun tenaga medis (`midwife`, `doctor`) dibuat dengan `is_verified = false` dan ditahan di `/waiting-approval` sampai diverifikasi Admin.
3. **Isolasi Admin Panel Filament 5**: Model `User` mengimplementasikan `Filament\Models\Contracts\FilamentUser` dengan `canAccessPanel(Panel $panel): bool` yang hanya mengizinkan `$this->role === 'admin'`.
4. **Cakupan Akses Peran (Single-Puskesmas)**:
   - Aplikasi dirancang untuk **1 instance = 1 Puskesmas** (sesuai Asumsi Operasional PRD).
   - `midwife` hanya dapat mengakses dan mengelola ibu hamil yang ditugaskan padanya (`assigned_midwife_id === auth()->id()`).
   - `doctor` (termasuk Dokter Umum, Nutrisionis, Psikolog yang terverifikasi) secara sengaja memiliki akses supervisi **read-only ke seluruh ibu hamil aktif di Puskesmas** dengan NIK ter-mask 4 digit awal.

## Alternatives
1. **Login berbasis email**: Kurang ramah bagi ibu hamil di tingkat Puskesmas karena tidak semua memiliki email aktif.
2. **Membatasi Dokter hanya ke pasien rujukan / per wilayah**: Membutuhkan alur rujukan eksplisit dan skema tambahan yang berada di luar cakupan MVP Single-Puskesmas.

## Consequences
- **Positif**: Onboarding ibu hamil lebih mudah; verifikasi nakes mencegah pendaftaran medis palsu; risiko `SEC-RISK-001` terjawab jelas sebagai kebijakan resmi Single-Puskesmas.
- **Negatif / Kewajiban**: Jika di masa depan aplikasi berkembang menjadi Multi-Puskesmas, diperlukan penambahan `facility_id` / tenant scope dan pembaruan ADR ini. Perbaikan Wave 2 tetap diperlukan untuk konsistensi redirect guest admin (`RedirectIfAuthenticated`), redirect profil bidan (`midwife.profile.setting`), regenerasi session registrasi (`SEC-003`), dan penyetaraan password minimum (`SEC-014`).
