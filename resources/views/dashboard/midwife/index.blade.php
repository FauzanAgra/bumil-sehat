@extends('layouts.midwife')

@section('title', 'BumilSehat - Dashboard Bidan')

@section('content')

    {{-- HERO HEADER --}}
    <header class="bg-linear-to-br from-rose-500 to-rose-700 px-6 pt-6 pb-8 relative overflow-hidden shrink-0">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-4 -top-4 w-20 h-20 bg-white/5 rounded-full blur-xl"></div>

        <div class="relative z-10 flex justify-between items-start mb-6">
            <div>
                <p class="text-rose-100 text-[11px] font-medium">{{ $greeting }},</p>
                <h2 class="text-xl font-bold text-white mt-0.5">Bidan {{ Str::before($user->name, ' ') }} 👩‍⚕️</h2>
                <span class="inline-block mt-1.5 text-[10px] bg-white/30 text-white px-3 py-1 rounded-full font-semibold">
                    {{ $areaLabel }}
                </span>
            </div>
            <a href="{{ route('midwife.profile.setting') }}"
                class="w-11 h-11 rounded-full border-2 border-white/40 p-0.5 shrink-0">
                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}" class="rounded-full"
                    alt="avatar">
            </a>
        </div>

        {{-- Stat strip --}}
        <div class="relative z-10 grid grid-cols-4 gap-2">
            <a href="{{ route('midwife.patients') }}"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $totalMothers }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5 leading-tight">Ibu<br>Dampingan</p>
            </a>
            <a href="{{ route('midwife.schedules') }}"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $todaySchedules->count() }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5 leading-tight">ANC<br>Hari Ini</p>
            </a>
            <a href="#validasiSection"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $pendingValidation }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5 leading-tight">Menunggu<br>Validasi</p>
            </a>
            <a href="#kondisiSection"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $riskCount }}</h3>
                <p class="text-[9px] text-rose-100 font-medium mt-0.5 leading-tight">Kondisi<br>Risiko</p>
            </a>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-28 space-y-6 pt-6">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Jadwal Hari Ini --}}
        <section class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">Jadwal Hari Ini</h4>
                <a href="{{ route('midwife.schedules') }}"
                    class="text-[11px] text-rose-500 font-semibold hover:underline">Lihat Semua →</a>
            </div>

            @forelse ($todaySchedules as $schedule)
                @php
                    $record = $schedule->ancRecord;
                    $hasPending = $record && !$record->is_locked && !$record->validation;
                    $hasRisk = $record?->detectedConditions->where('status', 'detected')->isNotEmpty();

                    if ($hasRisk) {
                        $borderClass = 'border-red-100';
                        $iconBg = 'from-red-100 to-red-200';
                        $iconColor = 'text-red-500';
                        $icon = 'fa-heart-circle-exclamation';
                        $badgeClass = 'bg-red-50 text-red-500';
                        $badgeText = 'Tinjau →';
                        $link = route('midwife.anc.validate', $record->id);
                    } elseif ($hasPending) {
                        $borderClass = 'border-yellow-200';
                        $iconBg = 'from-yellow-100 to-yellow-200';
                        $iconColor = 'text-yellow-600';
                        $icon = 'fa-clipboard-check';
                        $badgeClass = 'bg-yellow-400 text-white shadow-sm';
                        $badgeText = 'Validasi →';
                        $link = route('midwife.anc.validate', $record->id);
                    } else {
                        $borderClass = 'border-gray-100';
                        $iconBg = 'from-blue-100 to-blue-200';
                        $iconColor = 'text-blue-500';
                        $icon = 'fa-calendar-check';
                        $badgeClass = 'bg-blue-50 text-blue-600';
                        $badgeText = 'Detail →';
                        $link = route('midwife.patients.show', $schedule->pregnancy->mother_id);
                    }
                @endphp

                <a href="{{ $link }}"
                    class="bg-white rounded-4xl p-4 border {{ $borderClass }} shadow-sm flex items-center gap-3 hover:shadow-md transition active:scale-[0.98] block">
                    <div
                        class="w-12 h-12 bg-linear-to-tr {{ $iconBg }} rounded-2xl flex items-center justify-center {{ $iconColor }} shrink-0">
                        <i class="fa-solid {{ $icon }}"></i>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-semibold text-gray-800">
                            {{ $schedule->pregnancy->mother->user->name }}
                        </p>
                        <p class="text-xs text-gray-400 mt-0.5">
                            ANC ke-{{ $schedule->anc_number }} &nbsp;·&nbsp;
                            @if ($schedule->schedule_time)
                                {{ \Carbon\Carbon::parse($schedule->schedule_time)->format('H:i') }} WIB
                            @else
                                Belum ada jam
                            @endif
                        </p>
                    </div>
                    <span class="text-[11px] {{ $badgeClass }} font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap">
                        {{ $badgeText }}
                    </span>
                </a>
            @empty
                <div class="bg-white rounded-3xl p-6 border border-gray-100 text-center space-y-2">
                    <i class="fa-regular fa-calendar-check text-gray-300 text-2xl"></i>
                    <p class="text-sm text-gray-500">Tidak ada jadwal ANC hari ini</p>
                </div>
            @endforelse
        </section>

        {{-- Ibu Dampingan --}}
        <section class="space-y-3" id="validasiSection">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">Ibu Dampingan</h4>
                <a href="{{ route('midwife.patients') }}"
                    class="text-[11px] text-rose-500 font-semibold hover:underline">Lihat Semua →</a>
            </div>

            @forelse ($motherPreview as $mother)
                @php
                    $flag = $mother->status_flag ?? 'normal';
                    $borderClass = match ($flag) {
                        'risiko' => 'border-red-100',
                        'validasi' => 'border-yellow-100',
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
                    class="bg-white rounded-4xl p-4 border {{ $borderClass }} shadow-sm flex items-center gap-3 hover:shadow-md transition active:scale-[0.98] block">
                    <div class="w-10 h-10 rounded-full border-2 {{ $avatarBorder }} p-0.5 shrink-0">
                        <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $mother->user->name }}"
                            class="rounded-full" alt="{{ $mother->user->name }}">
                    </div>
                    <div class="flex-1 min-w-0">
                        <h5 class="text-sm font-bold text-gray-800">{{ $mother->user->name }}</h5>
                        <p class="text-xs text-gray-400">
                            {{ $mother->activePregnancy?->gestational_week ?? '—' }} minggu
                        </p>
                    </div>
                    <span class="text-[10px] px-2.5 py-1 rounded-full {{ $badgeClass }} font-semibold whitespace-nowrap">
                        {{ $badgeText }}
                    </span>
                </a>
            @empty
                <div class="bg-white rounded-3xl p-6 border border-gray-100 text-center">
                    <p class="text-sm text-gray-500">Belum ada ibu dampingan</p>
                </div>
            @endforelse
        </section>

        {{-- Aksi Cepat --}}
        <section class="space-y-3">
            <h4 class="text-sm font-bold text-gray-700 px-1">Aksi Cepat</h4>
            <a href="{{ route('midwife.schedules.create') }}"
                class="flex items-center justify-center gap-3 bg-rose-500 text-white py-4 rounded-2xl text-sm font-semibold shadow-md shadow-rose-200 hover:bg-rose-600 active:scale-[0.97] transition">
                <i class="fa-solid fa-calendar-plus text-base"></i>
                <span>+ Jadwal ANC</span>
            </a>
        </section>

        {{-- Ringkasan Kondisi --}}
        <section class="space-y-3" id="kondisiSection">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">Ringkasan Kondisi Terdeteksi</h4>
                <a href="{{ route('midwife.patients') }}?filter=risiko"
                    class="text-[11px] text-rose-500 font-semibold hover:underline">Lihat Ibu →</a>
            </div>

            <div class="bg-white border border-gray-100 rounded-[2.5rem] p-5 shadow-sm space-y-3">

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-weight-scale text-orange-400 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-700">KEK</p>
                        <p class="text-[10px] text-gray-400">LILA &lt; 23,5 cm</p>
                    </div>
                    <span
                        class="text-[10px] px-2 py-1 rounded-full {{ $conditionSummary['kek'] > 0 ? 'bg-orange-50 text-orange-500' : 'bg-gray-50 text-gray-300' }} font-semibold">
                        {{ $conditionSummary['kek'] > 0 ? $conditionSummary['kek'] . ' ibu' : '–' }}
                    </span>
                </div>

                <div class="h-px bg-gray-100"></div>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-heart-pulse text-red-400 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p class="text-xs font-semibold text-gray-700">Preeklamsia</p>
                        <p class="text-[10px] text-gray-400">TD ≥ 140 / 90</p>
                    </div>
                    <span
                        class="text-[10px] px-2 py-1 rounded-full {{ $conditionSummary['preeclampsia'] > 0 ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-300' }} font-semibold">
                        {{ $conditionSummary['preeclampsia'] > 0 ? $conditionSummary['preeclampsia'] . ' ibu' : '–' }}
                    </span>
                </div>

                <div class="h-px bg-gray-100"></div>

                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-droplet text-blue-300 text-sm"></i>
                    </div>
                    <div class="flex-1">
                        <p
                            class="text-xs font-semibold {{ $conditionSummary['anemia'] > 0 ? 'text-gray-700' : 'text-gray-400' }}">
                            Anemia</p>
                        <p class="text-[10px] text-gray-300">HB &lt; 11 g/dL</p>
                    </div>
                    <span
                        class="text-[10px] px-2 py-1 rounded-full {{ $conditionSummary['anemia'] > 0 ? 'bg-blue-50 text-blue-500' : 'bg-gray-50 text-gray-300' }} font-semibold">
                        {{ $conditionSummary['anemia'] > 0 ? $conditionSummary['anemia'] . ' ibu' : '–' }}
                    </span>
                </div>

            </div>
        </section>

    </main>

@endsection
