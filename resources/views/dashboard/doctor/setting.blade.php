@extends('layouts.doctor')

@section('title', 'BumilSehat - Profil Saya')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100">
        <a href="{{ route('doctor.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Profil Saya</h1>
            <p class="text-xs text-gray-400">Kelola data dan akun</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-28">

        {{-- PROFILE CARD --}}
        <div
            class="bg-linear-to-br from-blue-500 to-blue-700 rounded-[2.5rem] p-6 text-white shadow-xl shadow-blue-200 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-5">
                <div class="w-16 h-16 rounded-full border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}"
                        class="rounded-full w-full h-full object-cover bg-blue-300" alt="avatar">
                </div>
                <div class="flex-1">
                    <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest">
                        {{ $user->medicalProfile?->specialization?->name ?? 'Dokter / Nutrisionis' }}
                    </p>
                    <h2 class="text-base font-bold leading-tight">
                        {{ $user->medicalProfile->full_name }}
                    </h2>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        @if ($user->is_verified)
                            <span class="text-[10px] bg-white/30 px-2.5 py-1 rounded-full font-semibold">✔️
                                Terverifikasi</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- DATA DIRI --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-bold text-gray-700">Data Diri</h3>
                <a href="{{ route('doctor.profile.edit') }}"
                    class="text-xs text-blue-500 font-semibold hover:text-blue-700">
                    Edit
                </a>
            </div>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

                @foreach ([['icon' => 'fa-user', 'label' => 'Nama Lengkap', 'value' => $user->name], ['icon' => 'fa-phone', 'label' => 'No. HP', 'value' => $user->phone], ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => $user->email ?? '—']] as $item)
                    <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                                <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                            </div>
                            <span class="text-gray-500">{{ $item['label'] }}</span>
                        </div>
                        <span class="font-semibold text-gray-700 text-right max-w-[55%] truncate">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach

            </div>
        </section>

        {{-- IDENTITAS PROFESIONAL --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-bold text-gray-700">Identitas Profesional</h3>
                <span class="text-[10px] text-gray-400 bg-gray-100 px-2.5 py-1 rounded-full">Dikelola Admin</span>
            </div>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

                @foreach ([['icon' => 'fa-user-doctor', 'label' => 'Profesi', 'value' => $user->medicalProfile?->specialization?->name ?? '—'], ['icon' => 'fa-graduation-cap', 'label' => 'Gelar', 'value' => trim(($user->medicalProfile?->degree_prefix ?? '') . ' ' . ($user->medicalProfile?->degree_suffix ?? '')) ?: '—'], ['icon' => 'fa-id-badge', 'label' => 'No. STR / SIP', 'value' => $user->medicalProfile?->license_number ?? '—']] as $item)
                    <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                                <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                            </div>
                            <span class="text-gray-500">{{ $item['label'] }}</span>
                        </div>
                        <span class="font-semibold text-gray-700 text-right max-w-[55%] truncate">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach

            </div>
            <p class="text-[11px] text-gray-400 px-1 leading-relaxed">
                Data identitas profesional hanya dapat diubah melalui Admin Puskesmas.
            </p>
        </section>

        {{-- BATASAN AKSES --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Akses Anda</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">

                @foreach ([['can' => true, 'label' => 'Lihat data ANC ibu hamil'], ['can' => true, 'label' => 'Memberikan rekomendasi (non-mengikat)'], ['can' => false, 'label' => 'Input data ANC'], ['can' => false, 'label' => 'Validasi & kunci data ANC'], ['can' => false, 'label' => 'Membuat atau mengedit artikel']] as $access)
                    <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 last:border-0">
                        <div
                            class="w-8 h-8 {{ $access['can'] ? 'bg-green-50' : 'bg-red-50' }} rounded-xl flex items-center justify-center shrink-0">
                            <i
                                class="fa-solid {{ $access['can'] ? 'fa-check text-green-500' : 'fa-xmark text-red-400' }} text-xs"></i>
                        </div>
                        <span class="text-sm {{ $access['can'] ? 'text-gray-700' : 'text-gray-400' }}">
                            {{ $access['label'] }}
                        </span>
                    </div>
                @endforeach

            </div>
        </section>

        {{-- KEAMANAN AKUN --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Keamanan Akun</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <a href="{{ route('doctor.profile.password') }}"
                    class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-blue-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <span class="text-gray-700">Ubah Kata Sandi</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </a>
            </div>
        </section>

        {{-- BANTUAN --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Bantuan</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <button
                    class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-blue-50 transition border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-circle-question text-xs"></i>
                        </div>
                        <span class="text-gray-700">Pusat Bantuan</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </button>
                <button class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-blue-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-shield-halved text-xs"></i>
                        </div>
                        <span class="text-gray-700">Kebijakan Privasi</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </button>
            </div>
        </section>

        {{-- KELUAR --}}
        <section class="space-y-3">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <button onclick="document.getElementById('logoutModal').classList.remove('hidden')"
                    class="w-full flex items-center gap-3 px-5 py-4 hover:bg-red-50 transition text-left">
                    <div class="w-8 h-8 bg-red-50 rounded-xl flex items-center justify-center text-red-400">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-red-500">Keluar</span>
                </button>
            </div>
            <p class="text-center text-[10px] text-gray-300 pt-1">BumilSehat v1.0.0</p>
        </section>

    </main>

    {{-- MODAL KELUAR --}}
    <div id="logoutModal" class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-end justify-center z-[999] hidden">
        <div class="w-full max-w-md bg-white rounded-t-[2.5rem] p-6 space-y-5 shadow-2xl">
            <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto"></div>
            <div class="text-center space-y-2 pt-1">
                <div class="w-14 h-14 bg-red-50 rounded-2xl flex items-center justify-center mx-auto">
                    <i class="fa-solid fa-right-from-bracket text-red-400 text-xl"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800 mt-2">Keluar dari Akun?</h3>
                <p class="text-sm text-gray-500 leading-relaxed px-2">Sesi Anda akan diakhiri.</p>
            </div>
            <div class="space-y-3 pt-1">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 rounded-2xl transition">
                        Ya, Keluar
                    </button>
                </form>
                <button onclick="document.getElementById('logoutModal').classList.add('hidden')"
                    class="w-full text-sm font-semibold text-gray-500 py-2 hover:text-gray-700 transition">
                    Batal
                </button>
            </div>
        </div>
    </div>

@endsection
