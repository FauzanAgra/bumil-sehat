# Project Task Board — BumilSehat

Dokumen ini adalah **single source of truth** untuk pelacakan progres implementasi **BumilSehat**. Setiap perubahan status task wajib memperbarui ringkasan progres di bawah ini dan mengikuti aturan commit atomik di [`AGENTS.md`](../AGENTS.md).

## Progress Overview

- **Status Progress**: 25 / 43 Completed (58%)
- **Total Tasks**: 43
- **Completed (`[x]`)**: 25
- **In Progress (`[~]`)**: 0
- **Blocked (`[!]`)**: 0
- **Pending (`[ ]`)**: 18

> **Penanda Status**: `[ ]` Pending · `[~]` In Progress · `[x]` Completed · `[!]` Blocked

---

## Wave 1 — Core MVP (Existing Implementation)

### Module: Foundation, Schema & Master Data

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-001` | Inisialisasi proyek Laravel 12, Tailwind CSS v4, Vite 7, & konfigurasi dasar | High | - | Aplikasi boot bersih & asset ter-build via Vite | 2026-04-21 |
| [x] | `W1-002` | Buat 19 migrasi database dan 17 Eloquent Model domain + relasi, cast, & scope | High | `W1-001` | Seluruh tabel & model sesuai `docs/ERD.md` | 2026-05-02 |
| [x] | `W1-003` | Implementasikan seeder berurutan (`Specialization`, `Subdistrict`, `MedicalStaff`, `Mother`) | Medium | `W1-002`, `W1-004` | `DatabaseSeeder` berjalan deterministik dengan akun & wilayah demo | 2026-05-20 |

### Module: NIK Security & Audit (UU PDP)

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-004` | Bangun `NIKService`, `HasNIKEncryption`, `InvalidNIKException`, & `NikAuditLog` immutable | High | `W1-002` | NIK tersimpan AES + HMAC-SHA256, validasi 16 digit/provinsi, audit log blokir update/delete | 2026-05-18 |

### Module: Authentication & RBAC

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-005` | Implementasikan login berbasis Nomor HP (`throttle:10,1`), logout, & redirect per role | High | `W1-002` | Pengguna dapat login via `phone`, session diregenerasi, redirect sesuai role (`FR-001`) | 2026-05-04 |
| [x] | `W1-006` | Implementasikan registrasi Ibu Hamil 3 langkah (Akun → NIK & Wilayah → HPHT/HPL) | High | `W1-004`, `W1-005` | Registrasi menghasilkan User, `MotherProfile` + Bidan RW, & `Pregnancy` (`FR-002`) | 2026-05-18 |
| [x] | `W1-007` | Implementasikan registrasi Tenaga Medis + halaman `waiting-approval` | High | `W1-005` | Role otomatis dari `specializations.role_group`, status awal `is_verified = false` (`FR-003`) | 2026-05-06 |
| [x] | `W1-008` | Implementasikan middleware `EnsureRole` & `RedirectIfAuthenticated` | High | `W1-005` | Rute terisolasi per role & nakes belum verified diarahkan ke `/waiting-approval` (`FR-004`) | 2026-05-10 |

### Module: Mother Dashboard & Features

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-009` | Bangun Dashboard Ibu (usia janin, HPL, trimester, Bidan pendamping, alert risiko, widget TTD) | High | `W1-006`, `W1-008` | Dashboard memuat seluruh ringkasan kehamilan & tautan WA Bidan (`FR-005`) | 2026-05-04 |
| [x] | `W1-010` | Bangun riwayat, detail, form input ANC 1–6, unggah USG, & deteksi otomatis KEK/Preeklampsia/Anemia | High | `W1-009` | Ibu dapat input ANC & foto USG; kondisi risiko otomatis terdeteksi (`FR-006`, `FR-007`) | 2026-05-05 |
| [x] | `W1-011` | Bangun pencatatan konsumsi TTD harian (`TtdController`) & direktori konsultasi WhatsApp (`ChatController`) | Medium | `W1-009` | TTD tercatat unik per hari saat aktif & daftar nakes terverifikasi tampil di `/mother/chat` (`FR-008`, `FR-009`) | 2026-05-12 |
| [x] | `W1-012` | Bangun daftar & detail artikel edukasi terfilter usia kehamilan (`ArticleController`) | Medium | `W1-009` | Artikel `published` tersaring berdasarkan `min_week`–`max_week` + pencarian judul (`FR-010`) | 2026-05-05 |
| [x] | `W1-013` | Bangun manajemen profil Ibu (auto-reassign Bidan saat pindah RW) & ubah password | Medium | `W1-009` | Profil & password dapat diperbarui; pindah `area_id` otomatis reassign Bidan (`FR-011`) | 2026-05-06 |

