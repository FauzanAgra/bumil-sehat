# Architecture Decision Records (ADR) — BumilSehat

Folder ini menyimpan catatan keputusan arsitektur penting (**Architecture Decision Records**) untuk proyek **BumilSehat**. Setiap keputusan besar yang memengaruhi database, keamanan, autentikasi, runtime, atau struktur domain wajib terdokumentasi di sini agar seluruh pengembang dan asisten AI memahami konteks serta alasan teknis di baliknya.

## Daftar ADR

| ID | Judul | Status | Tanggal |
| --- | --- | :-: | :-: |
| [ADR-0001](./0001-database-selection.md) | Pemilihan PostgreSQL sebagai Database Utama & SQLite untuk Testing | Accepted | 2026-11-24 |
| [ADR-0002](./0002-nik-encryption-and-audit-trail.md) | Enkripsi Dual-Storage NIK & Audit Log Immutable (UU PDP) | Accepted | 2026-11-24 |
| [ADR-0003](./0003-role-authorization-and-authentication.md) | Autentikasi Nomor HP, Otorisasi Peran & Cakupan Single-Puskesmas | Accepted | 2026-11-24 |
| [ADR-0004](./0004-runtime-octane-and-atomic-ttd-activation.md) | Runtime Laravel Octane (FrankenPHP) & Aktivasi TTD Sinkron-Atomik | Accepted | 2026-11-24 |
| [ADR-0005](./0005-extensible-medical-specializations.md) | Tabel Master Specializations Menggantikan Enum Profesi | Accepted | 2026-11-24 |

## Aturan Penulisan ADR Baru
1. Gunakan penomoran 4 digit berurutan: `0006-nama-keputusan.md`.
2. Sertakan seksi standar: **Status**, **Context**, **Decision**, **Alternatives**, dan **Consequences**.
3. Perbarui tabel indeks di file ini serta sinkronkan `docs/TECH_SPEC.md` bila keputusan berdampak pada arsitektur.
