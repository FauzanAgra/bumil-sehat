# AGENTS.md

Panduan tata kelola repositori untuk semua asisten AI coding (Antigravity, Claude Code, Codex, Cursor, Copilot, Windsurf, Aider, dll.) dan kontributor di proyek **BumilSehat**.

---

## 1. Role, Objective & Project Context

Act as a pragmatic software engineer. Prioritize correctness, security, maintainability, readability, and consistency with the existing codebase and approved blueprint.

Deliver the smallest complete solution that satisfies the approved requirements. Avoid unnecessary complexity, speculative abstractions, and unrelated changes. Treat security, data integrity, and verification as essential parts of implementation correctness.

### Project Purpose
**BumilSehat** adalah aplikasi web pemantauan kehamilan berbasis **Single-Puskesmas** yang mendigitalisasi pencatatan Antenatal Care (ANC 1–6), deteksi dini faktor risiko (KEK, Preeklampsia, Anemia), pemantauan konsumsi Tablet Tambah Darah (TTD), edukasi artikel kehamilan, dan perlindungan NIK sesuai **UU PDP**.

### Required Blueprint Reading List
Sebelum merancang perubahan, menulis fitur, atau memperbaiki bug, baca dokumen blueprint relevan yang menjadi *single source of truth*:

| Dokumen | Fungsi |
| --- | --- |
| [`docs/PRD.md`](docs/PRD.md) | Kebutuhan produk, aturan bisnis, rumus klinis, hak akses peran, & acceptance criteria |
| [`docs/ERD.md`](docs/ERD.md) | Skema 17 tabel domain, data dictionary, relasi, index, & aturan integritas data |
| [`docs/TECH_SPEC.md`](docs/TECH_SPEC.md) | Arsitektur sistem, stack resmi, keamanan, transaksi, testing, & deployment |
| [`docs/DESIGN.md`](docs/DESIGN.md) | Bahasa visual, palet warna peran (Rose/Blue), tipografi `Poppins`, & pola komponen |
| [`docs/TASKS.md`](docs/TASKS.md) | Papan tugas resmi (Wave 1 & Wave 2) dan status progres aktual |
| [`docs/adr/README.md`](docs/adr/README.md) | Catatan keputusan arsitektur (ADR-0001 s.d. ADR-0005+) |

> **Catatan Audit Lokal**: Jika tersedia di lingkungan lokal, [`docs/temp/security-audit-issues.md`](docs/temp/security-audit-issues.md) (berada di folder lokal `/docs/temp` ter-gitignore) memuat detail teknis temuan keamanan yang dipetakan ke Wave 2 di `docs/TASKS.md`.

---

## 2. Tech Stack Integrity & Entry Points

### Approved Stack
- **Backend**: PHP `^8.2`, **Laravel 12** (`laravel/framework: ^12.0`), **Filament 5** (`filament/filament: ^5.0`) di `/admin`.
- **Runtime**: **Laravel Octane (`^2.20`) + FrankenPHP** di belakang reverse proxy HTTPS ([ADR-0004](docs/adr/0004-runtime-octane-and-atomic-ttd-activation.md)).
- **Database**: **PostgreSQL** untuk development & production; **SQLite in-memory** khusus untuk PHPUnit ([ADR-0001](docs/adr/0001-database-selection.md)).
- **Frontend**: Blade SSR, **Tailwind CSS v4** (`@tailwindcss/vite`), `@tailwindcss/typography`, Vite 7, Axios, Font Awesome 6.
- **Tooling**: Laravel Pint (`vendor/bin/pint`), PHPUnit 11 (`composer test`).

> **Stack Guardrail**: Jangan mengganti framework, menaikkan major version, mengganti DBMS target, atau memasang/menghapus dependensi Composer/npm tanpa persetujuan eksplisit pengguna dan pembaruan `docs/TECH_SPEC.md` (+ ADR bila relevan).

### Key Entry Points
- Browser routes dan alur per peran terpusat di `routes/web.php`; middleware aliases (`role`, custom `guest`) terdaftar di `bootstrap/app.php`; service provider terdaftar di `bootstrap/providers.php`.
- User-facing controllers & views dipisah per role di `app/Http/Controllers/{Auth,Mother,Midwife,Doctor}` dan `resources/views/{auth,dashboard/{mother,midwife,doctor}}`.
- Admin CRUD & view di-auto-discover dari `app/Filament/Resources` oleh `app/Providers/Filament/AdminPanelProvider.php` dan dibatasi lewat `User::canAccessPanel()` (`FilamentUser`).
- Frontend build inputs hanya `resources/css/app.css` dan `resources/js/app.js`; pemindaian `@source` Tailwind dikonfigurasi di `resources/css/app.css`.

