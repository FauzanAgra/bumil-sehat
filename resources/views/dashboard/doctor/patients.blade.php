@extends('layouts.doctor')

@section('title', 'BumilSehat - Daftar Ibu Hamil')

@section('content')

    {{-- HEADER --}}
    <header class="bg-linear-to-br from-blue-500 to-blue-700 px-6 pt-6 pb-8 relative overflow-hidden shrink-0">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-4 -top-4 w-20 h-20 bg-white/5 rounded-full blur-xl"></div>

        <div class="relative z-10 flex justify-between items-start mb-5">
            <div>
                <a href="{{ route('doctor.dashboard') }}"
                    class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/30 transition mb-3">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-xl font-bold text-white leading-tight">Ibu Hamil</h1>
                <p class="text-blue-100 text-xs mt-0.5">Semua pasien aktif</p>
            </div>
            <div class="text-right pt-1">
                <p class="text-blue-100 text-[10px] font-medium">Total</p>
                <p class="text-3xl font-bold text-white leading-none mt-0.5">{{ $counts['total'] }}</p>
            </div>
        </div>

        {{-- Stat strip --}}
        <div class="relative z-10 grid grid-cols-3 gap-2">
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $counts['normal'] }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5">Normal</p>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $counts['validasi'] }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5">Validasi</p>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $counts['risiko'] }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5">Risiko</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-5 pb-28 space-y-4">

        {{-- SEARCH --}}
        <form method="GET" action="{{ route('doctor.patients') }}">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="search" value="{{ $search }}" placeholder="Cari nama atau NIK ibu..."
                    class="w-full pl-10 pr-4 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 text-sm
                           focus:outline-none focus:ring-2 focus:ring-blue-200 focus:border-blue-300 transition">
            </div>
        </form>

        {{-- FILTER TABS --}}
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            @foreach ([
            'semua' => 'Semua (' . $counts['total'] . ')',
            'risiko' => '⚠️ Risiko (' . $counts['risiko'] . ')',
            'normal' => '✔️ Normal (' . $counts['normal'] . ')',
            'validasi' => '⏳ Validasi (' . $counts['validasi'] . ')',
        ] as $key => $label)
                <a href="{{ route('doctor.patients', ['filter' => $key, 'search' => $search]) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition
                        {{ $filter === $key
                            ? 'bg-blue-500 text-white'
                            : 'bg-white border border-gray-200 text-gray-600 hover:border-blue-300' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- INFO READONLY --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl px-4 py-3 text-xs text-blue-700 flex gap-2 items-center">
            <i class="fa-solid fa-eye text-blue-400 shrink-0"></i>
            <p>Anda dapat melihat data dan memberikan rekomendasi. Penguncian data dilakukan oleh Bidan.</p>
        </div>

        {{-- PATIENT LIST --}}
        <section class="space-y-3">
            @forelse ($mothers as $m)
                @php
                    $borderMap = [
                        'risiko' => 'border-red-100',
                        'validasi' => 'border-yellow-200',
                        'normal' => 'border-gray-100',
                    ];
                    $avatarBorderMap = [
                        'risiko' => 'border-red-200',
                        'validasi' => 'border-yellow-200',
                        'normal' => 'border-green-200',
                    ];
                    $avatarBgMap = ['risiko' => 'bg-red-50', 'validasi' => 'bg-yellow-50', 'normal' => 'bg-green-50'];
                    $badgeMap = [
                        'risiko' => 'bg-red-50 text-red-500',
                        'validasi' => 'bg-yellow-50 text-yellow-600',
                        'normal' => 'bg-green-50 text-green-600',
                    ];
                    $badgeLabelMap = ['risiko' => '⚠️ Risiko', 'validasi' => '⏳ Validasi', 'normal' => '✔️ Normal'];
                    $sf = $m->status_flag;
                @endphp
                <a href="{{ route('doctor.patients.show', $m->id) }}"
                    class="block bg-white rounded-3xl p-4 border {{ $borderMap[$sf] }} shadow-sm hover:shadow-md transition active:scale-[0.98]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl border-2 {{ $avatarBorderMap[$sf] }} p-0.5 shrink-0">
                            <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $m->user->name }}"
                                class="rounded-xl w-full h-full object-cover {{ $avatarBgMap[$sf] }}"
                                alt="{{ $m->user->name }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $m->user->name }}</h4>
                                <span class="text-[9px] {{ $badgeMap[$sf] }} font-bold px-2 py-0.5 rounded-full">
                                    {{ $badgeLabelMap[$sf] }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $m->activePregnancy?->gestational_week ?? '–' }} minggu ·
                                ANC ke-{{ $m->activePregnancy?->completed_anc_count ?? 0 }} selesai
                            </p>
                            @if ($m->active_conditions->isNotEmpty())
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach ($m->active_conditions as $cond)
                                        @php
                                            $cs = match ($cond->condition_type) {
                                                'preeclampsia' => 'bg-red-50 text-red-500',
                                                'kek' => 'bg-orange-50 text-orange-500',
                                                'anemia' => 'bg-yellow-50 text-yellow-600',
                                                default => 'bg-gray-50 text-gray-500',
                                            };
                                        @endphp
                                        <span
                                            class="text-[9px] {{ $cs }} font-semibold px-1.5 py-0.5 rounded-full">
                                            {{ $cond->label }}
                                        </span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <i class="fa-solid fa-chevron-right text-gray-300 text-xs shrink-0"></i>
                    </div>
                </a>
            @empty
                <div class="text-center py-12 space-y-3">
                    <div class="w-16 h-16 mx-auto bg-gray-100 rounded-2xl flex items-center justify-center">
                        <i class="fa-solid fa-user-xmark text-gray-300 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-500">Tidak ada ibu yang cocok</p>
                    <p class="text-xs text-gray-400">Coba ubah kata kunci atau filter</p>
                </div>
            @endforelse
        </section>

    </main>

@endsection

@push('head')
    <style>
        .scrollbar-hide::-webkit-scrollbar {
            display: none;
        }

        .scrollbar-hide {
            -ms-overflow-style: none;
            scrollbar-width: none;
        }
    </style>
@endpush