### Module: Midwife Dashboard & Operations

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-014` | Bangun Dashboard Bidan, daftar pasien binaan (pencarian/filter), & detail timeline ANC 1–6 + TTD | High | `W1-008` | Bidan hanya melihat pasien wilayahnya dengan prioritas risiko & antrean validasi (`FR-012`, `FR-013`) | 2026-05-07 |
| [x] | `W1-015` | Bangun manajemen jadwal ANC (buat, ubah, tandai `missed` & `missed-direct`) | High | `W1-014` | Jadwal ANC 1–6 unik per kehamilan, mendukung reschedule & status `missed` (`FR-014`) | 2026-06-02 |
| [x] | `W1-016` | Bangun validasi & penguncian ANC (`lockForUpdate`), konfirmasi/tolak risiko, & auto-aktivasi TTD | High | `W1-010`, `W1-014` | Validasi & lock berjalan; auto-discovery listener Auto-TTD terverifikasi tunggal via `W2-001` (`FR-015`, `FR-016`) | 2026-11-24 |
| [x] | `W1-017` | Bangun toggle TTD manual & pengaturan profil/password Bidan | Medium | `W1-014` | UI, controller, & redirect `midwife.profile.setting` terverifikasi lulus lewat `W2-002` (`FR-016`, `FR-017`) | 2026-11-24 |

### Module: Doctor Supervision

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-018` | Bangun Dashboard supervisi Puskesmas, daftar pasien (search nama/NIK), detail ANC 1–6 + USG, & profil Dokter | High | `W1-008` | Dokter memantau seluruh ibu aktif (read-only), pencarian NIK exact via `byNik()`, & profil sendiri (`FR-018`–`FR-020`) | 2026-05-08 |

