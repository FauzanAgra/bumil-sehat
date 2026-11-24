# Design System (DESIGN) — BumilSehat

Dokumen ini mengekstrak dan menstandarisasi bahasa visual **BumilSehat** dari implementasi aktual pada [`resources/css/app.css`](../resources/css/app.css), [`resources/views/layouts/`](../resources/views/layouts), dan [`AdminPanelProvider.php`](../app/Providers/Filament/AdminPanelProvider.php), agar seluruh tampilan baru dan perbaikan UI tetap konsisten lintas peran serta lintas asisten AI.

---

## 1. Design Philosophy & Brand Direction

1. **Hangat & Menenangkan untuk Ibu Hamil (`mother`)**: Kehamilan adalah fase personal yang sensitif; antarmuka menggunakan palet *soft rose*, sudut membulat besar (*pill / squircle*), dan salinan teks empatik (mis. pesan peringatan risiko bernuansa tenang agar ibu tidak panik sebelum bertemu Bidan).
2. **Jelas & Cepat Dipindai untuk Tenaga Kesehatan (`midwife` & `doctor`)**: Ringkasan status risiko (KEK, Preeklampsia, Anemia), antrean validasi, dan timeline ANC 1–6 memakai kode warna semantik yang tegas dengan hierarki tipografi yang rapi.
3. **Diferensiasi Peran Lewat Warna Aksen**:
   - **Ibu Hamil, Bidan, & Admin Panel (Filament)**: Identitas utama **Rose** (`#f43f5e`).
   - **Dokter / Supervisi Medis**: Identitas aksen **Blue** (`bg-blue-50`, `text-blue-500`) untuk membedakan mode supervisi klinis lintas Puskesmas secara instan.
4. **Mobile-First App Shell**: Seluruh aplikasi pengguna (`mother`, `midwife`, `doctor`) dirancang seperti *native mobile app* (`max-w-md mx-auto`) dengan navigasi bawah (*bottom navigation*) yang mudah dijangkau ibu jari saat dipakai di ruang periksa Puskesmas maupun di rumah.

---

## 2. Visual Identity & Color Palette

### 2.1 Primary Brand Tokens (`resources/css/app.css` — Tailwind CSS v4 `@theme`)

| Token CSS | Hex | Penggunaan Utama |
| --- | --- | --- |
| `--color-rose-50` | `#fff1f2` | Background halaman luar (Mother, Midwife, Auth), tint input & badge ringan |
| `--color-rose-100` | `#ffe4e6` | Border input lembut, gradient ikon, surface highlight |
| `--color-rose-200` | `#fecdd3` | Border alert kosong, shadow tint (`shadow-rose-200`) |
| `--color-rose-300` | `#fda4af` | Ring avatar, aksen halus |
| `--color-rose-400` | `#fb7185` | Ikon input, hover nav, gradient awal `.day-today`, label kecil |
| `--color-rose-500` | `#f43f5e` | **Primary Brand** — tombol aksi utama, header auth, active nav, Filament `Color::Rose` |
| `--color-rose-600` | `#e11d48` | State hover tombol utama |
| `--color-rose-700` | `#be123c` | Akhir gradient hero card (`from-rose-500 to-rose-700`) |
| `--color-rose-800` | `#9f1239` | Teks kontras tinggi di atas permukaan rose muda |
| `--color-rose-900` | `#881337` | Teks penekanan maksimal |

### 2.2 Role & Surface Palette

| Peran / Permukaan | Background Luar | Container Utama | Aksen Aktif |
| --- | --- | --- | --- |
| **Mother & Auth** | `bg-rose-50` | `bg-white` | `rose-500` (`.active-nav`) |
| **Midwife** | `bg-rose-50` | `bg-white` | `rose-500` (`.active-nav`) |
| **Doctor** | `bg-blue-50` | `bg-white` | `text-blue-500` (`hover:text-blue-400`) |
| **Admin (Filament 5)** | Filament Light/Default | Filament Panel | `Color::Rose` |

### 2.3 Semantic & Clinical Status Colors

