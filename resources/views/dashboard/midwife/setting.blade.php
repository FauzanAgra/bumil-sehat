@extends('layouts.midwife')

@section('title', 'BumilSehat - Profil Bidan')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('midwife.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Profil Bidan</h1>
            <p class="text-xs text-gray-400">Kelola data dan akun</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-28">

        {{-- Profile card --}}
        <div
            class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2.5rem] p-6 text-white shadow-xl shadow-rose-200 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-5">
                <div class="w-16 h-16 rounded-full border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}"
                        class="rounded-full w-full h-full" alt="avatar">
                </div>
                <div class="flex-1">
                    <p class="text-rose-100 text-[10px] font-bold uppercase tracking-widest">Bidan</p>
                    <h2 class="text-lg font-bold leading-tight">{{ $user->name }}</h2>
                    @if ($user->medicalProfile?->specialization)
                        <span class="inline-block mt-1 text-[10px] bg-white/30 px-3 py-1 rounded-full">
                            {{ $user->medicalProfile->specialization->name }}
                        </span>
                    @endif
                </div>
            </div>
        </div>

        {{-- Wilayah --}}
        @if ($user->midwifeAreas->isNotEmpty())
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Wilayah Tugas</h3>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    @foreach ($user->midwifeAreas()->where('is_active', true)->with('area.subdistrict')->get() as $ma)
                        <div class="flex items-center gap-3 px-5 py-4 border-b border-gray-100 last:border-0">
                            <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-map-pin text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-semibold text-gray-800">
                                    Kel. {{ $ma->area->subdistrict->name }}
                                </p>
                                <p class="text-xs text-gray-400">RW {{ $ma->area->rw_number }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Data Diri --}}
        <section class="space-y-3">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-bold text-gray-700">Data Diri</h3>
                <a href="{{ route('midwife.setting') }}" class="text-xs text-rose-500 font-semibold hover:text-rose-700">
                    Edit
                </a>
            </div>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                @foreach ([['icon' => 'fa-user', 'label' => 'Nama Lengkap', 'value' => $user->name], ['icon' => 'fa-phone', 'label' => 'Nomor HP', 'value' => $user->phone], ['icon' => 'fa-envelope', 'label' => 'Email', 'value' => $user->email ?? '—']] as $item)
                    <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100 last:border-0">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                            </div>
                            <span class="text-gray-500">{{ $item['label'] }}</span>
                        </div>
                        <span class="font-semibold text-gray-800 text-right max-w-[55%] truncate">
                            {{ $item['value'] }}
                        </span>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- Identitas Profesional --}}
        @if ($user->medicalProfile)
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Identitas Profesional</h3>
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                    @foreach ([['icon' => 'fa-id-card', 'label' => 'No. STR / SIP', 'value' => $user->medicalProfile->license_number], ['icon' => 'fa-graduation-cap', 'label' => 'Gelar', 'value' => trim(($user->medicalProfile->degree_prefix ?? '') . ' ' . ($user->medicalProfile->degree_suffix ?? '')) ?: '—'], ['icon' => 'fa-clock', 'label' => 'Jam Praktik', 'value' => $user->medicalProfile->availability ?? '—']] as $item)
                        <div
                            class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100 last:border-0">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                                    <i class="fa-solid {{ $item['icon'] }} text-xs"></i>
                                </div>
                                <span class="text-gray-500">{{ $item['label'] }}</span>
                            </div>
                            <span class="font-semibold text-gray-800 text-right max-w-[55%] truncate">
                                {{ $item['value'] }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Keamanan Akun --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Keamanan Akun</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <a href="#" {{-- ganti dengan route ubah password ketika sudah dibuat --}}
                    class="flex items-center justify-between px-5 py-4 hover:bg-gray-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-yellow-50 rounded-xl flex items-center justify-center text-yellow-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">Ubah Password</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </a>
            </div>
        </section>

        {{-- Bantuan --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Bantuan</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center text-purple-400">
                            <i class="fa-solid fa-circle-question text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">Panduan Penggunaan</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </div>
                <div class="flex items-center justify-between px-5 py-4 border-t border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center text-green-400">
                            <i class="fa-brands fa-whatsapp text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-700">Hubungi Admin</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </div>
            </div>
        </section>

        {{-- Keluar --}}
        <section class="space-y-3">
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <button type="button" onclick="document.getElementById('logoutModal').classList.remove('hidden')"
                    class="w-full flex items-center gap-3 px-5 py-4 hover:bg-red-50 transition text-left">
                    <div class="w-8 h-8 bg-red-50 rounded-xl flex items-center justify-center text-red-400">
                        <i class="fa-solid fa-right-from-bracket text-xs"></i>
                    </div>
                    <span class="text-sm font-semibold text-red-500">Keluar</span>
                </button>
            </div>
        </section>

        {{-- Logout Modal --}}
        <div id="logoutModal"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-end justify-center z-[999] hidden">
            <div class="bg-white w-full max-w-md rounded-t-[2rem] p-6 space-y-4">
                <div class="w-12 h-12 bg-red-100 rounded-2xl flex items-center justify-center mx-auto">
                    <i class="fa-solid fa-right-from-bracket text-red-500 text-lg"></i>
                </div>
                <h3 class="text-base font-bold text-gray-800 text-center mt-2">Keluar dari Akun?</h3>
                <p class="text-xs text-gray-500 text-center leading-relaxed">
                    Kamu akan keluar dari sesi ini. Data yang belum disimpan akan hilang.
                </p>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full bg-red-500 hover:bg-red-600 text-white font-bold py-3.5 rounded-2xl transition mb-2">
                        Ya, Keluar
                    </button>
                </form>
                <button onclick="document.getElementById('logoutModal').classList.add('hidden')"
                    class="w-full bg-gray-100 text-gray-600 font-semibold py-3.5 rounded-2xl hover:bg-gray-200 transition">
                    Batal
                </button>
            </div>
        </div>
    </main>

@endsection
