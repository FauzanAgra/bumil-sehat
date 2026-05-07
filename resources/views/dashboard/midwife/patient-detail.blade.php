@extends('layouts.midwife')

@section('title', 'BumilSehat - Detail Pasien')

@section('content')

    <header class="bg-linear-to-br from-rose-500 to-rose-700 px-6 pt-6 pb-8 relative overflow-hidden shrink-0">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="relative z-10 flex justify-between items-start mb-4">
            <a href="{{ route('midwife.patients') }}"
                class="w-9 h-9 bg-white/20 rounded-full flex items-center justify-center text-white hover:bg-white/30 transition">
                <i class="fa-solid fa-arrow-left text-sm"></i>
            </a>
            <span class="text-[10px] bg-white/30 text-white px-3 py-1 rounded-full font-semibold">
                Detail Pasien
            </span>
        </div>
        <div class="relative z-10 flex items-center gap-4">
            <div class="w-16 h-16 rounded-2xl border-2 border-white/40 p-0.5 shrink-0">
                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $motherProfile->user->name }}"
                    class="rounded-xl w-full h-full" alt="{{ $motherProfile->user->name }}">
            </div>
            <div>
                <h2 class="text-lg font-bold text-white">{{ $motherProfile->user->name }}</h2>
                <p class="text-rose-100 text-xs mt-0.5">
                    {{ $pregnancy?->gestational_week ?? '—' }} minggu
                    · Trimester {{ $pregnancy?->trimester ?? '—' }}
                </p>
                @if ($pregnancy)
                    <span class="inline-block mt-1 text-[10px] bg-white/30 text-white px-2 py-0.5 rounded-full">
                        HPL: {{ \Carbon\Carbon::parse($pregnancy->hpl_date)->translatedFormat('d M Y') }}
                    </span>
                @endif
            </div>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-28 pt-5 space-y-5">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Aksi cepat --}}
        <div class="flex gap-3">
            <a href="{{ route('midwife.schedules.create', ['mother_id' => $motherProfile->id]) }}"
                class="flex-1 flex items-center justify-center gap-2 bg-rose-500 text-white py-3 rounded-2xl text-xs font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 transition">
                <i class="fa-solid fa-calendar-plus"></i> Buat Jadwal
            </a>
            @if ($motherProfile->user->phone)
                <a href="https://wa.me/62{{ ltrim($motherProfile->user->phone, '0') }}" target="_blank"
                    class="flex-1 flex items-center justify-center gap-2 bg-green-500 text-white py-3 rounded-2xl text-xs font-semibold shadow-md shadow-green-200 hover:bg-green-600 transition">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp
                </a>
            @endif
        </div>

        {{-- Data ibu --}}
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-gray-700">Data Ibu</h3>
            <div class="grid grid-cols-2 gap-3 text-xs">
                <div>
                    <p class="text-gray-400">Kelurahan</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $motherProfile->subdistrict->name }}</p>
                </div>
                <div>
                    <p class="text-gray-400">RW / RT</p>
                    <p class="font-semibold text-gray-700 mt-0.5">
                        {{ $motherProfile->area->rw_number }} / {{ $motherProfile->rt ?? '—' }}
                    </p>
                </div>
                <div>
                    <p class="text-gray-400">No. HP</p>
                    <p class="font-semibold text-gray-700 mt-0.5">{{ $motherProfile->user->phone }}</p>
                </div>
                <div>
                    <p class="text-gray-400">Tanggal Lahir</p>
                    <p class="font-semibold text-gray-700 mt-0.5">
                        {{ $motherProfile->birth_date->translatedFormat('d M Y') }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Timeline ANC --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Timeline ANC</h3>

            @if ($pregnancy)
                @forelse ($pregnancy->ancSchedules as $schedule)
                    @php
                        $record = $schedule->ancRecord;
                        $hasPending = $record && !$record->is_locked && !$record->validation;
                        $hasRisk = $record?->detectedConditions->where('status', 'detected')->isNotEmpty();
                        $isLocked = $record?->is_locked;

                        $statusColor = match ($schedule->status) {
                            'done' => 'bg-green-500',
                            'scheduled' => 'bg-blue-500',
                            'missed' => 'bg-red-300',
                            default => 'bg-gray-300',
                        };
                    @endphp

                    <div class="bg-white border border-gray-100 rounded-3xl p-4 shadow-sm">
                        <div class="flex items-start gap-3">
                            <div
                                class="w-10 h-10 {{ $statusColor }} rounded-2xl flex flex-col items-center justify-center text-white shrink-0">
                                <span class="text-[8px] font-medium uppercase">ANC</span>
                                <span class="text-base font-bold leading-none">{{ $schedule->anc_number }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <p class="text-sm font-bold text-gray-800">
                                            Pemeriksaan Ke-{{ $schedule->anc_number }}
                                        </p>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d F Y') }}
                                        </p>
                                    </div>
                                    @if ($hasRisk)
                                        <span
                                            class="text-[10px] bg-red-50 text-red-500 font-semibold px-2 py-0.5 rounded-full whitespace-nowrap">⚠️
                                            Risiko</span>
                                    @elseif ($hasPending)
                                        <span
                                            class="text-[10px] bg-yellow-50 text-yellow-600 font-semibold px-2 py-0.5 rounded-full whitespace-nowrap">⏳
                                            Validasi</span>
                                    @elseif ($isLocked)
                                        <span
                                            class="text-[10px] bg-green-50 text-green-600 font-semibold px-2 py-0.5 rounded-full whitespace-nowrap">✔
                                            Selesai</span>
                                    @endif
                                </div>

                                @if ($record && ($hasPending || $hasRisk))
                                    <a href="{{ route('midwife.anc.validate', $record->id) }}"
                                        class="mt-2 inline-flex items-center gap-1 text-xs text-rose-500 font-semibold hover:underline">
                                        Validasi data →
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6">
                        <p class="text-sm text-gray-500">Belum ada jadwal ANC</p>
                    </div>
                @endforelse
            @else
                <div class="text-center py-6">
                    <p class="text-sm text-gray-500">Tidak ada kehamilan aktif</p>
                </div>
            @endif
        </section>

    </main>

@endsection
