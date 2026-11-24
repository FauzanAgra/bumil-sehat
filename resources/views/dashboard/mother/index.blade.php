@extends('layouts.mother')

@section('title', 'BumilSehat - Dashboard')

@section('content')

    {{-- HEADER --}}
    <header class="shrink-0">
        <div class="px-6 py-5 flex justify-between items-center">
            <div>
                <p class="text-[11px] text-gray-400 font-medium">{{ $greeting }},</p>
                <h2 class="text-lg font-bold text-gray-700">
                    Bunda {{ $user->name }} ✨
                </h2>
            </div>
            <a href="{{ route('mother.setting') }}" class="w-10 h-10 rounded-full border-2 border-rose-300 p-0.5">
                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}" class="rounded-full"
                    alt="avatar">
            </a>
        </div>
    </header>

    {{-- MAIN CONTENT --}}
    <main class="flex-1 overflow-y-auto px-6 pb-28 space-y-6">

        {{-- 1. Kartu Usia Janin --}}
        @if ($pregnancy)
            <div
                class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2.5rem] p-7 text-white shadow-xl shadow-rose-200 relative overflow-hidden">
                <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                <div class="relative z-10">
                    <p class="text-rose-100 text-sm font-medium">Usia Janin Bunda</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <h3 class="text-3xl font-bold">{{ $gestationalWeek }}</h3>
                        <span class="text-lg font-semibold opacity-90">Minggu</span>
                    </div>
                    @if ($daysUntilNextWeek > 0)
                        <p class="text-rose-100 text-xs mt-1">
                            {{ $daysUntilNextWeek }} hari lagi menuju minggu ke-{{ $gestationalWeek + 1 }}!
                        </p>
                    @endif
                    <span class="inline-block mt-2 text-[10px] bg-white/30 text-white px-3 py-1 rounded-full">
                        {{ $pregnancy->status === 'active' ? 'Kehamilan Aktif' : 'Kehamilan Selesai' }}
                    </span>
                </div>
            </div>
        @else
            <div class="bg-rose-50 border border-rose-200 rounded-[2.5rem] p-7 text-center">
                <p class="text-rose-500 font-semibold">Belum ada data kehamilan aktif.</p>
            </div>
        @endif

        {{-- Alert kondisi risiko --}}
        @if ($pendingConditions)
            <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-4 flex gap-3">
                <div class="text-yellow-500 mt-0.5">
                    <i class="fa-solid fa-heart-circle-exclamation"></i>
                </div>
                <div>
                    <p class="text-sm font-semibold text-gray-800">Ada hal yang perlu diperhatikan 🤍</p>
                    <p class="text-xs text-gray-600 leading-relaxed mt-1">
                        Beberapa hasil pemeriksaan terakhir akan dibahas bersama Bidan
                        pada kunjungan berikutnya. Bunda tidak sendirian, pendampingan tetap berjalan.
                    </p>
                </div>
            </div>
        @endif

        {{-- 2. Bidan Pendamping --}}
        <div class="bg-white border-2 border-rose-50 rounded-4xl p-5 shadow-sm flex items-center gap-4">
            <div
                class="w-14 h-14 bg-linear-to-tr from-rose-100 to-rose-200 rounded-2xl flex items-center justify-center text-rose-500 text-2xl shadow-inner">
                <i class="fa-solid fa-user-nurse"></i>
            </div>
            <div class="flex-1">
                <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest">Bidan Pendamping</p>
                @if ($midwife)
                    <h4 class="text-sm font-bold text-gray-700">{{ $midwife->name }}</h4>
                    <p class="text-xs text-gray-400">
                        {{ $midwife->medicalProfile?->availability ?? 'BumilSehat' }}
                    </p>
                @else
                    <h4 class="text-sm font-bold text-gray-700">Belum ada bidan</h4>
                    <p class="text-xs text-gray-400">Bidan akan ditentukan oleh Admin</p>
                @endif
            </div>
            @if ($midwife?->phone)
                <a href="https://wa.me/62{{ ltrim($midwife->phone, '0') }}" target="_blank"
                    class="w-12 h-12 bg-green-500 text-white rounded-2xl flex items-center justify-center shadow-lg shadow-green-100 hover:scale-110 transition-transform">
                    <i class="fa-brands fa-whatsapp text-2xl"></i>
                </a>
            @endif
        </div>

        {{-- 3. TTD --}}
        <section class="space-y-4">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-base font-bold text-gray-700">
                    Pemantauan Tablet <br>Tambah Darah
                </h4>
                <div class="bg-rose-50 text-rose-500 px-3 py-1 rounded-full text-[9px] font-bold">
                    90 TABLET TARGET
                </div>
            </div>

            @if ($ttdStatus?->is_active)
                <div class="bg-white border border-gray-100 rounded-[2.5rem] p-6 shadow-sm">
                    {{-- Grid 5 hari --}}
                    <div class="flex justify-between items-center gap-2 mb-6">
                        @php
                            $days = collect();
                            for ($i = -2; $i <= 2; $i++) {
                                $days->push(now()->addDays($i));
                            }

                            $ttdLogsByDate = collect($ttdLogs)->keyBy(
                                fn($l) => \Carbon\Carbon::parse(is_object($l) ? $l->date : $l['date'])->toDateString(),
                            );
                        @endphp

                        @foreach ($days as $day)
                            @php
                                $dateStr = $day->toDateString();
                                $log = $ttdLogsByDate[$dateStr] ?? null;
                                $isToday = $day->isToday();
                                $isFuture = $day->isFuture();
                                $isTaken = $log?->is_taken;
                            @endphp

                            @if ($isToday)
                                <div id="today-box"
                                    class="flex-[1.3] aspect-square rounded-2xl flex flex-col items-center justify-center {{ $isTaken ? 'day-done' : 'day-today' }}">
                                    <span class="text-[8px] font-medium uppercase tracking-tight mb-1">Hari Ini</span>
                                    <span class="text-base font-semibold leading-none">{{ $day->format('d') }}</span>
                                    <div id="status-icon"
                                        class="mt-2 {{ $isTaken ? '' : 'w-2 h-2 bg-white rounded-full animate-ping' }}">
                                        @if ($isTaken)
                                            <i class="fa-solid fa-check text-xs"></i>
                                        @endif
                                    </div>
                                </div>
                            @elseif ($isTaken)
                                <div
                                    class="flex-1 aspect-square rounded-2xl flex flex-col items-center justify-center day-done text-[10px] font-semibold">
                                    <span
                                        class="opacity-60 text-[8px] font-medium">{{ strtoupper($day->translatedFormat('D')) }}</span>
                                    <span>{{ $day->format('d') }}</span>
                                    <i class="fa-solid fa-check mt-1"></i>
                                </div>
                            @elseif ($isFuture)
                                <div
                                    class="flex-1 aspect-square rounded-2xl border border-dashed border-gray-200 flex flex-col items-center justify-center text-gray-300 text-[10px] font-semibold">
                                    <span
                                        class="opacity-60 text-[8px] font-medium">{{ strtoupper($day->translatedFormat('D')) }}</span>
                                    <span>{{ $day->format('d') }}</span>
                                </div>
                            @else
                                <div
                                    class="flex-1 aspect-square rounded-2xl bg-gray-100 flex flex-col items-center justify-center text-gray-400 text-[10px] font-semibold">
                                    <span
                                        class="opacity-60 text-[8px] font-medium">{{ strtoupper($day->translatedFormat('D')) }}</span>
                                    <span>{{ $day->format('d') }}</span>
                                    <i class="fa-solid fa-minus mt-1 text-[8px]"></i>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    {{-- Action --}}
                    <div id="action-area" class="text-center">
                        @if ($takenToday)
                            <div
                                class="flex flex-col items-center justify-center p-3 bg-green-50 text-green-600 rounded-2xl border-2 border-green-200">
                                <div class="flex items-center gap-2 mb-1">
                                    <i class="fa-solid fa-circle-check"></i>
                                    <span class="text-xs font-bold uppercase tracking-wider">Sudah Minum Hari Ini!</span>
                                </div>
                                <p class="text-[10px]">Bunda hebat! Jangan lupa istirahat yang cukup ya.</p>
                            </div>
                        @else
                            <form method="POST" action="{{ route('mother.ttd.take') }}">
                                @csrf
                                <button type="submit"
                                    class="w-full bg-rose-500 text-white py-3.5 rounded-2xl text-[11px] font-semibold shadow-lg active:scale-95 transition-all flex items-center justify-center gap-2">
                                    <i class="fa-solid fa-pills text-rose-200 text-sm"></i>
                                    Sudah Minum Hari Ini
                                </button>
                            </form>
                            <p class="text-xs text-gray-400 mt-3 leading-relaxed">
                                Laporan ini membantu bidan mendampingi Bunda dengan lebih baik ❤️
                            </p>
                        @endif
                    </div>
                </div>
            @elseif ($gestationalWeek !== null && $gestationalWeek < 10)
                <div class="bg-blue-50 px-4 py-5 space-y-3 rounded-4xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <i class="fa-solid fa-baby text-blue-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Usia Kehamilan {{ $gestationalWeek }} Minggu</p>
                            <p class="text-xs text-gray-500">Fase awal kehamilan</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed text-center">
                        Tablet tambah darah <b>belum dianjurkan</b> pada fase ini,
                        kecuali atas saran tenaga kesehatan.
                    </p>
                    <div class="text-center">
                        <span
                            class="inline-flex items-center gap-1 text-[11px] font-medium text-blue-600 bg-white px-3 py-1 rounded-full">
                            <i class="fa-regular fa-clock"></i>
                            Rutin TTD dimulai saat ≥ 10 minggu
                        </span>
                    </div>
                </div>
            @elseif ($ttdStatus && ! $ttdStatus->is_active)
                <div class="bg-gray-50 border border-gray-200 px-4 py-5 space-y-3 rounded-4xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gray-200 flex items-center justify-center">
                            <i class="fa-solid fa-pills text-gray-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Usia Kehamilan {{ $gestationalWeek }} Minggu</p>
                            <p class="text-xs text-gray-500">Trimester {{ $trimester }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed text-center">
                        Pemantauan tablet tambah darah saat ini <b>sedang dinonaktifkan oleh Bidan</b>.
                        Hubungi Bidan pendamping jika Bunda memiliki pertanyaan.
                    </p>
                    <div class="text-center">
                        <span
                            class="inline-flex items-center gap-1 text-[11px] font-medium text-gray-600 bg-white px-3 py-1 rounded-full border border-gray-200">
                            <i class="fa-solid fa-pause"></i>
                            Dinonaktifkan oleh Bidan
                        </span>
                    </div>
                </div>
            @elseif ($pregnancy)
                <div class="bg-rose-50 border border-rose-100 px-4 py-5 space-y-3 rounded-4xl">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                            <i class="fa-solid fa-pills text-rose-500 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-sm font-bold text-gray-800">Usia Kehamilan {{ $gestationalWeek }} Minggu</p>
                            <p class="text-xs text-gray-500">Trimester {{ $trimester }}</p>
                        </div>
                    </div>
                    <p class="text-xs text-gray-600 leading-relaxed text-center">
                        Pemantauan tablet tambah darah akan <b>aktif otomatis</b> setelah kunjungan ANC Bunda
                        divalidasi oleh Bidan.
                    </p>
                    <div class="text-center">
                        <span
                            class="inline-flex items-center gap-1 text-[11px] font-medium text-rose-600 bg-white px-3 py-1 rounded-full">
                            <i class="fa-solid fa-user-nurse"></i>
                            Menunggu validasi ANC Bidan
                        </span>
                    </div>
                </div>
            @else
                <div class="bg-gray-50 px-4 py-5 rounded-4xl text-center">
                    <p class="text-xs text-gray-500">Belum ada kehamilan aktif untuk pemantauan TTD.</p>
                </div>
            @endif
        </section>

        {{-- 4. Jadwal ANC --}}
        <section class="space-y-4">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-base font-bold text-gray-700">Jadwal Kontrol ANC</h4>
                <a href="{{ route('mother.anc.history') }}"
                    class="text-[11px] text-rose-500 font-semibold hover:underline">
                    Lihat Semua
                </a>
            </div>

            @if ($nextSchedule)
                <div
                    class="bg-linear-to-r from-blue-500 to-blue-600 rounded-4xl p-5 text-white shadow-lg shadow-blue-200 flex items-center gap-4">
                    <div
                        class="bg-white/25 backdrop-blur-md w-14 h-14 rounded-2xl flex flex-col items-center justify-center font-bold">
                        <span class="text-xs uppercase tracking-wide opacity-90">
                            {{ Carbon\Carbon::parse($nextSchedule->schedule_date)->translatedFormat('D') }}
                        </span>
                        <span class="text-2xl leading-none">
                            {{ Carbon\Carbon::parse($nextSchedule->schedule_date)->format('d') }}
                        </span>
                    </div>
                    <div class="flex-1 space-y-1">
                        <span class="text-[10px] font-semibold text-blue-100 uppercase tracking-wider">Jadwal
                            Terdekat</span>
                        <h5 class="font-semibold text-sm leading-snug">
                            Pemeriksaan Ke-{{ $nextSchedule->anc_number }}
                        </h5>
                        @if ($nextSchedule->note)
                            <p class="text-xs flex items-center gap-1 opacity-90">
                                <i class="fa-solid fa-location-dot text-[11px]"></i>
                                <span>{{ $nextSchedule->note }}</span>
                            </p>
                        @endif
                        @if ($nextSchedule->schedule_time)
                            <p class="text-xs flex items-center gap-1 opacity-90">
                                <i class="fa-regular fa-clock text-[11px]"></i>
                                <span>{{ Carbon\Carbon::parse($nextSchedule->schedule_time)->format('H:i') }} WIB</span>
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
                        <a href="{{ route('mother.anc.form', $nextSchedule->id) }}"
                            class="w-9 h-9 bg-white/25 rounded-full flex items-center justify-center">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </a>
                    @else
                        <div
                            class="w-9 h-9 bg-white/10 rounded-full flex items-center justify-center shrink-0 opacity-40 cursor-not-allowed">
                            <i class="fa-solid fa-chevron-right text-sm"></i>
                        </div>
                    @endif

                </div>
            @else
                <div class="bg-white rounded-4xl p-6 shadow-sm border border-gray-100 text-center space-y-4">
                    <div class="mx-auto w-16 h-16 rounded-2xl bg-blue-50 flex items-center justify-center">
                        <i class="fa-regular fa-calendar text-blue-400 text-2xl"></i>
                    </div>
                    <div class="space-y-1">
                        <p class="text-sm font-semibold text-gray-700">Jadwal kontrol sedang disiapkan</p>
                        <p class="text-xs text-gray-500 leading-relaxed">
                            Jadwal pemeriksaan ANC akan diinformasikan oleh Bidan dan akan tampil di sini.
                        </p>
                    </div>
                    <div class="text-[11px] text-blue-600 font-medium bg-blue-50 inline-block px-3 py-1.5 rounded-full">
                        Pendampingan kehamilan tetap berjalan 🤍
                    </div>
                </div>
            @endif
        </section>

    </main>

@endsection