Always verify the actual implementation and installed dependencies when a task depends on details that may have changed.

---

## 3. Database Schema Compliance

- Ikuti [`docs/ERD.md`](docs/ERD.md) secara ketat sebagai acuan skema database.
- Jangan mengarang tabel, kolom, enum, atau relasi baru tanpa dasar kebutuhan di `docs/PRD.md` dan persetujuan pengguna.
- Setiap perubahan skema wajib melalui migrasi Laravel yang kompatibel dengan **PostgreSQL** (runtime utama) sekaligus **SQLite** (PHPUnit), menjaga *referential integrity* (`CASCADE`/`RESTRICT`/`SET NULL`), serta memperbarui `docs/ERD.md` terlebih dahulu atau bersamaan setelah disetujui.
- Gunakan `Illuminate\Database\UniqueConstraintViolationException` untuk menangani pelanggaran *unique constraint* lintas DBMS; hindari pengecekan kode error spesifik vendor (seperti MySQL `1062`).
- Jangan menjalankan operasi database destruktif tanpa otorisasi eksplisit.

---

## 4. Understand the Project First, Planning & Scope Guardrails

Before making changes:
- Baca `AGENTS.md`, cek task relevan di `docs/TASKS.md`, dan baca bagian terkait dari `docs/PRD.md`, `docs/ERD.md`, `docs/TECH_SPEC.md`, atau `docs/DESIGN.md`.
- Periksa file terkait, dependensi, efek samping, implikasi keamanan, dan risiko regresi. Jangan membaca seluruh repositori tanpa kebutuhan.
- Tanyakan klarifikasi jika ambiguitas dapat mengubah implementasi secara material.

### Scope Guardrails
- Implementasikan **hanya** kebutuhan yang sudah disetujui di `docs/PRD.md` / `docs/TASKS.md` atau diminta eksplisit oleh pengguna.
- Jangan menambahkan fitur spekulatif di luar scope (lihat `Out of Scope` di `docs/PRD.md`) dan jangan me-refactor kode yang tidak terkait kecuali diperlukan demi keamanan/kebenaran perbaikan.
- Untuk tugas kompleks atau lintas modul: ringkas kebutuhan → identifikasi file/dampak → pertimbangkan edge case & keamanan → susun rencana ringkas → eksekusi bertahap → verifikasi penuh.

---

## 5. Architecture, Service Layer & Coding Standards

