<div align="center">

<img src="https://api.dicebear.com/9.x/notionists/svg?seed=BumilSehat&backgroundColor=fda4af" width="120" height="120" alt="BumilSehat Logo" />

# BumilSehat

**Aplikasi Pemantauan Kehamilan Berbasis Puskesmas**

[![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20?style=flat-square&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Tailwind CSS](https://img.shields.io/badge/Tailwind_CSS-v4-06B6D4?style=flat-square&logo=tailwindcss&logoColor=white)](https://tailwindcss.com)
[![Filament](https://img.shields.io/badge/Filament-v3-FDAE4B?style=flat-square)](https://filamentphp.com)
[![License](https://img.shields.io/badge/License-MIT-green?style=flat-square)](LICENSE)

[Fitur](#-fitur-utama) · [Instalasi](#-instalasi) · [Dokumentasi](#-dokumentasi) · [Role](#-role-pengguna) · [Screenshot](#-screenshot)

</div>

---

## 📋 Tentang BumilSehat

BumilSehat adalah aplikasi web pemantauan kehamilan yang dirancang khusus untuk **Puskesmas** di Indonesia. Aplikasi ini mendigitalisasi pencatatan Antenatal Care (ANC) yang sebelumnya dilakukan secara manual melalui buku KIA.

### Prinsip Utama

- 🤱 **Ibu Hamil** menginput data ANC secara mandiri saat kunjungan
- 👩‍⚕️ **Bidan** menentukan jadwal, memvalidasi, dan mengunci data
- 🩺 **Dokter** melakukan supervisi dan memberikan rekomendasi
- 🤖 **Sistem** mendeteksi kondisi risiko secara otomatis (bukan diagnosis)
- 🔐 **NIK** disimpan terenkripsi penuh (AES + HMAC-SHA256)

---

## ✨ Fitur Utama

| Fitur | Keterangan |
|-------|-----------|
| 📅 Penjadwalan ANC | Bidan membuat jadwal ANC ke-1 s/d ke-6 |
| 📝 Input Data ANC | Form lengkap: antropometri, vital sign, lab, USG |
| ⚠️ Deteksi Kondisi Risiko | Auto-detect KEK, Preeklampsia, Anemia |
| ✅ Validasi & Penguncian | Bidan validasi + kunci data (irreversible) |
| 💊 Pemantauan TTD | Tracking konsumsi Tablet Tambah Darah harian |
| 📰 Artikel Edukasi | CMS artikel kehamilan dengan filter usia kandungan |
| 🔒 Keamanan NIK | Enkripsi AES + hash HMAC-SHA256, tidak pernah plaintext |
| 🏥 Admin Panel | Filament-based: kelola user, wilayah, artikel, spesialisasi |

---

## 👥 Role Pengguna

```
┌─────────────┬──────────────────────────────────────────────────┐
│ Role        │ Akses                                            │
├─────────────┼──────────────────────────────────────────────────┤
│ Ibu Hamil   │ Input ANC, lihat jadwal, TTD, baca artikel       │
│ Bidan       │ Jadwal ANC, validasi, kunci data, pantau TTD     │
│ Dokter      │ Review data, supervisi, rekomendasi              │
│ Admin       │ Verifikasi akun, mapping wilayah, CMS artikel    │
└─────────────┴──────────────────────────────────────────────────┘
```

---

## 🛠 Tech Stack

- **Backend**: Laravel 11, PHP 8.2+, Eloquent ORM
- **Frontend**: Blade, Tailwind CSS v4, Font Awesome 6
- **Admin Panel**: Filament v3
- **Database**: MySQL / MariaDB
- **Auth**: Laravel session-based (custom, login via nomor HP)
- **Storage**: Laravel Storage (public disk)
- **Security**: AES encryption + HMAC-SHA256 untuk NIK

---

## 🚀 Instalasi

### Prasyarat

- PHP >= 8.2
- Composer
- Node.js >= 18 & npm
- MySQL / MariaDB

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/fauzanagra/bumilsehat.git
cd bumilsehat

# 2. Install PHP dependencies
composer install

# 3. Install JS dependencies
npm install

# 4. Konfigurasi environment
cp .env.example .env
php artisan key:generate

# 5. Konfigurasi database di .env
DB_DATABASE=bumilsehat
DB_USERNAME=root
DB_PASSWORD=

# 6. Generate NIK hash salt (WAJIB — jangan skip)
php artisan tinker --execute="echo bin2hex(random_bytes(32));"
# Salin output ke NIK_HASH_SALT= di .env

# 7. Jalankan migrasi & seeder
php artisan migrate --seed

# 8. Build assets
npm run build

# 9. Link storage
php artisan storage:link

# 10. Jalankan server
php artisan serve
```

Akses aplikasi di `http://localhost:8000`  
Akses admin panel di `http://localhost:8000/admin`

---

## ⚙️ Konfigurasi Environment

Variabel penting di `.env`:

```env
APP_NAME=BumilSehat
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_DATABASE=bumilsehat

# KRITIS — Generate sekali, JANGAN pernah diubah setelah data production masuk
NIK_HASH_SALT=your-generated-salt-here

# Storage
FILESYSTEM_DISK=public

# Queue (gunakan 'database' atau 'redis' di production)
QUEUE_CONNECTION=sync
```

> ⚠️ **Penting**: `NIK_HASH_SALT` **tidak boleh diubah** setelah data production ada. Perubahan akan membuat seluruh hash NIK existing tidak valid.

---

## 🌱 Seeder & Akun Demo

Setelah `php artisan migrate --seed`, akun berikut tersedia:

| Role | Nomor HP | Password |
|------|----------|----------|
| Admin | `08100000000` | `password` |
| Bidan | `08100000001` | `password` |
| Dokter | `08100000002` | `password` |
| Ibu Hamil | `08100000003` | `password` |

> Akun demo hanya untuk environment **development/staging**. Hapus atau ganti password sebelum deploy ke production.

---

## 📁 Struktur Folder Utama

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/          # Login, Register (multi-step)
│   │   ├── Mother/        # Dashboard, ANC, TTD, Artikel, Profil
│   │   ├── Midwife/       # Dashboard, Pasien, Jadwal, Validasi
│   │   └── Doctor/        # Dashboard, Pasien, Profil
│   └── Middleware/
│       └── EnsureRole.php # Role-based access control
├── Models/                # Eloquent models
├── Services/
│   └── NIKService.php     # Enkripsi, hash, masking NIK
├── Events/                # Domain events
├── Listeners/             # Event listeners
└── Filament/Resources/    # Admin panel resources

resources/views/
├── auth/                  # Halaman login & registrasi
└── dashboard/
    ├── mother/            # Views ibu hamil
    ├── midwife/           # Views bidan
    └── doctor/            # Views dokter

routes/
└── web.php                # Semua route, dikelompokkan per role
```

---

## 🔒 Keamanan NIK

BumilSehat menerapkan pendekatan **dual-storage** untuk NIK sesuai prinsip UU PDP:

| Kolom | Metode | Tujuan |
|-------|--------|--------|
| `nik_encrypted` | AES (Laravel Crypt) | Ditampilkan ke user berwenang |
| `nik_hash` | HMAC-SHA256 | Uniqueness check & pencarian |

NIK **tidak pernah** disimpan dalam bentuk plaintext di database.

---

## 📖 Dokumentasi

Dokumentasi lengkap tersedia di folder `doc/`:

| File | Isi |
|------|-----|
| [`doc/01__PRD.md`](doc/01.%20PRD.md) | Product Requirement Document lengkap |
| [`doc/02__ERD.md`](doc/02.%20ERD.md) | Entity Relationship Document + schema detail |
| [`CLAUDE.md`](doc/00.%20CLAUDE.md) | Panduan project untuk Claude Code |

---

## 🗺 Route Overview

```
/                           → redirect ke /login
/login                      → halaman login (nomor HP + password)
/register                   → pilih role registrasi
/register/mother            → registrasi ibu hamil (3 step)
/register/medical           → registrasi tenaga medis

/mother/*                   → dashboard ibu hamil
/midwife/*                  → dashboard bidan
/doctor/*                   → dashboard dokter
/admin                      → panel admin (Filament)
```

---

## 🚢 Deployment

### Production Checklist

```bash
# 1. Set environment
APP_ENV=production
APP_DEBUG=false

# 2. Optimize
php artisan optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 3. Build assets
npm run build

# 4. Pastikan queue worker berjalan (untuk event listener TTD)
php artisan queue:work --daemon
```

### Hal yang WAJIB dicek sebelum go-live

- [ ] `APP_KEY` sudah di-generate dan aman
- [ ] `NIK_HASH_SALT` sudah di-set dan dicatat di tempat aman
- [ ] `APP_DEBUG=false`
- [ ] Akun demo sudah dihapus atau password diganti
- [ ] `storage:link` sudah dijalankan
- [ ] Queue worker berjalan (untuk `AutoActivateTtdOnValidation`)
- [ ] HTTPS aktif (wajib — data kesehatan sensitif)

---

## 📝 Lisensi

Distributed under the MIT License. See [`LICENSE`](LICENSE) for more information.

---

<div align="center">

Dibuat dengan ❤️ untuk mendukung kesehatan ibu hamil Indonesia

</div>