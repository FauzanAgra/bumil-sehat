@extends('layouts.midwife')

@section('title', 'BumilSehat - Jadwal ANC')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('midwife.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Jadwal ANC</h1>
            <p class="text-xs text-gray-400">Pemeriksaan terjadwal</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-5 space-y-5 pb-28">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        <div class="flex items-center justify-between px-1">
            <div>
                <p class="text-xs text-gray-400 font-medium">Hari ini</p>
                <h3 class="text-base font-bold text-gray-800">
                    {{ now()->translatedFormat('l, d F Y') }}
                </h3>
            </div>
            <a href="{{ route('midwife.schedules.create') }}"
                class="flex items-center gap-1.5 bg-rose-500 text-white text-xs font-semibold px-3 py-2 rounded-xl shadow-sm">
                <i class="fa-solid fa-plus text-[10px]"></i> Buat Jadwal
            </a>
        </div>

        {{-- Filter tab --}}
        <div class="flex gap-2 overflow-x-auto pb-1 scrollbar-hide">
            @foreach ([
            'today' => 'Hari Ini',
            'tomorrow' => 'Besok',
            'week' => 'Minggu Ini',
            'all' => 'Semua',
        ] as $key => $label)
                <a href="{{ route('midwife.schedules', ['filter' => $key]) }}"
                    class="px-4 py-2 rounded-xl text-xs font-semibold whitespace-nowrap transition
                    {{ $filter === $key
                        ? 'bg-rose-500 text-white'
                        : 'bg-white border border-gray-200 text-gray-500 hover:border-rose-300 hover:text-rose-500' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        {{-- Summary strip --}}
        <div class="grid grid-cols-3 gap-2">
            <div class="bg-yellow-50 rounded-2xl px-3 py-3 text-center">
                <p class="text-lg font-bold text-yellow-600">{{ $summary['validasi'] }}</p>
                <p class="text-[9px] text-yellow-500 font-semibold leading-tight mt-0.5">Menunggu<br>Validasi</p>
            </div>
            <div class="bg-blue-50 rounded-2xl px-3 py-3 text-center">
                <p class="text-lg font-bold text-blue-500">{{ $summary['belum_input'] }}</p>
                <p class="text-[9px] text-blue-400 font-semibold leading-tight mt-0.5">Belum<br>Diinput</p>
            </div>
            <div class="bg-red-50 rounded-2xl px-3 py-3 text-center">
                <p class="text-lg font-bold text-red-500">{{ $summary['risiko'] }}</p>
                <p class="text-[9px] text-red-400 font-semibold leading-tight mt-0.5">Kondisi<br>Risiko</p>
            </div>
        </div>

        {{-- Schedule list --}}
        <section class="space-y-3">
            @forelse ($schedules as $schedule)
                @php
                    $record = $schedule->ancRecord;
                    $hasPending = $record && !$record->is_locked && !$record->validation;
                    $hasRisk = $record?->detectedConditions->where('status', 'detected')->isNotEmpty();

                    if ($hasRisk) {
                        $borderClass = 'border-red-100';
                        $badgeClass = 'bg-red-50 text-red-500';
                        $badgeText = '⚠️ Risiko';
                        $link = route('midwife.anc.validate', $record->id);
                    } elseif ($hasPending) {
                        $borderClass = 'border-yellow-200';
                        $badgeClass = 'bg-yellow-50 text-yellow-600';
                        $badgeText = '⏳ Validasi';
                        $link = route('midwife.anc.validate', $record->id);
                    } elseif ($record?->is_locked) {
                        $borderClass = 'border-green-100';
                        $badgeClass = 'bg-green-50 text-green-600';
                        $badgeText = '✔ Selesai';
                        $link = route('midwife.patients.show', $schedule->pregnancy->mother_id);
                    } else {
                        $borderClass = 'border-gray-100';
                        $badgeClass = 'bg-blue-50 text-blue-500';
                        $badgeText = 'Menunggu Input';
                        $link = route('midwife.patients.show', $schedule->pregnancy->mother_id);
                    }
                @endphp

                <a href="{{ $link }}"
                    class="block bg-white rounded-3xl p-4 border {{ $borderClass }} shadow-sm hover:shadow-md transition active:scale-[0.98]">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 bg-rose-50 rounded-2xl flex flex-col items-center justify-center shrink-0">
                            <span class="text-[9px] text-rose-400 font-bold">ANC</span>
                            <span class="text-lg font-bold text-rose-500 leading-none">{{ $schedule->anc_number }}</span>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800">
                                {{ $schedule->pregnancy->mother->user->name }}
                            </p>
                            <p class="text-xs text-gray-400 mt-0.5">
                                {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d F Y') }}
                                @if ($schedule->schedule_time)
                                    · {{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }} WIB
                                @endif
                            </p>
                        </div>
                        <span
                            class="text-[10px] {{ $badgeClass }} font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                            {{ $badgeText }}
                        </span>
                    </div>
                </a>
            @empty
                <div class="bg-white rounded-3xl p-8 border border-gray-100 shadow-sm text-center space-y-4">
                    <div class="w-14 h-14 mx-auto bg-gray-50 rounded-2xl flex items-center justify-center">
                        <i class="fa-regular fa-calendar-xmark text-gray-300 text-xl"></i>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-600">Tidak ada jadwal</p>
                        <p class="text-xs text-gray-400 leading-relaxed">Belum ada ANC yang terjadwal.</p>
                    </div>
                    <a href="{{ route('midwife.schedules.create') }}"
                        class="inline-flex items-center gap-2 bg-rose-500 text-white text-xs font-semibold px-5 py-2.5 rounded-2xl shadow-md shadow-rose-200">
                        <i class="fa-solid fa-calendar-plus text-xs"></i> Buat Jadwal ANC
                    </a>
                </div>
            @endforelse
        </section>

    </main>

@endsection