| Status / Kondisi | Background | Text / Border | Ikon Font Awesome |
| --- | --- | --- | --- |
| **Preeklampsia (Risiko Tinggi)** | `bg-red-100` / `bg-red-50` | `text-red-600` / `border-red-200` | `fa-heart-pulse` |
| **KEK (Kurang Energi Kronis)** | `bg-orange-100` | `text-orange-600` | `fa-weight-scale` |
| **Anemia / Alert Perhatian** | `bg-yellow-100` / `bg-yellow-50` | `text-yellow-600` / `border-yellow-200` | `fa-droplet` / `fa-heart-circle-exclamation` |
| **TTD Sudah Diminum (`.day-done`)** | `#d1fae5` (`emerald-100`) | `#059669` (`emerald-600`), border `2px solid #34d399` | `fa-check` |
| **TTD Hari Ini (`.day-today`)** | Gradient `135deg, rose-400 → rose-500` | `#ffffff`, shadow `rgb(244 63 94 / 0.3)` | - |
| **Aksi WhatsApp** | `bg-green-500` | `text-white`, `shadow-green-100` | `fa-brands fa-whatsapp` |
| **Error Form / Validasi** | `bg-red-50` | `text-red-600`, `border-red-200` | - |
| **Teks Netral** | - | Utama `text-gray-800` / `text-gray-700`, Sekunder `text-gray-500`, Muted `text-gray-400` / `text-gray-300` | - |

> **Mode Warna**: Aplikasi saat ini menggunakan **Light Mode** secara eksklusif agar konsisten dengan estetika KIA yang bersih dan mudah dibaca di kondisi pencahayaan ruang klinik/outdoor.

---

## 3. Typography & Type Scale

- **Font Family**: `Poppins` (Google Fonts, bobot `300, 400, 500, 600, 700, 800`), didefinisikan pada `--font-sans: "Poppins", sans-serif`.
- **Base Root Size**: `html { font-size: 15px; }`.
- **Artikel Edukasi**: Menggunakan plugin `@tailwindcss/typography` (`prose`) agar heading, paragraf, list, blockquote, dan gambar dari Filament `RichEditor` terformat rapi.

| Level | Kelas Tailwind | Penggunaan |
| --- | --- | --- |
| **Hero Metric** | `text-3xl font-bold` | Angka usia janin (minggu) & metrik utama dashboard |
| **Page Title** | `text-2xl font-bold` / `text-xl font-bold tracking-wide` | Judul layar Auth & header utama |
| **Section Heading** | `text-lg font-bold text-gray-700` / `text-base font-bold` | Nama pengguna, judul seksi kartu |
| **Card Title** | `text-sm font-bold text-gray-700` | Nama Bidan/pasien, item timeline ANC |
| **Body & Input** | `text-sm text-gray-600` | Isi kartu, input form, alert validasi |
| **Caption / Meta** | `text-xs text-gray-400` / `text-xs text-gray-500` | Jadwal praktik, subjudul, keterangan waktu |
| **Micro Label / Badge** | `text-[11px]` / `text-[10px] font-bold uppercase tracking-widest` / `text-[9px]` | Sapaan waktu, label kategori, badge target 90 tablet |

---

## 4. Spacing, Border Radius, Layout Rules & Grid System

### 4.1 Mobile App Shell
- **Container Utama**: `min-h-screen max-w-md mx-auto bg-white flex flex-col`.
- **Auth Container**: `w-full max-w-md bg-white sm:rounded-4xl sm:shadow-2xl h-screen sm:h-[90vh] overflow-hidden flex flex-col`.
- **Content Area**: `flex-1 overflow-y-auto px-6 pb-28 space-y-6` (padding bawah `pb-28` memberi ruang aman di atas *fixed bottom navigation*).
- **Sub-Pages & Forms**: Menggunakan layout `-plain` (`mother-plain`, `midwife-plain`, `doctor-plain`) tanpa bottom navigation agar pengguna fokus menyelesaikan form (input ANC, validasi ANC, tambah jadwal, edit profil, ganti password).

### 4.2 Border Radius Scale
| Kelas | Penggunaan |
| --- | --- |
| `rounded-[2.5rem]` | Kartu hero besar (Usia Janin, container utama widget TTD) |
| `rounded-4xl` | Kartu fitur utama (Bidan Pendamping, shell Auth di layar `sm+`) |
| `rounded-3xl` | Alert kondisi risiko, header Auth melengkung bawah (`rounded-b-3xl`) |
| `rounded-2xl` | Ikon kontainer (`w-12 h-12` / `w-14 h-14`), tombol aksi WhatsApp |
| `rounded-xl` | Input form, tombol submit utama, alert error form |
| `rounded-full` | Avatar, status pill badge, target chip |

