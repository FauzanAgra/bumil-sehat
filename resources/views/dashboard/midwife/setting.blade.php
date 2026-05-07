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

        {{-- Data Profesi --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Data Profesi</h3>
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="flex items-center justify-between px-5 py-4 text-sm border-b border-gray-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-id-badge text-xs"></i>
                        </div>
                        <span class="text-gray-500">No. STR/SIP</span>
                    </div>
                    <span class="font-semibold text-gray-700">
                        {{ $user->medicalProfile?->license_number ?? '—' }}
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
                            <i class="fa-regular fa-clock text-xs"></i>
                        </div>
                        <span class="text-gray-500">Jam Tersedia</span>
                    </div>
                    <span class="font-semibold text-gray-700 text-right max-w-40 leading-snug">
                        {{ $user->medicalProfile?->availability ?? '—' }}
                    </span>
                </div>
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