- Pertahankan arsitektur role-scoped controllers yang ringkas (*skinny controllers*): gunakan **Form Request** untuk validasi kompleks + otorisasi, manfaatkan service terfokus (`app/Services/NIKService.php`, dst.) untuk logika domain/kriptografi/perhitungan kompleks, dan gunakan `DB::transaction()` + `lockForUpdate()` untuk mutasi multi-langkah atau rawan *race condition*.
- Hindari membuat lapisan repository, interface, trait, atau service abstrak hanya demi pola (*pattern-for-pattern's sake*).
- Jaga kode tetap **Octane-safe**: jangan menyimpan mutable request/user state pada properti statis atau singleton container.
- Ikuti idiom PHP 8.2+ & Laravel 12 yang konsisten dengan codebase, gunakan nama simbol yang bermakna, tangani error secara eksplisit tanpa menelan exception diam-diam, dan tambahkan komentar hanya untuk menjelaskan keputusan non-obvious.
- Jaga kompatibilitas ke belakang kecuali perubahan pemecah (*breaking change*) diminta eksplisit.

---

## 6. BumilSehat Domain Constraints

Aturan domain berikut bersifat wajib dan tidak boleh dilanggar kecuali ada perubahan domain yang disetujui eksplisit beserta pembaruan PRD/ADR:

### NIK Protection (UU PDP — [ADR-0002](docs/adr/0002-nik-encryption-and-audit-trail.md))
- NIK tidak boleh pernah disimpan atau di-query dalam bentuk plaintext (baik di DB, session, old input, maupun log).
- Gunakan `NIKService`, `HasNIKEncryption`, dan `MotherProfile::byNik()` untuk seluruh penanganan NIK.
- Kolom tersimpan adalah `nik_encrypted` (AES-256-CBC) dan `nik_hash` (HMAC-SHA256); keduanya tetap berada di `$hidden`.
- Gunakan helper model `getMaskedNik()` / `getDecryptedNik()` di seluruh jalur tampilan (Blade & Filament) agar audit log `nik_audit_logs` dan otorisasi role selalu berjalan.
- `NIK_HASH_SALT` dan `APP_KEY` wajib dipertahankan permanen setelah data ada; mengubahnya memutus pencarian dan dekripsi NIK existing.
- `NikAuditLog` bersifat *immutable* (menolak update, delete, dan drop table di production).

### Role Authorization ([ADR-0003](docs/adr/0003-role-authorization-and-authentication.md))
- Tenaga medis (`midwife`, `doctor`) wajib terverifikasi (`is_verified = true`) pada `EnsureRole`; peran `mother` tidak memerlukan verifikasi manual.
- Akses `/admin` (Filament 5) wajib terbatas pada `role === 'admin'` melalui kontrak `FilamentUser::canAccessPanel()`.
- Pertahankan middleware role dan pemeriksaan kepemilikan (*ownership* / anti-IDOR) di setiap layar & endpoint baru. Jangan pernah mematikan kontrol otorisasi demi memudahkan testing.
- Cakupan supervisi `doctor` adalah *read-only* Single-Puskesmas (seluruh ibu dengan kehamilan aktif, NIK ter-mask 4 digit).

### ANC Validation, Risk Detection & Atomic TTD Activation ([ADR-0004](docs/adr/0004-runtime-octane-and-atomic-ttd-activation.md))
- Ambang deteksi risiko otomatis pada `AncRecord`: **KEK** (`lila < 23.5` cm), **Preeklampsia** (`systolic >= 140` atau `diastolic >= 90` mmHg), **Anemia** (`hb < 11.0` g/dL). Kondisi `confirmed`/`rejected` oleh Bidan bersifat final dan tidak boleh di-override oleh deteksi ulang.
- Catatan ANC yang sudah dikunci (`is_locked = true`) bersifat permanen (tidak dapat diubah/dihapus).
- Validasi ANC men-dispatch `AncValidationCreated`, yang listener-nya (`AutoActivateTtdOnValidation`) mengaktifkan TTD secara sinkron & atomik untuk kehamilan terkait (`ancRecord->schedule->pregnancy`) yang aktif pada usia kehamilan ≥ 10 minggu.
- Pertahankan alur event/listener tersebut; jangan menduplikasi logika aktivasi TTD di controller lain.

### Database Seeding
- Urutan seeder di `DatabaseSeeder` bersifat deterministik dan wajib dijaga: Admin → `SpecializationSeeder` & `SubdistrictSeeder` → `MedicalStaffSeeder` → `MotherSeeder`.
- `NIK_HASH_SALT` wajib terisi sebelum menjalankan `MotherSeeder`.

---

## 7. Security and Data Integrity

Treat security as a core requirement, not an optional polish:
- Validasi seluruh input tidak tepercaya di batas aplikasi (termasuk relasi antar-entitas seperti `area_id` terhadap `subdistrict_id`, kelayakan jadwal ANC, dan aturan USG wajib ANC 1 & 5).
- Tegakkan autentikasi, regenerasi session ID pasca login/registrasi, otorisasi role, dan pencegahan IDOR pada seluruh operasi terlindungi.
- Gunakan query berparameter / Eloquent ORM aman; hindari raw SQL tanpa binding dan batasi mass-assignment (`$fillable`).
- Jangan pernah mengekspose secret, kredensial, token, atau NIK plaintext pada log, pesan error, session unencrypted, atau respons tidak berwenang.
- Terapkan output encoding / sanitasi HTML allowlist untuk konten rich text (seperti artikel) untuk mencegah Stored XSS.
- Gunakan transaksi database dan *pessimistic locking* (`lockForUpdate`) untuk mencegah *race condition*, pengiriman ganda, dan penulisan parsial.

---

## 8. Frontend, Design System & UX

- Ikuti token warna, tipografi `Poppins`, layout mobile-first `max-w-md` (tema Rose untuk Mother/Midwife/Admin, Blue untuk Doctor), dan pola komponen di [`docs/DESIGN.md`](docs/DESIGN.md).
- Gunakan HTML semantik, jaga aksesibilitas keyboard (`focus:ring-*`) dan `aria-label` untuk tombol ikon, serta sediakan state *loading*, *empty*, *error*, dan *success* yang jelas.
- Hindari dependensi UI baru yang tidak perlu dan animasi berlebihan.
- Pastikan seluruh perubahan frontend tetap kompatibel dengan pipeline build Vite (`npm run build`).

---

## 9. Task Board Maintenance & Documentation Synchronization

### Pemeliharaan `docs/TASKS.md`
- Selalu baca [`docs/TASKS.md`](docs/TASKS.md) sebelum mulai mengeksekusi task Wave 2 atau fitur baru.
- Ubah status task ke `[~]` saat mulai dikerjakan, dan ke `[x]` beserta tanggal penyelesaian (`YYYY-MM-DD`) **hanya setelah** kode dan verifikasi/test benar-benar lulus.
- Setiap kali status task berubah, **hitung ulang dan perbarui** seluruh angka pada seksi `Progress Overview` (Completed, In Progress, Blocked, Pending, dan persentase).

### Documentation Synchronization
- Setiap perubahan arsitektur, skema database, kebutuhan bisnis, atau token desain yang disetujui wajib disinkronkan ke seluruh dokumen blueprint terdampak (`docs/PRD.md`, `docs/ERD.md`, `docs/TECH_SPEC.md`, `docs/DESIGN.md`, `docs/TASKS.md`, `docs/adr/`, `README.md`) tanpa menyentuh file yang tidak relevan.

---

## 10. Commands, Testing & Verification

### Development & Build Commands
- Setup awal (jangan jalankan membabi-buta di environment existing): `composer run setup`.
- Dev stack lokal: `composer run dev` (server, queue listener, Pail logs, Vite) atau Octane `php artisan octane:start --server=frankenphp`.
- Asset dev / build produksi: `npm run dev` / `npm run build`.

### Formatting & Testing
- Format PHP: `vendor/bin/pint`.
- Jalankan seluruh test: `composer test` (membersihkan config cache lebih dulu) atau `php artisan test`.
- Fokus test tertentu: `php artisan test --filter=TestName` atau `php artisan test tests/Feature/SomeTest.php`.
- Catatan: PHPUnit memakai SQLite in-memory, array cache/session, dan queue `sync`; test yang memakai `NIKService` tetap membutuhkan `NIK_HASH_SALT` non-empty.

### Verification Rules
- Tambahkan atau perbarui automated tests (Unit & Feature) untuk setiap fitur baru, perbaikan bug, batas otorisasi, dan perbaikan keamanan; jangan pernah menghapus atau melemahkan test yang gagal agar tampak lulus.
- Untuk perubahan PHP: jalankan `vendor/bin/pint` diikuti test relevan. Untuk perubahan frontend: jalankan `npm run build`.
- Jangan pernah melaporkan perintah sebagai berhasil kecuali benar-benar dijalankan dan lulus; jika ada yang belum dapat diverifikasi, jelaskan alasannya secara jujur.

---

## 11. Git & Conventional Commits Standards

- Periksa `git status` dan working tree sebelum melakukan perubahan; jaga perubahan pengguna yang belum di-commit dan jangan pernah me-revert perubahan yang tidak terkait.
- Jangan membuat commit, push, deploy, atau mengubah riwayat Git kecuali diminta eksplisit oleh pengguna.
- Hormati `.gitignore`: jangan pernah meng-commit `.env`, `public/build`, `vendor`, `node_modules`, binary `frankenphp`, folder lokal `/docs/temp`, atau `.agents`.
- Saat membuat commit (atas permintaan pengguna), gunakan **Conventional Commits v1.2.0** dengan format scoped:
  - Format: `<type>(<scope>): <subject>` (imperative mood, lowercase subject, tanpa titik akhir, maks 72 karakter, tanpa ticket ID di subject).
  - Contoh:
    - `fix(ttd): register EventServiceProvider for auto TTD activation`
    - `fix(security): encrypt NIK before storing in registration session`
    - `test(anc): add concurrency and schedule eligibility feature tests`
    - `docs(blueprint): add PRD, ERD, TECH_SPEC, TASKS, DESIGN, and ADRs`
    - `chore(deps): run composer and npm security audit`
  - **Atomic Commits**: Setiap commit fokus pada satu task logis dan menyertakan pembaruan status pada `docs/TASKS.md` dalam commit yang sama.

---

## 12. Completion Report

Saat menyelesaikan tugas, berikan laporan singkat yang memuat:
1. Apa yang diimplementasikan atau diperbaiki.
2. File atau komponen penting yang berubah.
3. Perintah test, format, dan build yang benar-benar dijalankan beserta hasil aktualnya.
4. Isu yang belum selesai, batasan, atau risiko tersisa (bedakan tegas antara hasil terverifikasi dan hal yang belum diverifikasi).
