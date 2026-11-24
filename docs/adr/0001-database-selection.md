# ADR-0001: Pemilihan PostgreSQL sebagai Database Utama & SQLite untuk Testing

- **Status**: Accepted
- **Date**: 2026-11-24

## Context
Selama fase awal pengembangan BumilSehat, terjadi inkonsistensi referensi database antar berkas:
- `.env` lokal menggunakan **PostgreSQL** (`pgsql`).
- `README.md` lama dan pengecekan kode error `1062` di `RegisterController.php` mengasumsikan **MySQL / MariaDB**.
- `.env.example` dan `phpunit.xml` mengacu pada **SQLite** (in-memory untuk test suite).

Ketidakkonsistenan ini menimbulkan risiko bug lintas-dialect (seperti gagal menangkap pelanggaran *unique constraint* pada PostgreSQL karena kode error PostgreSQL adalah `23505`, bukan `1062`) serta kebingungan saat onboarding pengembang atau berganti asisten AI.

## Decision
1. Menetapkan **PostgreSQL** sebagai DBMS resmi tunggal untuk environment **development** dan **production**.
2. Mempertahankan **SQLite in-memory (`:memory:`)** khusus untuk eksekusi cepat **PHPUnit** sesuai konfigurasi `phpunit.xml`.
3. Melarang pengecekan kode error spesifik vendor (seperti MySQL `1062`); seluruh penanganan pelanggaran uniqueness di kode Laravel wajib menggunakan exception agnostik framework (`Illuminate\Database\UniqueConstraintViolationException`).
4. Menyelaraskan `.env.example`, `README.md`, `TECH_SPEC.md`, dan `ERD.md` ke standar PostgreSQL.

## Alternatives
1. **MySQL / MariaDB sebagai standar utama**: Sudah familiar di shared hosting, namun `.env` aktif sudah menggunakan PostgreSQL dan PostgreSQL lebih kuat dalam dukungan *partial unique index*, transaksi ketat, serta integritas skema.
2. **Dual-DBMS resmi (PostgreSQL & MySQL setara)**: Menambah beban pengujian matriks database tanpa manfaat nyata untuk proyek Single-Puskesmas.

## Consequences
- **Positif**: Satu sumber kebenaran DBMS yang jelas; dukungan kuat untuk transaksi, locking, dan constraint lanjutan (termasuk potensi *partial unique index* pada `midwife_areas` di masa depan); pengujian PHPUnit tetap cepat dengan SQLite in-memory.
- **Negatif / Kewajiban**: Pengembang lokal perlu menjalankan layanan PostgreSQL untuk development manual; `RegisterController.php` harus diperbaiki di Wave 2 (`W2-004`) agar tidak lagi mengecek kode error MySQL `1062`; migrasi tetap harus dijaga agar kompatibel dengan SQLite saat menjalankan PHPUnit.
