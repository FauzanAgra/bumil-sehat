@extends('layouts.mother')

@section('title', 'BumilSehat - Profil')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Profil Bunda</h1>
            <p class="text-xs text-gray-400">Kelola data dan akun Bunda</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-28">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Profile Card --}}
        <div
            class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2.5rem] p-6 text-white shadow-xl shadow-rose-200 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-5">
                <div class="w-16 h-16 rounded-full border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}"
                        class="rounded-full w-full h-full object-cover bg-rose-300" alt="avatar">
                </div>
                <div class="flex-1">
                    <p class="text-rose-100 text-[10px] font-bold uppercase tracking-widest">Ibu Hamil</p>
                    <h2 class="text-lg font-bold leading-tight">{{ $user->name }}</h2>
                    <span class="inline-block mt-1.5 text-[10px] bg-white/30 px-3 py-1 rounded-full">
                        ✔️ Terverifikasi
                    </span>
                </div>
                <a href="{{ route('mother.profile.edit') }}"
                    class="w-9 h-9 bg-white/20 hover:bg-white/30 rounded-full flex items-center justify-center transition shrink-0">
                    <i class="fa-solid fa-pen text-xs"></i>
                </a>
            </div>
        </div>

        {{-- Data Diri --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Data Diri</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-id-card text-xs"></i>
                        </div>
                        <span class="text-gray-500">NIK</span>
                    </div>
                    <span class="font-semibold text-gray-700 font-mono">
                        {{ $motherProfile ? $motherProfile->getMaskedNik('mother') : '—' }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-cake-candles text-xs"></i>
                        </div>
                        <span class="text-gray-500">Tanggal Lahir</span>
                    </div>
                    <span class="font-semibold text-gray-700">
                        {{ $motherProfile?->birth_date?->translatedFormat('d F Y') ?? '—' }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-phone text-xs"></i>
                        </div>
                        <span class="text-gray-500">No. HP</span>
                    </div>
                    <span class="font-semibold text-gray-700">{{ $user->phone }}</span>
                </div>

                <div class="flex items-center justify-between px-5 py-4 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-envelope text-xs"></i>
                        </div>
                        <span class="text-gray-500">Email</span>
                    </div>
                    <span class="font-semibold text-gray-700">{{ $user->email ?? '—' }}</span>
                </div>
            </div>
        </section>

        {{-- Alamat --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Alamat</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-map-pin text-xs"></i>
                        </div>
                        <span class="text-gray-500">Kelurahan</span>
                    </div>
                    <span class="font-semibold text-gray-700">{{ $motherProfile?->subdistrict?->name ?? '—' }}</span>
                </div>

                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-signs-post text-xs"></i>
                        </div>
                        <span class="text-gray-500">RW / RT</span>
                    </div>
                    <span class="font-semibold text-gray-700">
                        {{ $motherProfile?->area?->rw_number ?? '—' }} / {{ $motherProfile?->rt ?? '—' }}
                    </span>
                </div>

                <div class="flex items-center justify-between px-5 py-4 text-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-house text-xs"></i>
                        </div>
                        <span class="text-gray-500">Detail Alamat</span>
                    </div>
                    <span class="font-semibold text-gray-700 text-right max-w-40 leading-snug">
                        {{ $motherProfile?->address ?? '—' }}
                    </span>
                </div>
            </div>
        </section>

        {{-- Pengaturan Akun --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Pengaturan Akun</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <a href="{{ route('mother.profile.password') }}"
                    class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-rose-50 transition">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-lock text-xs"></i>
                        </div>
                        <span class="text-gray-700">Ubah Kata Sandi</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </a>
            </div>
        </section>

        {{-- Bantuan --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Bantuan</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <button
                    class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-rose-50 transition border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-circle-question text-xs"></i>
                        </div>
                        <span class="text-gray-700">Pusat Bantuan</span>
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-xs"></i>
                </button>
                <button class="w-full flex items-center justify-between px-5 py-4 text-sm hover:bg-rose-50 transition">
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

        {{-- Keluar --}}
        <section>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                        class="w-full flex items-center justify-between px-5 py-4 text-sm text-red-500 hover:bg-red-50 transition">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-red-50 rounded-xl flex items-center justify-center text-red-400">
                                <i class="fa-solid fa-right-from-bracket text-xs"></i>
                            </div>
                            <span class="font-semibold">Keluar</span>
                        </div>
                    </button>
                </form>
            </div>
            <p class="text-center text-[10px] text-gray-300 pt-3">BumilSehat v1.0.0</p>
        </section>

    </main>

@endsection
