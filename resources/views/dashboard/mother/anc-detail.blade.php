@extends('layouts.mother')

@section('title', 'BumilSehat - Detail ANC')

@section('content')

    <header class="px-6 py-4 flex items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.anc.history') }}"
            class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <h1 class="text-base font-bold text-gray-800 flex-1">
            Detail ANC Ke-{{ $schedule->anc_number }}
        </h1>
        @if ($record?->is_locked)
            <span class="text-[10px] font-semibold bg-green-100 text-green-600 px-2 py-1 rounded-full">
                <i class="fa-solid fa-lock text-[9px]"></i> Terkunci
            </span>
        @endif
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-28 pt-5 space-y-5">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Info jadwal --}}
        <div class="bg-linear-to-r from-blue-500 to-blue-600 rounded-3xl p-5 text-white">
            <p class="text-[10px] font-semibold text-blue-100 uppercase tracking-wider mb-1">Jadwal Pemeriksaan</p>
            <h2 class="text-lg font-bold">Pemeriksaan Ke-{{ $schedule->anc_number }}</h2>
            <p class="text-sm opacity-90 mt-1">
                {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d F Y') }}
                @if ($schedule->schedule_time)
                    · {{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }} WIB
                @endif
            </p>
            @if ($schedule->note)
                <p class="text-xs opacity-80 mt-1">
                    <i class="fa-solid fa-location-dot text-[11px]"></i> {{ $schedule->note }}
                </p>
            @endif
        </div>

        @if ($record)
            {{-- Antropometri --}}
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-gray-700">Antropometri</h3>
                <div class="grid grid-cols-3 gap-3">
                    <div class="bg-rose-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] text-gray-500">Berat Badan</p>
                        <p class="text-base font-bold text-gray-800">{{ $record->weight ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400">kg</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] text-gray-500">Tinggi Badan</p>
                        <p class="text-base font-bold text-gray-800">{{ $record->height ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400">cm</p>
                    </div>
                    <div class="bg-rose-50 rounded-2xl p-3 text-center">
                        <p class="text-[10px] text-gray-500">LILA</p>
                        <p
                            class="text-base font-bold {{ $record->lila && $record->lila < 23.5 ? 'text-red-500' : 'text-gray-800' }}">
                            {{ $record->lila ?? '-' }}
                        </p>
                        <p class="text-[10px] text-gray-400">cm</p>
                    </div>
                </div>
            </div>

            {{-- Tanda Vital --}}
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-gray-700">Tanda Vital & Lab</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-blue-50 rounded-2xl p-3">
                        <p class="text-[10px] text-gray-500">Tekanan Darah</p>
                        <p
                            class="text-base font-bold {{ $record->systolic >= 140 || $record->diastolic >= 90 ? 'text-red-500' : 'text-gray-800' }}">
                            {{ $record->systolic && $record->diastolic ? $record->systolic . '/' . $record->diastolic : '-' }}
                        </p>
                        <p class="text-[10px] text-gray-400">mmHg</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-3">
                        <p class="text-[10px] text-gray-500">Hemoglobin</p>
                        <p
                            class="text-base font-bold {{ $record->hb && $record->hb < 11 ? 'text-red-500' : 'text-gray-800' }}">
                            {{ $record->hb ?? '-' }}
                        </p>
                        <p class="text-[10px] text-gray-400">g/dL</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-3">
                        <p class="text-[10px] text-gray-500">DJJ</p>
                        <p class="text-base font-bold text-gray-800">{{ $record->fetal_heart_rate ?? '-' }}</p>
                        <p class="text-[10px] text-gray-400">bpm</p>
                    </div>
                    <div class="bg-blue-50 rounded-2xl p-3">
                        <p class="text-[10px] text-gray-500">Status TT</p>
                        <p
                            class="text-sm font-bold {{ $record->tt_status === 'done' ? 'text-green-600' : 'text-gray-800' }}">
                            {{ match ($record->tt_status) {
                                'done' => 'Sudah',
                                'not_done' => 'Belum',
                                default => '-',
                            } }}
                        </p>
                    </div>
                </div>
                <div class="bg-blue-50 rounded-2xl p-3">
                    <p class="text-[10px] text-gray-500">Letak Janin</p>
                    <p class="text-sm font-bold text-gray-800">
                        {{ match ($record->fetal_position) {
                            'head_down' => 'Kepala (Normal)',
                            'transverse' => 'Lintang',
                            'breech' => 'Sungsang',
                            default => '-',
                        } }}
                    </p>
                </div>
            </div>

            {{-- USG --}}
            @if ($record->usgImage)
                <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                    <h3 class="text-sm font-bold text-gray-700">Foto USG</h3>
                    <img src="{{ asset('storage/' . $record->usgImage->image_url) }}"
                        class="w-full rounded-2xl object-cover" alt="USG">
                </div>
            @endif

            {{-- Kondisi terdeteksi --}}
            @if ($record->detectedConditions->isNotEmpty())
                <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-5 space-y-2">
                    <h3 class="text-sm font-bold text-yellow-700">
                        <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                        Kondisi Terdeteksi
                    </h3>
                    @foreach ($record->detectedConditions as $condition)
                        <div class="flex justify-between items-center text-xs">
                            <span class="font-semibold text-gray-700">{{ $condition->label }}</span>
                            <span
                                class="px-2 py-0.5 rounded-full font-semibold
                            {{ $condition->status === 'confirmed'
                                ? 'bg-red-100 text-red-600'
                                : ($condition->status === 'rejected'
                                    ? 'bg-green-100 text-green-600'
                                    : 'bg-yellow-100 text-yellow-600') }}">
                                {{ $condition->status_label }}
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif

            {{-- Validasi bidan --}}
            @if ($record->validation)
                <div class="bg-green-50 border border-green-200 rounded-3xl p-5 space-y-1">
                    <p class="text-xs font-bold text-green-600">
                        <i class="fa-solid fa-circle-check mr-1"></i>
                        Divalidasi oleh {{ $record->validation->midwife->name }}
                    </p>
                    @if ($record->validation->notes)
                        <p class="text-xs text-gray-600 leading-relaxed">{{ $record->validation->notes }}</p>
                    @endif
                    <p class="text-[10px] text-gray-400">
                        {{ \Carbon\Carbon::parse($record->validation->locked_at)->translatedFormat('d F Y, H:i') }} WIB
                    </p>
                </div>
            @endif

            {{-- Tombol edit jika belum dikunci --}}
            @if (!$record->is_locked)
                <a href="{{ route('mother.anc.form', $schedule) }}"
                    class="block w-full text-center bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                    Edit Data ANC
                </a>
            @endif
        @else
            {{-- Belum ada data --}}
            <div class="text-center py-8 space-y-3">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <i class="fa-solid fa-clipboard text-gray-400 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700">Belum ada data ANC</p>
                <a href="{{ route('mother.anc.form', $schedule) }}"
                    class="inline-block bg-rose-500 text-white text-sm font-bold px-6 py-3 rounded-xl shadow-lg shadow-rose-500/30">
                    Input Data ANC
                </a>
            </div>
        @endif

    </main>

@endsection
