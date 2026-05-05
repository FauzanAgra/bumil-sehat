@extends('layouts.mother')

@section('title', 'BumilSehat - Riwayat ANC')

@section('content')

    <style>
        .card-accent-1 {
            border-left: 3px solid #f43f5e;
        }

        .card-accent-2 {
            border-left: 3px solid #fb7185;
        }

        .card-accent-3 {
            border-left: 3px solid #fda4af;
        }

        .card-accent-4 {
            border-left: 3px solid #3b82f6;
        }

        .card-accent-5 {
            border-left: 3px solid #8b5cf6;
        }

        .card-accent-6 {
            border-left: 3px solid #10b981;
        }

        .card-accent-missed {
            border-left: 3px solid #fca5a5;
        }

        .anc-dot-active {
            background: #f43f5e;
            box-shadow: 0 0 0 3px #fecdd3;
        }

        .anc-dot-done {
            background: #f43f5e;
        }

        .anc-dot-missed {
            background: #fca5a5;
        }

        .anc-dot-empty {
            background: #e5e7eb;
        }

        .fade-up {
            animation: fadeUp 0.4s ease forwards;
            opacity: 0;
        }

        @keyframes fadeUp {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .delay-1 {
            animation-delay: 0.05s;
        }

        .delay-2 {
            animation-delay: 0.10s;
        }

        .delay-3 {
            animation-delay: 0.15s;
        }
    </style>

    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur-sm z-40">
        <a href="{{ route('mother.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 hover:text-rose-500 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Riwayat Pemeriksaan</h1>
            <p class="text-xs text-gray-400">Ringkasan kunjungan ANC Bunda</p>
        </div>
    </header>

    <main class="px-6 py-5 space-y-6 pb-32">

        {{-- PROGRESS DOTS --}}
        @php
            $completedCount = $schedules->where('status', 'done')->count();
            $nextAnc = $schedules->where('status', 'scheduled')->sortBy('anc_number')->first();
            $nextAncNumber = $nextAnc?->anc_number;
        @endphp

        <div class="bg-white border border-gray-100 rounded-3xl px-5 py-4 shadow-sm fade-up">
            <div class="flex justify-between items-center mb-3">
                <p class="text-xs font-bold text-gray-500 uppercase tracking-wide">Progress ANC Bunda</p>
                <span class="text-xs font-bold text-rose-500 bg-rose-50 px-2.5 py-1 rounded-full">
                    {{ $completedCount }} / 6 Selesai
                </span>
            </div>
            <div class="flex items-center gap-2">
                @for ($i = 1; $i <= 6; $i++)
                    @php
                        $s = $schedules->firstWhere('anc_number', $i);
                        $isDone = $s?->status === 'done';
                        $isMissed = $s?->status === 'missed';
                        $isScheduled = $s?->status === 'scheduled';
                        $isNext = $nextAncNumber === $i;
                    @endphp

                    <div class="flex flex-col items-center gap-1.5 flex-1">
                        @if ($isDone)
                            <div class="w-7 h-7 rounded-full anc-dot-done flex items-center justify-center">
                                <i class="fa-solid fa-check text-white text-[9px]"></i>
                            </div>
                            <span class="text-[10px] text-rose-500 font-semibold">ANC {{ $i }}</span>
                        @elseif ($isMissed)
                            <div class="w-7 h-7 rounded-full anc-dot-missed flex items-center justify-center">
                                <i class="fa-solid fa-minus text-white text-[9px]"></i>
                            </div>
                            <span class="text-[10px] text-red-400 font-semibold">ANC {{ $i }}</span>
                        @elseif ($isNext)
                            <div class="w-7 h-7 rounded-full anc-dot-active flex items-center justify-center">
                                <span class="text-white text-[10px] font-bold">{{ $i }}</span>
                            </div>
                            <span class="text-[10px] text-rose-400 font-semibold">ANC {{ $i }}</span>
                        @else
                            <div class="w-7 h-7 rounded-full anc-dot-empty flex items-center justify-center">
                                <span class="text-gray-400 text-[10px] font-bold">{{ $i }}</span>
                            </div>
                            <span class="text-[10px] text-gray-400 font-semibold">ANC {{ $i }}</span>
                        @endif
                    </div>

                    @if ($i < 6)
                        @php
                            $connectorColor = $isDone || $isMissed ? 'bg-rose-200' : 'bg-gray-200';
                            if ($isMissed) {
                                $connectorColor = 'bg-gray-200';
                            }
                        @endphp
                        <div class="flex-1 h-0.5 {{ $connectorColor }} -mt-4"></div>
                    @endif
                @endfor
            </div>
        </div>

        {{-- JADWAL TERDEKAT --}}
        @if ($nextSchedule)
            <section class="space-y-3 fade-up delay-1">
                <p class="text-xs font-semibold text-gray-400 uppercase tracking-wide px-1">Jadwal Terdekat</p>

                <div
                    class="bg-linear-to-r from-blue-500 to-blue-600 rounded-4xl p-5 text-white shadow-lg shadow-blue-200 flex items-center gap-4">
                    <div
                        class="bg-white/25 backdrop-blur-md w-14 h-14 rounded-2xl flex flex-col items-center justify-center font-bold shrink-0">
                        <span class="text-xs uppercase tracking-wide opacity-90">
                            {{ \Carbon\Carbon::parse($nextSchedule->schedule_date)->translatedFormat('D') }}
                        </span>
                        <span class="text-2xl leading-none">
                            {{ \Carbon\Carbon::parse($nextSchedule->schedule_date)->format('d') }}
                        </span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <h5 class="font-semibold text-sm leading-snug">
                            Pemeriksaan Ke-{{ $nextSchedule->anc_number }}
                        </h5>
                        @if ($nextSchedule->note)
                            <p class="text-xs flex items-center gap-1 opacity-90">
                                <i class="fa-solid fa-location-dot text-[11px]"></i>
                                <span>{{ $nextSchedule->note }}</span>
                            </p>
                        @endif
                        <p class="text-xs flex items-center gap-1 opacity-90" id="scheduleTimeLabel">
                            <i class="fa-regular fa-clock text-[11px]"></i>
                            <span>
                                @if (\Carbon\Carbon::parse($nextSchedule->schedule_date)->isToday())
                                    Hari ini
                                @elseif (\Carbon\Carbon::parse($nextSchedule->schedule_date)->isTomorrow())
                                    Besok
                                @else
                                    {{ \Carbon\Carbon::parse($nextSchedule->schedule_date)->translatedFormat('l, d F Y') }}
                                @endif
                                @if ($nextSchedule->schedule_time)
                                    · {{ \Carbon\Carbon::parse($nextSchedule->schedule_time)->format('H:i') }} WIB
                                @endif
                            </span>
                        </p>
                    </div>

                    @if (\Carbon\Carbon::parse($nextSchedule->schedule_date)->isToday())
                        <a href="{{ route('mother.anc.form', $nextSchedule) }}"
                            class="w-9 h-9 bg-white/25 rounded-full flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </a>
                    @else
                        <div
                            class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center shrink-0 opacity-40 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </div>
                    @endif
                </div>

                @if (!\Carbon\Carbon::parse($nextSchedule->schedule_date)->isToday())
                    <p
                        class="text-[11px] text-gray-500 text-center bg-blue-50 border border-blue-100 rounded-2xl px-4 py-3 leading-relaxed">
                        Form input ANC akan terbuka pada <strong>hari jadwal</strong>.
                    </p>
                @endif

                <p class="text-[11px] text-gray-500 text-center">
                    @if (\Carbon\Carbon::parse($nextSchedule->schedule_date)->isToday())
                        Semoga pemeriksaannya berjalan lancar ya, Bunda 🤍
                    @else
                        Persiapkan diri ya, Bunda 🤍
                    @endif
                </p>
            </section>
        @endif

        {{-- DIVIDER RIWAYAT SELESAI --}}
        @php
            $doneOrMissed = $schedules->whereIn('status', ['done', 'missed'])->sortByDesc('anc_number');
        @endphp

        @if ($doneOrMissed->isNotEmpty())
            <div class="flex items-center gap-3">
                <div class="flex-1 h-px bg-gray-100"></div>
                <span class="text-xs font-semibold text-gray-400 uppercase tracking-wide">Riwayat Selesai</span>
                <div class="flex-1 h-px bg-gray-100"></div>
            </div>

            <section class="space-y-3 fade-up delay-2">
                @foreach ($doneOrMissed as $schedule)
                    @php
                        $isMissed = $schedule->status === 'missed';
                        $record = $schedule->ancRecord;
                        $accentClass = $isMissed ? 'card-accent-missed' : 'card-accent-' . $schedule->anc_number;
                        $numBgClass = $isMissed ? 'bg-gray-100' : 'bg-rose-50';
                        $numTextClass = $isMissed ? 'text-gray-400' : 'text-rose-500';
                        $numLabelClass = $isMissed ? 'text-gray-400' : 'text-rose-400';
                    @endphp

                    <div
                        class="bg-white rounded-[2.5rem] p-5 shadow-sm border border-gray-100 {{ $accentClass }} overflow-hidden">
                        <div class="flex items-start gap-4">
                            <div
                                class="w-11 h-11 rounded-2xl {{ $numBgClass }} flex flex-col items-center justify-center shrink-0">
                                <span class="text-[9px] {{ $numLabelClass }} font-bold uppercase">ANC</span>
                                <span
                                    class="text-base font-bold {{ $numTextClass }} leading-none">{{ $schedule->anc_number }}</span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex justify-between items-start gap-2">
                                    <div>
                                        <h3 class="text-sm font-bold text-gray-800">
                                            {{ $schedule->anc_number === 1 ? 'Pemeriksaan Pertama' : 'Pemeriksaan Ke-' . $schedule->anc_number }}
                                        </h3>
                                        <p class="text-xs text-gray-400 mt-0.5">
                                            @if ($isMissed)
                                                Sebelum bergabung
                                            @else
                                                {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('l, d F Y') }}
                                            @endif
                                        </p>
                                    </div>

                                    @if ($isMissed)
                                        <span
                                            class="shrink-0 text-[10px] px-2 py-0.5 rounded-full bg-red-50 text-red-400 font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-minus text-[9px]"></i> Terlewat
                                        </span>
                                    @else
                                        <span
                                            class="shrink-0 text-[10px] px-2 py-0.5 rounded-full bg-green-50 text-green-600 font-semibold flex items-center gap-1">
                                            <i class="fa-solid fa-circle-check text-[9px]"></i> Selesai
                                        </span>
                                    @endif
                                </div>

                                @if ($isMissed)
                                    <p class="text-xs text-gray-400 leading-relaxed mt-2 italic">
                                        Pemeriksaan ini terlewat sebelum Bunda bergabung di BumilSehat.
                                    </p>
                                @else
                                    {{-- USG badge --}}
                                    @if ($record?->usgImage)
                                        <div
                                            class="mt-2 inline-flex items-center gap-1.5 bg-blue-50 text-blue-600 text-[10px] font-semibold px-2.5 py-1 rounded-full">
                                            <i class="fa-solid fa-image text-[9px]"></i>
                                            Termasuk hasil USG
                                        </div>
                                    @endif

                                    <div class="mt-2">
                                        <a href="{{ route('mother.anc.detail', $schedule) }}"
                                            class="inline-flex items-center gap-1 text-xs text-rose-500 font-semibold hover:underline">
                                            Lihat detail <i class="fa-solid fa-chevron-right text-[10px]"></i>
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </section>
        @endif

        {{-- BELUM TERJADWAL --}}
        @php
            $notScheduled = collect(range(1, 6))->filter(function ($i) use ($schedules) {
                return !$schedules->firstWhere('anc_number', $i);
            });
        @endphp

        @if ($notScheduled->isNotEmpty())
            <section class="space-y-3 fade-up delay-3">
                <div class="flex items-center gap-3">
                    <div class="flex-1 h-px bg-gray-100"></div>
                    <span class="text-xs font-semibold text-gray-300 uppercase tracking-wide">Belum Terjadwal</span>
                    <div class="flex-1 h-px bg-gray-100"></div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    @foreach ($notScheduled as $i)
                        <div
                            class="flex-1 bg-gray-50 border border-dashed border-gray-200 rounded-3xl p-4 text-center space-y-1.5">
                            <div class="w-8 h-8 rounded-xl bg-gray-100 flex items-center justify-center mx-auto">
                                <span class="text-sm font-bold text-gray-400">{{ $i }}</span>
                            </div>
                            <p class="text-[12px] font-semibold text-gray-400">ANC Ke-{{ $i }}</p>
                            @if (in_array($i, [1, 5]))
                                <p class="text-[11px] text-gray-300 leading-tight">Termasuk USG</p>
                            @elseif ($i === 6)
                                <p class="text-[11px] text-gray-300 leading-tight">Pemeriksaan akhir</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

@endsection