### Module: Admin Panel (Filament 5), Runtime & Blueprint

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W1-019` | Bangun 11 Filament 5 Resources & isolasi panel admin via `FilamentUser` (`SEC-001` fixed) | High | `W1-002`, `W1-004` | Semua resource aktif di `/admin` dan `User::canAccessPanel()` terhubung ke `FilamentUser` (`FR-021`–`FR-024`) | 2026-10-20 |
| [x] | `W1-020` | Integrasikan Laravel Octane + FrankenPHP (`config/octane.php`) & rapikan code style Pint | Medium | `W1-019` | `laravel/octane` terkonfigurasi & seluruh kode PHP diformat konsisten | 2026-10-20 |
| [x] | `W1-021` | Bangun Blueprint Dokumentasi (`docs/*`) & sinkronisasi Cross-AI Governance (`AGENTS.md`, `README.md`) | High | `W1-020` | `PRD`, `ERD`, `TECH_SPEC`, `TASKS`, `DESIGN`, `adr/`, `AGENTS.md`, dan `README.md` konsisten 100% dengan kode aktual | 2026-11-24 |

---

## Wave 2 — Stabilization, Security Hardening & Automated Testing

### Module: Functional Bug Fixes

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [x] | `W2-001` | Verifikasi event auto-discovery `AutoActivateTtdOnValidation`, hapus `EventServiceProvider` legacy (cegah duplikasi), & tambah test | High | `W1-016` | `php artisan event:list --event=AncValidationCreated` & `AncValidationEventDiscoveryTest` membuktikan listener terdaftar tepat 1x; `W1-016` selesai `[x]` | 2026-11-24 |
| [x] | `W2-002` | Perbaiki redirect `midwife.setting` → `midwife.profile.setting` di `Midwife\ProfileController` | High | `W1-017` | Update profil & password Bidan sukses redirect ke halaman setting tanpa `RouteNotFoundException`; `W1-017` selesai `[x]` | 2026-11-24 |
| [x] | `W2-003` | Perbaiki redirect admin `route('admin.dashboard')` → `url('/admin')` di `RedirectIfAuthenticated` | High | `W1-008` | Admin yang sudah login dan mengakses `/login` dialihkan bersih ke `/admin` tanpa error route | 2026-11-24 |
| [x] | `W2-004` | Ganti penanganan error duplikat MySQL `1062` → `UniqueConstraintViolationException` di `RegisterController` | High | `W1-006` | Pelanggaran duplikasi NIK/phone tertangani bersih di PostgreSQL maupun SQLite (`ADR-0001`) | 2026-11-24 |

### Module: Security Hardening

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [ ] | `W2-005` | Hilangkan penyimpanan NIK plaintext di session registrasi & hapus `withInput()` untuk field NIK (`SEC-002`) | High | `W1-006` | Session registrasi hanya menyimpan NIK terenkripsi dan error duplikasi tidak mem-flash NIK mentah | - |
| [ ] | `W2-006` | Tambahkan regenerasi session ID setelah `Auth::login()` pada registrasi Ibu & Tenaga Medis (`SEC-003`) | High | `W2-005` | Session ID berganti segera setelah registrasi sukses dan key `reg_mother` terhapus bersih | - |
| [ ] | `W2-007` | Bungkus `Mother\AncController@store` dalam transaksi + `lockForUpdate` & guarded update `is_locked = false` (`SEC-004`) | High | `W1-010` | Penulisan ANC Ibu tidak bisa menimpa catatan yang sedang/sudah dikunci Bidan | - |
| [ ] | `W2-008` | Batasi submit ANC hanya untuk jadwal berstatus `scheduled` pada `activePregnancy` & tandai `done` secara atomik (`SEC-005`) | High | `W2-007` | Submit pada jadwal `cancelled`, `missed`, `done`, atau kehamilan non-aktif ditolak tanpa memutasi jadwal | - |
| [ ] | `W2-009` | Tegakkan validasi server-side unggahan `usg_image` wajib pada ANC ke-1 & ke-5 jika belum ada foto USG (`SEC-012`) | Medium | `W2-008` | Request langsung tanpa `usg_image` pada ANC 1 & 5 (tanpa USG lama) ditolak dengan 422 validation error | - |
| [ ] | `W2-010` | Arahkan resolusi kehamilan pada `AutoActivateTtdOnValidation` ke `ancRecord->schedule->pregnancy` & jalankan atomik di transaksi validasi (`SEC-006`, `SEC-010`) | High | `W2-001` | Validasi ANC historis tidak mengaktifkan TTD kehamilan lain; validasi & TTD komit/rollback atomik (`ADR-0004`) | - |
| [ ] | `W2-011` | Lindungi pembuatan & toggle manual `TtdStatus` dengan transaksi + `lockForUpdate` pada baris `Pregnancy` (`SEC-011`) | Medium | `W2-010` | Request konkuren aktivasi/toggle TTD tidak memicu unique-key exception atau *lost update* | - |
| [ ] | `W2-012` | Ganti seluruh pemanggilan langsung `NIKService::mask()` di Blade & Filament dengan `MotherProfile::getMaskedNik()` (`SEC-007`) | Medium | `W1-004` | Seluruh tampilan NIK (profil Ibu & tabel/infolist Filament) otomatis tercatat di `nik_audit_logs` | - |
| [ ] | `W2-013` | Validasi server-side relasi `areas.subdistrict_id === subdistrict_id` pada registrasi & edit profil Ibu (`SEC-008`) | Medium | `W1-006`, `W1-013` | Request dengan `area_id` yang tidak berada di bawah `subdistrict_id` ditolak dengan validation error | - |
| [ ] | `W2-014` | Bungkus pembuatan `User`, `MotherProfile`, & `Pregnancy` di `RegisterController@storeHpht` dalam `DB::transaction()` (`SEC-009`) | Medium | `W2-004`, `W2-005` | Kegagalan di tengah proses registrasi me-rollback seluruh tabel tanpa meninggalkan akun yatim (*orphaned*) | - |
| [ ] | `W2-015` | Seragamkan kebijakan password minimum (8 karakter) di semua role & selaraskan `.env.example` / `trustProxies` aman produksi (`SEC-013`, `SEC-014`) | Medium | `W1-020` | Semua registrasi & ganti password memakai aturan password terpusat; `.env.example` & `bootstrap/app.php` aman produksi | - |
| [ ] | `W2-016` | Turunkan `anc_records.mother_id` langsung dari `schedule->pregnancy->mother_id` & ketatkan mass-assignment (`SEC-015`) | Low | `W2-007` | Tidak ada celah ketidaksinkronan antara `schedule_id` dan `mother_id` pada `anc_records` | - |
| [ ] | `W2-017` | Tambahkan sanitasi HTML allowlist untuk konten rich text artikel & audit dependensi (`SEC-RISK-002`, `SEC-RISK-003`) | Medium | `W1-012`, `W1-019` | Payload XSS pada konten artikel dibersihkan/diblokir; hasil `composer audit` & `npm audit` terdokumentasi bersih | - |

### Module: Automated Test Suite (`SEC-016`)

| Status | ID | Task | Priority | Dependencies | Acceptance Criteria | Completed Date |
| :-: | --- | --- | --- | --- | --- | :-: |
| [ ] | `W2-018` | Perbaiki `tests/Feature/ExampleTest.php` (`GET /` → redirect 302 `login`) & siapkan base test helper + `NIK_HASH_SALT` testing | High | `W2-001` | `composer test` hijau 100% sebagai baseline testing | - |
| [ ] | `W2-019` | Tambahkan Unit Tests: `NIKService`, `HasNIKEncryption`, `NikAuditLog` immutability, kalkulasi `Pregnancy`, & deteksi risiko `AncRecord` | High | `W2-018` | Seluruh logika enkripsi, masking, HPL, trimester, BMI, KEK/Preeklampsia/Anemia lulus uji | - |
| [ ] | `W2-020` | Tambahkan Feature Tests: Isolasi `/admin`, matriks role `EnsureRole`, redirect admin/nakes, & keamanan registrasi (`SEC-002`, `SEC-003`, `SEC-008`, `SEC-009`, `SEC-014`) | High | `W2-003`, `W2-006`, `W2-013`, `W2-014`, `W2-015`, `W2-018` | Semua skenario otorisasi panel/role & integritas registrasi terverifikasi otomatis | - |
| [ ] | `W2-021` | Tambahkan Feature Tests: IDOR lintas role, penjadwalan & `missed` ANC, jadwal eligible (`SEC-005`), USG wajib ANC 1 & 5 (`SEC-012`), audit NIK (`SEC-007`), & XSS artikel (`SEC-RISK-002`) | High | `W2-008`, `W2-009`, `W2-012`, `W2-017`, `W2-018` | Seluruh skenario IDOR, validasi ANC, audit log NIK, dan sanitasi artikel lulus uji | - |
| [ ] | `W2-022` | Tambahkan Feature Tests: Validasi & penguncian ANC, konkurensi simpan vs lock (`SEC-004`), Auto-TTD (`< 10w`, `>= 10w`, historis `SEC-006`/`SEC-010`), toggle TTD (`SEC-011`), & profil Bidan (`W2-002`) | High | `W2-002`, `W2-007`, `W2-010`, `W2-011`, `W2-018` | Seluruh alur validasi, penguncian permanen, dan TTD lulus uji tanpa regresi | - |
