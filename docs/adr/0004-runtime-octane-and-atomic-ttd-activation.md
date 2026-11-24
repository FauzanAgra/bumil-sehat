# ADR-0004: Runtime Laravel Octane (FrankenPHP) & Aktivasi TTD Sinkron-Atomik

- **Status**: Accepted
- **Date**: 2026-11-24

## Context
Proyek telah mengintegrasikan `laravel/octane` bersama binary `frankenphp` dan `config/octane.php` untuk meningkatkan throughput permintaan. Di sisi lain, ditemukan dua masalah pada alur validasi ANC dan aktivasi otomatis TTD:
1. Terdapat file legacy `app/Providers/EventServiceProvider.php` yang tidak terdaftar di `bootstrap/providers.php`; padahal di Laravel 12 *event auto-discovery* untuk `app/Listeners` aktif secara bawaan, sehingga mendaftarkan `EventServiceProvider` justru berisiko membuat listener `AutoActivateTtdOnValidation` tereksekusi ganda.
2. Audit keamanan (`SEC-006`, `SEC-010`, `SEC-011`) menyoroti bahwa jika aktivasi TTD berjalan terpisah di luar batas transaksi validasi ANC (atau gagal di async queue), catatan ANC bisa sudah terkunci permanen tanpa TTD teraktifkan — dan target kehamilan harus berasal dari `ancRecord->schedule->pregnancy`.

## Decision
1. **Runtime Server**: Menetapkan **Laravel Octane + FrankenPHP** di belakang reverse proxy HTTPS sebagai runtime target produksi, dengan konfigurasi `trustProxies` yang aman di `bootstrap/app.php` dan `OCTANE_HTTPS=true` saat TLS diterminasi di proxy.
2. **Queue Driver**: Menggunakan `QUEUE_CONNECTION=database` dengan queue worker terpisah untuk pekerjaan background asinkron di masa mendatang.
3. **Aktivasi TTD Sinkron & Atomik**:
   - Mempertahankan pola event/listener `AncValidationCreated` → `AutoActivateTtdOnValidation` menggunakan *event auto-discovery* bawaan Laravel 12 (dan menghapus `EventServiceProvider` legacy agar tidak terjadi duplikasi pendaftaran listener).
   - Menjalankan aktivasi TTD **secara sinkron di dalam batas transaksi yang sama** dengan validasi penguncian ANC (tidak mengubah listener menjadi `ShouldQueue`), menggunakan `lockForUpdate` pada baris kehamilan milik `ancRecord->schedule->pregnancy` agar validasi ANC dan aktivasi TTD selalu konsisten secara atomik.

## Alternatives
1. **Queue Asinkron (`ShouldQueue` + `afterCommit`) untuk TTD**: Menambah kompleksitas retry, dead-letter monitoring, dan rekonsiliasi data untuk operasi kecil (1 query upsert `ttd_statuses`) yang kritis terhadap konsistensi klinis.
2. **Menghapus Event/Listener dan menaruh logika TTD langsung di Controller**: Melanggar arsitektur event-driven yang sudah terdokumentasi di `AGENTS.md` dan mengurangi keterpisahan tanggung jawab.

## Consequences
- **Positif**: Performa respons tinggi melalui worker FrankenPHP; tidak ada eksekusi ganda listener; tidak ada kondisi *split-brain* antara ANC terkunci dan status TTD; tidak bergantung pada ketersediaan queue daemon untuk fungsi klinis inti.
- **Negatif / Kewajiban**: Seluruh kode aplikasi harus tetap *Octane-safe* (tidak menyimpan state per-request di singleton/static); Wave 2 wajib memperbaiki resolusi kehamilan ke `schedule->pregnancy` (`SEC-006`) dan memasukkan dispatch/eksekusi ke transaksi atomik (`SEC-010`, `SEC-011`).
