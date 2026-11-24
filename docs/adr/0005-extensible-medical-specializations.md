# ADR-0005: Tabel Master Specializations Menggantikan Enum Profesi

- **Status**: Accepted
- **Date**: 2026-11-24

## Context
Pada rancangan awal aplikasi, jenis profesi tenaga kesehatan di `medical_profiles` sempat direncanakan sebagai enum `profession` yang hardcoded (`midwife`, `doctor`, `nutritionist`, dll.). Namun di lapangan, Puskesmas dapat melibatkan jenis pendamping kesehatan tambahan (seperti Psikolog, Konselor Laktasi, atau Instruktur Senam/Yoga Hamil) yang membutuhkan informasi tampilan tersendiri (ikon, warna kartu, sapaan WhatsApp default, dan urutan tampil).

## Decision
1. Menggantikan kolom enum profesi di `medical_profiles` dengan relasi Foreign Key `specialization_id` ke tabel master `specializations`.
2. Tabel `specializations` menyimpan:
   - `role_group` (`enum: midwife | doctor`) untuk memetakan profesi tersebut ke salah satu dari 2 level otorisasi medis sistem saat registrasi (`RegisterController::storeMedical()`).
   - Metadata presentasi (`icon`, `color`, `wa_greeting`, `sort_order`, `is_active`) yang langsung dipakai pada halaman registrasi tenaga medis dan direktori konsultasi `/mother/chat`.
3. Pengelolaan penuh `specializations` diserahkan ke Admin melalui Filament Resource `SpecializationResource`.

## Alternatives
1. **Enum hardcoded di migrasi `medical_profiles`**: Setiap penambahan profesi baru memaksa pembuatan migrasi baru, perubahan validasi, dan pembaruan view/seeder.
2. **Menambah role otorisasi baru per profesi di `users.role`**: Memecah terlalu banyak middleware dan dashboard padahal secara kewenangan klinis hanya ada dua pola operasional: penanggung jawab ANC (`midwife`) dan supervisi/konsultasi (`doctor`).

## Consequences
- **Positif**: Penambahan jenis tenaga kesehatan baru (mis. Guru Yoga atau Konselor Laktasi) cukup dilakukan lewat Filament Admin tanpa migrasi database maupun deploy ulang kode.
- **Negatif / Kewajiban**: `SpecializationSeeder` wajib dijalankan sebelum `MedicalStaffSeeder` (urutan seeder harus dipertahankan); penghapusan spesialisasi yang masih dipakai diblokir oleh `restrictOnDelete` pada `medical_profiles.specialization_id`.
