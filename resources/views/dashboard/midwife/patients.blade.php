@extends('layouts.midwife')

@section('title', 'BumilSehat - Ibu Dampingan')

@section('content')

    <header class="bg-linear-to-br from-rose-500 to-rose-700 px-6 pt-6 pb-8 relative overflow-hidden shrink-0">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex justify-between items-start mb-5">
            <div>
                <a href="{{ route('midwife.dashboard') }}"
                    class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/30 transition mb-3 inline-flex">
                    <i class="fa-solid fa-arrow-left text-sm"></i>
                </a>
                <h1 class="text-xl font-bold text-white leading-tight">Ibu Dampingan</h1>
            </div>
            <div class="text-right pt-1">
                <p class="text-rose-100 text-[10px] font-medium">Total Ibu</p>
                <p class="text-3xl font-bold text-white leading-none mt-0.5">{{ $stats['total'] }}</p>
            </div>
        </div>

        <div class="relative z-10 grid grid-cols-3 gap-2">
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $stats['normal'] }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5">Normal</p>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $stats['validasi'] }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5">Menunggu<br>Validasi</p>
            </div>
            <div class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center">
                <h3 class="text-lg font-bold text-white">{{ $stats['risiko'] }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5">Kondisi<br>Risiko</p>
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-5 pb-28 space-y-4">

        {{-- Search --}}
        <form method="GET" action="{{ route('midwife.patients') }}">
            <input type="hidden" name="filter" value="{{ $filter }}">
            <div class="relative">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari nama ibu..."
                    class="w-full pl-10 pr-4 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
            </div>
        </form>

        {{-- Filter tabs --}}
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            @foreach ([
            'semua' => 'Semua (' . $stats['total'] . ')',
            'risiko' => '⚠️ Risiko (' . $stats['risiko'] . ')',
            'validasi' => '⏳ Validasi (' . $stats['validasi'] . ')',
            'normal' => '✔️ Normal (' . $stats['normal'] . ')',
        ] as $key => $label)
                <a href="{{ route('midwife.patients', ['filter' => $key, 'q' => request('q')]) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition
                    {{ $filter === $key
                        ? 'bg-rose-500 text-white'
                        : 'bg-white border border-gray-200 text-gray-600 hover:bg-rose-50' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Patient list --}}
        <section class="space-y-3">
            @forelse ($mothers as $mother)
                @php
                    $flag = $mother->status_flag;
                    $borderClass = match ($flag) {
                        'risiko' => 'border-red-100',
                        'validasi' => 'border-yellow-200',
                        default => 'border-gray-100',
                    };
                    $avatarBorder = match ($flag) {
                        'risiko' => 'border-red-200',
                        'validasi' => 'border-yellow-200',
                        default => 'border-green-200',
                    };
                    $badgeClass = match ($flag) {
                        'risiko' => 'bg-red-50 text-red-500',
                        'validasi' => 'bg-yellow-50 text-yellow-600',
                        default => 'bg-green-50 text-green-600',
                    };
                    $badgeText = match ($flag) {
                        'risiko' => '⚠️ Risiko',
                        'validasi' => '⏳ Validasi',
                        default => '✔️ Normal',
                    };
                @endphp

                <a href="{{ route('midwife.patients.show', $mother->id) }}"
                    class="block bg-white rounded-3xl p-4 border {{ $borderClass }} shadow-sm hover:shadow-md transition-all active:scale-[0.98]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-2xl border-2 {{ $avatarBorder }} p-0.5 shrink-0">
                            <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $mother->user->name }}"
                                class="rounded-xl w-full h-full object-cover" alt="{{ $mother->user->name }}">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 flex-wrap">
                                <h4 class="text-sm font-bold text-gray-800">{{ $mother->user->name }}</h4>
                                <span class="text-[9px] {{ $badgeClass }} font-bold px-2 py-0.5 rounded-full">
                                    {{ $badgeText }}
                                </span>
                            </div>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ $mother->activePregnancy?->gestational_week ?? '—' }} minggu
                                ·
                                ANC ke-{{ $mother->activePregnancy?->completed_anc_count ?? 0 }} selesai
                            </p>
                            @if (count($mother->conditions ?? []) > 0)
                                <div class="flex flex-wrap gap-1 mt-1.5">
                                    @foreach ($mother->conditions as $c)
                                        <span
                                            class="text-[9px] {{ $c === 'preeclampsia' ? 'bg-red-50 text-red-500' : ($c === 'kek' ? 'bg-orange-50 text-orange-500' : 'bg-blue-50 text-blue-500') }} font-semibold px-1.5 py-0.5 rounded-full">
                                            {{ match ($c) {
                                                'kek' => 'KEK',
                                                'preeclampsia' => 'Preeklamsia',
                                                'anemia' => 'Anemia',
                                                default => $c,
                                            } }}
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
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                        <i class="fa-solid fa-people-group text-gray-400 text-2xl"></i>
                    </div>
                    <p class="text-sm font-semibold text-gray-700">Tidak ada ibu dampingan</p>
                </div>
            @endforelse
        </section>

    </main>

@endsection