---

## 5. Component Blueprints

### 5.1 Buttons
- **Primary CTA**: `w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95`
- **Floating Action**: `.floating-button` (`box-shadow: 0 10px 25px -5px rgb(244 63 94 / 0.4)`)
- **WhatsApp Action**: `w-12 h-12 bg-green-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-green-100 hover:scale-110 transition-transform`

### 5.2 Form Inputs
- **Label**: `text-sm font-semibold text-gray-600 ml-1 mb-1 block`
- **Field dengan Leading Icon**: `w-full pl-10 pr-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all`
- **Validation Error Banner**: `bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-4`

### 5.3 Bottom Navigation (`fixed bottom-0`)
- Container: `fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-gray-100 px-8 py-4 flex justify-between z-50`
- Tab per peran:
  - **Mother (4 tab)**: Beranda (`fa-house-chimney`), Konsultasi (`fa-comment-medical`), Artikel (`fa-newspaper`), Pengaturan (`fa-gear`).
  - **Midwife (4 tab)**: Beranda (`fa-house-chimney`), Jadwal (`fa-calendar`), Pasien (`fa-people-group`), Pengaturan (`fa-gear`).
  - **Doctor (3 tab)**: Beranda (`fa-house-chimney`), Pasien (`fa-people-group`), Pengaturan (`fa-gear`).
- Active state: `.active-nav` (`color: var(--color-rose-500); transform: translateY(-4px); transition: all 0.3s ease;`) untuk Mother/Midwife dan `text-blue-500` untuk Doctor.

### 5.4 Cards & Feedback States
- **Hero Pregnancy Card**: Gradient `bg-linear-to-br from-rose-500 to-rose-700`, dekorasi lingkaran blur `bg-white/10 rounded-full blur-2xl`, status pill `bg-white/30`.
- **Empty State**: Kartu ringan `bg-rose-50 border border-rose-200 rounded-[2.5rem] p-7 text-center` dengan pesan informatif & CTA bila relevan.
- **Avatar**: DiceBear Notionists SVG (`https://api.dicebear.com/9.x/notionists/svg?seed=...`) di dalam bingkai `rounded-full border-2 border-rose-300 p-0.5`.
- **Motion Utilities**:
  - `.fade-in` (`0.4s ease-in-out`, `translateY(10px) → 0`) untuk transisi masuk layar.
  - `.day-today` (`bounce-subtle 2s infinite ease-in-out`) untuk penanda hari ini pada grid TTD 5 hari.
  - `.no-scrollbar` & `.scrollbar-hide` untuk area gulir horizontal bersih.

---

## 6. Responsive Behavior & Accessibility

1. **Mobile-First Centered Column**: Pada layar desktop, aplikasi pengguna (`mother`, `midwife`, `doctor`) tetap terpusat di kolom `max-w-md mx-auto` di atas latar `bg-rose-50` atau `bg-blue-50` untuk menjaga proporsi tata letak; panel admin Filament 5 (`/admin`) berjalan *desktop-first* dengan sidebar responsif bawaan Filament.
2. **Panduan Aksesibilitas (Wajib Dijaga pada Pengembangan Baru)**:
   - Gunakan elemen semantik HTML (`<header>`, `<main>`, `<section>`, `<nav>`, `<label for="...">`) serta teks `aria-label` pada tombol ikon (seperti tab bottom navigation, tombol mata password, dan tombol WhatsApp).
   - Pertahankan fokus keyboard yang terlihat (`focus:ring-4 focus:ring-rose-500/10 focus:border-rose-500`) dan jangan menghapus outline fokus tanpa pengganti yang jelas.
   - Pastikan seluruh ikon status klinis selalu disertai label teks deskriptif (jangan hanya mengandalkan warna) agar tetap dapat dipahami oleh pengguna dengan keterbatasan penglihatan warna.
   - Hormati `prefers-reduced-motion` saat menambah animasi baru dan hindari animasi dekoratif berlebihan.
