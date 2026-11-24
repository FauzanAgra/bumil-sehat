<div align="center">

<img src="https://api.dicebear.com/9.x/notionists/svg?seed=BumilSehat&backgroundColor=fda4af" width="120" height="120" alt="BumilSehat Logo" />

# BumilSehat

**Aplikasi Pemantauan Kehamilan Berbasis Puskesmas**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Filament](https://img.shields.io/badge/Filament-5-FDAE4B?style=flat-square)](https://filamentphp.com)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![PostgreSQL](https://img.shields.io/badge/PostgreSQL-Primary-4169E1?style=flat-square&logo=postgresql&logoColor=white)](https://www.postgresql.org)
[![Octane](https://img.shields.io/badge/Octane-FrankenPHP-EF4444?style=flat-square)](https://laravel.com/docs/octane)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](#-lisensi)

[Fitur](#-fitur-utama) · [Role](#-role-pengguna) · [Tech Stack](#-tech-stack) · [Instalasi](#-instalasi) · [Testing](#-pengujian--formatting) · [Dokumentasi](#-dokumentasi-blueprint)

</div>

---

## 📋 Tentang BumilSehat

**BumilSehat** adalah aplikasi web pemantauan kehamilan berbasis **Single-Puskesmas** di Indonesia yang mendigitalisasi pencatatan **Antenatal Care (ANC 1–6)** dari buku KIA konvensional secara terstruktur, aman, dan sesuai praktik lapangan.

### Prinsip Utama

- **Ibu Hamil (`mother`)** menginput data ANC saat kunjungan (didampingi Bidan), mencatat konsumsi harian TTD, membaca artikel sesuai usia kehamilan, dan menghubungi nakes via WhatsApp.
- **Bidan (`midwife`)** menjadwalkan ANC 1–6, menandai kunjungan terlewat (*missed*), memvalidasi kondisi risiko, mengunci data secara permanen, dan mengelola status TTD ibu binaannya.
- **Dokter / Nutrisionis / Psikolog (`doctor`)** melakukan supervisi klinis seluruh ibu hamil aktif di Puskesmas secara *read-only*.
- **Admin (`admin`)** mengelola verifikasi akun tenaga medis, pemetaan wilayah Kelurahan/RW, spesialisasi profesi, artikel edukasi, dan audit akses NIK melalui **Filament 5 Admin Panel**.
- **Sistem** mendeteksi indikator kondisi risiko secara otomatis (**KEK**, **Preeklampsia**, **Anemia**) sebagai alat bantu keputusan (*bukan diagnosis medis*).
- **Keamanan NIK (UU PDP)** disimpan dengan skema *dual-storage* (AES-256-CBC + HMAC-SHA256), *masking* per role, dan audit log *immutable*.

---

## ✨ Fitur Utama

| Fitur | Keterangan |
| --- | --- |
| **Penjadwalan ANC 1–6** | Bidan menjadwalkan, menjadwal ulang, dan menandai kunjungan terlewat (*missed*) |
| **Input Data ANC & USG** | Antropometri (BB, TB, LILA), tanda vital (TD), lab (Hb, TT), janin (letak, DJJ), dan unggah foto USG |
| **Deteksi Kondisi Risiko** | Auto-detect **KEK** (`LILA < 23.5 cm`), **Preeklampsia** (`TD >= 140/90`), dan **Anemia** (`Hb < 11.0 g/dL`) |
| **Validasi & Penguncian Permanen** | Bidan mengonfirmasi/menolak risiko dan mengunci catatan ANC (*irreversible lock* + `lockForUpdate`) |
| **Pemantauan TTD (Target 90 Hari)** | Aktivasi otomatis saat validasi ANC usia ≥ 10 minggu, toggle manual Bidan, & log konsumsi harian Ibu |
| **Artikel Edukasi & Konsultasi WA** | CMS artikel terfilter usia kehamilan (`min_week`–`max_week`) + direktori kontak WhatsApp nakes |
| **Keamanan NIK & Audit Log** | Enkripsi AES + HMAC-SHA256 (`NIKService`), *role-based masking*, dan tabel `nik_audit_logs` immutable |
| **Admin Panel (Filament 5)** | 11 resource manajemen master data, verifikasi nakes, penugasan wilayah Bidan, CMS, & audit NIK |

---

## 👥 Role Pengguna

| Role | Akses Utama | Verifikasi Admin |
| --- | --- | :-: |
| **Ibu Hamil (`mother`)** | Registrasi 3 step, input ANC + USG, catat TTD harian, baca artikel, konsultasi WA, profil | Otomatis aktif |
| **Bidan (`midwife`)** | Kelola ibu binaan wilayah RW, jadwal/missed ANC, validasi & lock ANC, toggle TTD, profil | Wajib |
| **Dokter (`doctor`)** | Supervisi *read-only* seluruh ibu aktif di Puskesmas, filter risiko, pencarian nama/NIK, profil | Wajib |
| **Admin (`admin`)** | Panel Filament 5 (`/admin`): verifikasi akun, wilayah, spesialisasi, artikel, pantauan klinis & audit NIK | Admin |

---

## 🛠 Tech Stack

- **Backend**: PHP `^8.2`, **Laravel 12**, Eloquent ORM
- **Runtime Server**: **Laravel Octane (`^2.20`) + FrankenPHP**
- **Admin Panel**: **Filament 5** (`/admin`, dilindungi `FilamentUser`)
- **Database**: **PostgreSQL** (development & production), **SQLite in-memory** (PHPUnit testing)
- **Frontend**: Blade SSR, **Tailwind CSS v4** (`@tailwindcss/vite`, `@tailwindcss/typography`), Vite 7, Font Awesome 6
- **Auth & Security**: Laravel Session Auth (login nomor HP, `throttle:10,1`), `EnsureRole`, AES-256-CBC + HMAC-SHA256 NIK

---

## 🚀 Instalasi

### Prasyarat

- PHP >= 8.2 (beserta ekstensi `pdo_pgsql`, `pdo_sqlite`, `mbstring`, `openssl`, `intl`, `fileinfo`)
- Composer
- Node.js >= 18 & npm
- PostgreSQL 15+

### Langkah Setup Lokal

```bash
# 1. Clone repository
git clone https://github.com/fauzanagra/bumilsehat.git
cd bumilsehat

# 2. Install dependensi PHP & JavaScript
composer install
npm install

# 3. Salin environment & generate APP_KEY
cp .env.example .env
php artisan key:generate

# 4. Generate NIK_HASH_SALT (WAJIB sebelum seeding data ibu)
php artisan tinker --execute="echo bin2hex(random_bytes(32));"
# Salin hasilnya ke NIK_HASH_SALT= di .env
```

Konfigurasikan koneksi **PostgreSQL** di `.env`:

```env
APP_NAME=BumilSehat
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=bumil_sehat
DB_USERNAME=postgres
DB_PASSWORD=your_password

NIK_HASH_SALT=your_64_char_hex_salt
SESSION_DRIVER=database
SESSION_ENCRYPT=true
QUEUE_CONNECTION=database
OCTANE_SERVER=frankenphp
```

> ⚠️ **KRITIS**: `APP_KEY` dan `NIK_HASH_SALT` **tidak boleh diubah** setelah data produksi tersimpan. Perubahan akan membuat pencarian `nik_hash` dan dekripsi `nik_encrypted` gagal.

Jalankan migrasi, seeder, storage link, dan build aset:

```bash
# 5. Migrasi & seeder (urutan seeder sudah diatur di DatabaseSeeder)
php artisan migrate --seed

# 6. Link public storage & build aset produksi
php artisan storage:link
npm run build
```

### Menjalankan Aplikasi

```bash
# Mode development lengkap (Laravel server + queue listener + Pail logs + Vite)
composer run dev

# Atau jalankan dengan Laravel Octane (FrankenPHP)
php artisan octane:start --server=frankenphp
```

- Aplikasi pengguna: `http://127.0.0.1:8000`
- Admin panel Filament: `http://127.0.0.1:8000/admin`

---

## 🌱 Akun Demo (Hasil Seeder)

Setelah `php artisan migrate --seed`, akun demo berikut tersedia (password seluruhnya `password`):

| Role | Nama | Nomor HP | Keterangan |
| --- | --- | --- | --- |
| **Admin** | Admin BumilSehat | `08000000000` | Email `/admin`: `admin@bumilsehat.com` |
| **Bidan 1** | Sari Dewi, A.Md.Keb | `08111111101` | Kel. Maju (RW 01–02) & Kel. Sehat (RW 01) |
| **Bidan 2** | Rina Pertiwi, S.Tr.Keb | `08111111102` | Kel. Maju (RW 03), Kel. Sehat (RW 02), Kel. Sejahtera (RW 01–03) |
| **Dokter Umum** | dr. Ahmad Fauzi | `08111111201` | Supervisi seluruh Puskesmas |
| **Nutrisionis** | Lestari Handayani, S.Gz | `08111111202` | Supervisi seluruh Puskesmas (`role = doctor`) |
| **Ibu Hamil 1–5** | Ani Rahayu – Eka Putri | `08222222201` – `08222222205` | Variasi usia kandungan Trimester 1–3 |

> Hapus atau ganti kredensial seluruh akun demo sebelum deploy ke production.

---

## 🧪 Pengujian & Formatting

```bash
# Jalankan seluruh automated tests (SQLite in-memory)
composer test

# Jalankan test spesifik
php artisan test --filter=ExampleTest

# Format kode PHP menggunakan Laravel Pint
vendor/bin/pint

# Build aset frontend produksi
npm run build
```

> Pastikan `NIK_HASH_SALT` tersedia di environment saat menjalankan test yang memanggil `NIKService`.

---

## 📖 Dokumentasi Blueprint

Seluruh dokumentasi arsitektur dan tata kelola proyek tersimpan di folder [`docs/`](docs/) dan [`AGENTS.md`](AGENTS.md):

| Dokumen | Isi |
| --- | --- |
| [`docs/PRD.md`](docs/PRD.md) | Product Requirements Document (kebutuhan, peran, rumus klinis, FR/NFR, AC, Wave) |
| [`docs/ERD.md`](docs/ERD.md) | Entity Relationship Document (Mermaid ERD 17 tabel, Data Dictionary, index, integritas) |
| [`docs/TECH_SPEC.md`](docs/TECH_SPEC.md) | Spesifikasi teknis lengkap (arsitektur, keamanan, Octane, transaksi, testing, deployment) |
| [`docs/DESIGN.md`](docs/DESIGN.md) | Design System (palet warna Rose/Blue, tipografi `Poppins`, pola komponen mobile-first) |
| [`docs/TASKS.md`](docs/TASKS.md) | Task Board resmi (progres Wave 1 MVP & backlog Wave 2 Stabilisasi/Hardening/Testing) |
| [`docs/adr/README.md`](docs/adr/README.md) | Architecture Decision Records (PostgreSQL, NIK UU PDP, RBAC, Octane & TTD, Specializations) |
| [`AGENTS.md`](AGENTS.md) | Aturan tata kelola lintas asisten AI (Antigravity, Claude Code, Codex, Cursor, dll.) |

---

## 📁 Struktur Proyek

```text
bumil-sehat/
├── app/
│   ├── Events/               # AncValidationCreated
│   ├── Exceptions/           # InvalidNIKException
│   ├── Filament/Resources/   # 11 Filament 5 Admin Resources
│   ├── Http/
│   │   ├── Controllers/      # Auth/, Mother/, Midwife/, Doctor/
│   │   ├── Middleware/       # EnsureRole, RedirectIfAuthenticated
│   │   └── Requests/         # Form Requests (StoreAncValidationRequest, dst.)
│   ├── Listeners/            # AutoActivateTtdOnValidation
│   ├── Models/               # 17 Eloquent domain models
│   ├── Providers/            # AppServiceProvider, AdminPanelProvider
│   ├── Services/             # NIKService
│   └── Traits/               # HasNIKEncryption
├── docs/                     # PRD, ERD, TECH_SPEC, DESIGN, TASKS, adr/
├── database/                 # 19 migrations & 5 seeders
├── resources/                # Tailwind CSS v4 (app.css), JS, & Blade views per role
├── routes/web.php            # Seluruh web routes per role
├── tests/                    # PHPUnit Unit & Feature tests
└── AGENTS.md                 # Panduan kontribusi & lintas-AI
```

---

## 🚢 Checklist Deployment Produksi

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`
- [ ] `APP_KEY` dan `NIK_HASH_SALT` sudah di-generate dan dicadangkan aman
- [ ] `SESSION_ENCRYPT=true`, `SESSION_SECURE_COOKIE=true`, `OCTANE_HTTPS=true`
- [ ] Reverse proxy HTTPS aktif & `trustProxies` dikonfigurasi terbatas pada proxy tepercaya
- [ ] `php artisan migrate --force`, `php artisan storage:link`, `npm run build`, `php artisan optimize`
- [ ] `php artisan octane:start --server=frankenphp` & `php artisan queue:work` berjalan via process manager
- [ ] Seluruh akun demo seeder dihapus atau diganti passwordnya

---

## 🤝 Panduan Kontribusi & AI Agents

Sebelum berkontribusi atau menjalankan asisten AI pada repositori ini, wajib membaca [`AGENTS.md`](AGENTS.md) dan [`docs/TASKS.md`](docs/TASKS.md). Gunakan format **Conventional Commits v1.2.0** (`<type>(<scope>): <subject>`) dengan commit atomik yang turut memperbarui status task di `docs/TASKS.md`.

---

## 📝 Lisensi

Distributed under the MIT License.
