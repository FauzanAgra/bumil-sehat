@extends('layouts.doctor')

@section('title', 'BumilSehat - Dashboard Dokter')

@section('content')

    {{-- HERO HEADER --}}
    <header class="bg-linear-to-br from-blue-500 to-blue-700 px-6 pt-6 pb-8 relative overflow-hidden shrink-0">
        <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
        <div class="absolute -left-4 -top-4 w-20 h-20 bg-white/5 rounded-full blur-xl"></div>

        <div class="relative z-10 flex justify-between items-start mb-6">
            <div>
                <p class="text-blue-100 text-[11px] font-medium">Selamat Bertugas,</p>
                <h2 class="text-xl font-bold text-white mt-0.5">
                    {{ trim(($user->medicalProfile?->degree_prefix ? $user->medicalProfile->degree_prefix . ' ' : '') . $user->name) }}
                    👨‍⚕️
                </h2>
                <span class="inline-block mt-1.5 text-[10px] bg-white/30 text-white px-3 py-1 rounded-full font-semibold">
                    {{ $user->medicalProfile?->specialization?->name ?? 'Dokter / Nutrisionis' }}
                </span>
            </div>
            <div class="w-11 h-11 rounded-full border-2 border-white/40 p-0.5 shrink-0">
                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}" class="rounded-full"
                    alt="avatar">
            </div>
        </div>

        {{-- Stat strip --}}
        <div class="relative z-10 grid grid-cols-3 gap-2">
            <a href="{{ route('doctor.patients') }}"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $totalMothers }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5 leading-tight">Total<br>Ibu Hamil</p>
            </a>
            <a href="#risikoSection"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $riskCount }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5 leading-tight">Kondisi<br>Risiko</p>
            </a>
            <a href="{{ route('doctor.patients') }}"
                class="bg-white/15 backdrop-blur-sm rounded-2xl px-2 py-3 text-center hover:bg-white/25 transition active:scale-95">
                <h3 class="text-xl font-bold text-white">{{ $ancThisWeek }}</h3>
                <p class="text-[9px] text-blue-100 font-medium mt-0.5 leading-tight">ANC<br>Minggu Ini</p>
            </a>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-28 space-y-6 pt-6">

        {{-- RINGKASAN IBU HAMIL --}}
        <section class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">Ringkasan Ibu Hamil</h4>
                <a href="{{ route('doctor.patients') }}" class="text-[11px] text-blue-500 font-semibold hover:underline">
                    Lihat Semua →
                </a>
            </div>
            <div class="grid grid-cols-3 gap-2">
                <div class="bg-green-50 rounded-2xl p-3 text-center">
                    <p class="text-lg font-bold text-green-600">{{ $normalCount }}</p>
                    <p class="text-[9px] text-green-500 font-semibold mt-0.5 leading-tight">Normal</p>
                </div>
                <div class="bg-yellow-50 rounded-2xl p-3 text-center">
                    <p class="text-lg font-bold text-yellow-600">{{ $pendingCount }}</p>
                    <p class="text-[9px] text-yellow-500 font-semibold mt-0.5 leading-tight">Validasi<br>Pending</p>
                </div>
                <div class="bg-red-50 rounded-2xl p-3 text-center">
                    <p class="text-lg font-bold text-red-500">{{ $riskCount }}</p>
                    <p class="text-[9px] text-red-400 font-semibold mt-0.5 leading-tight">Kondisi<br>Risiko</p>
                </div>
            </div>
        </section>

        {{-- KONDISI RISIKO AKTIF --}}
        <section class="space-y-3" id="risikoSection">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">Kondisi Risiko Aktif</h4>
                <a href="{{ route('doctor.patients', ['filter' => 'risiko']) }}"
                    class="text-[11px] text-blue-500 font-semibold hover:underline">
                    Lihat Semua →
                </a>
            </div>

            @if ($riskMothers->isEmpty())
                <div class="bg-green-50 border border-green-200 rounded-3xl p-5 flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-2xl flex items-center justify-center shrink-0">
                        <i class="fa-solid fa-circle-check text-green-600"></i>
                    </div>
                    <p class="text-sm text-green-700 font-semibold">Tidak ada kondisi risiko aktif saat ini.</p>
                </div>
            @else
                <div class="space-y-3">
                    @foreach ($riskMothers as $m)
                        @php
                            $conditions = $m->ancRecords
                                ->flatMap(
                                    fn($r) => $r->detectedConditions->whereIn('status', ['confirmed', 'detected']),
                                )
                                ->unique('condition_type')
                                ->values();
                            $hasPreeclampsia = $conditions->contains('condition_type', 'preeclampsia');
                            $borderColor = $hasPreeclampsia ? 'border-red-100' : 'border-orange-100';
                            $avatarBorder = $hasPreeclampsia ? 'border-red-200' : 'border-orange-200';
                            $avatarBg = $hasPreeclampsia ? 'bg-red-50' : 'bg-orange-50';
                        @endphp
                        <a href="{{ route('doctor.patients.show', $m->id) }}"
                            class="block bg-white rounded-3xl p-4 border {{ $borderColor }} shadow-sm hover:shadow-md transition active:scale-[0.98]">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 rounded-2xl border-2 {{ $avatarBorder }} p-0.5 shrink-0">
                                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $m->user->name }}"
                                        class="rounded-xl w-full h-full object-cover {{ $avatarBg }}"
                                        alt="{{ $m->user->name }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <h5 class="text-sm font-bold text-gray-800">{{ $m->user->name }}</h5>
                                        <span
                                            class="text-[9px] bg-red-50 text-red-500 font-bold px-2 py-0.5 rounded-full">⚠️
                                            Risiko</span>
                                    </div>
                                    <p class="text-xs text-gray-400 mt-0.5">
                                        {{ $m->activePregnancy?->gestational_week ?? '–' }} minggu ·
                                        ANC ke-{{ $m->activePregnancy?->completed_anc_count ?? 0 }} selesai
                                    </p>
                                    <div class="flex flex-wrap gap-1 mt-1.5">
                                        @foreach ($conditions as $cond)
                                            @php
                                                $condStyle = match ($cond->condition_type) {
                                                    'preeclampsia' => 'bg-red-50 text-red-500',
                                                    'kek' => 'bg-orange-50 text-orange-500',
                                                    'anemia' => 'bg-yellow-50 text-yellow-600',
                                                    default => 'bg-gray-50 text-gray-500',
                                                };
                                            @endphp
                                            <span
                                                class="text-[9px] {{ $condStyle }} font-semibold px-1.5 py-0.5 rounded-full">
                                                {{ $cond->label }}
                                            </span>
                                        @endforeach
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-gray-300 text-xs shrink-0"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- DISTRIBUSI KONDISI --}}
        <section class="space-y-3">
            <h4 class="text-sm font-bold text-gray-700 px-1">Distribusi Kondisi</h4>

            <div class="bg-white border border-gray-100 rounded-[2.5rem] p-5 shadow-sm space-y-3">

                {{-- KEK --}}
                @if ($conditionSummary['kek'] > 0)
                    <a href="{{ route('doctor.patients', ['filter' => 'risiko']) }}"
                        class="flex items-center gap-3 hover:opacity-80 transition">
                    @else
                        <div class="flex items-center gap-3">
                @endif
                <div class="w-9 h-9 bg-orange-50 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-weight-scale text-orange-400 text-sm"></i>
                </div>
                <div class="flex-1">
                    <p
                        class="text-xs font-semibold {{ $conditionSummary['kek'] > 0 ? 'text-gray-700' : 'text-gray-400' }}">
                        KEK</p>
                    <p class="text-[10px] {{ $conditionSummary['kek'] > 0 ? 'text-gray-400' : 'text-gray-300' }}">LILA &lt;
                        23,5 cm</p>
                </div>
                <span
                    class="text-[10px] px-2 py-1 rounded-full font-semibold
                            {{ $conditionSummary['kek'] > 0 ? 'bg-orange-50 text-orange-500' : 'bg-gray-50 text-gray-300' }}">
                    {{ $conditionSummary['kek'] > 0 ? $conditionSummary['kek'] . ' ibu' : '–' }}
                </span>
                @if ($conditionSummary['kek'] > 0)
                    <i class="fa-solid fa-chevron-right text-gray-300 text-[10px]"></i>
                @endif
                @if ($conditionSummary['kek'] > 0)
                    </a>
                @else
            </div>
            @endif

            <div class="h-px bg-gray-100"></div>

            {{-- Preeklampsia --}}
            @if ($conditionSummary['preeclampsia'] > 0)
                <a href="{{ route('doctor.patients', ['filter' => 'risiko']) }}"
                    class="flex items-center gap-3 hover:opacity-80 transition">
                @else
                    <div class="flex items-center gap-3">
            @endif
            <div class="w-9 h-9 bg-red-50 rounded-xl flex items-center justify-center shrink-0">
                <i class="fa-solid fa-heart-pulse text-red-400 text-sm"></i>
            </div>
            <div class="flex-1">
                <p
                    class="text-xs font-semibold {{ $conditionSummary['preeclampsia'] > 0 ? 'text-gray-700' : 'text-gray-400' }}">
                    Preeklamsia</p>
                <p class="text-[10px] {{ $conditionSummary['preeclampsia'] > 0 ? 'text-gray-400' : 'text-gray-300' }}">TD ≥
                    140/90 mmHg</p>
            </div>
            <span
                class="text-[10px] px-2 py-1 rounded-full font-semibold
                            {{ $conditionSummary['preeclampsia'] > 0 ? 'bg-red-50 text-red-500' : 'bg-gray-50 text-gray-300' }}">
                {{ $conditionSummary['preeclampsia'] > 0 ? $conditionSummary['preeclampsia'] . ' ibu' : '–' }}
            </span>
            @if ($conditionSummary['preeclampsia'] > 0)
                <i class="fa-solid fa-chevron-right text-gray-300 text-[10px]"></i>
            @endif
            @if ($conditionSummary['preeclampsia'] > 0)
                </a>
            @else
                </div>
            @endif

            <div class="h-px bg-gray-100"></div>

            {{-- Anemia --}}
            @if ($conditionSummary['anemia'] > 0)
                <a href="{{ route('doctor.patients', ['filter' => 'risiko']) }}"
                    class="flex items-center gap-3 hover:opacity-80 transition">
                @else
                    <div class="flex items-center gap-3">
            @endif
            <div class="w-9 h-9 bg-blue-50 rounded-xl flex items-center justify-center shrink-0">
                <i
                    class="fa-solid fa-droplet {{ $conditionSummary['anemia'] > 0 ? 'text-blue-400' : 'text-blue-200' }} text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="text-xs font-semibold {{ $conditionSummary['anemia'] > 0 ? 'text-gray-700' : 'text-gray-400' }}">
                    Anemia</p>
                <p class="text-[10px] {{ $conditionSummary['anemia'] > 0 ? 'text-gray-400' : 'text-gray-300' }}">Hb &lt; 11
                    g/dL</p>
            </div>
            <span
                class="text-[10px] px-2 py-1 rounded-full font-semibold
                            {{ $conditionSummary['anemia'] > 0 ? 'bg-blue-50 text-blue-500' : 'bg-gray-50 text-gray-300' }}">
                {{ $conditionSummary['anemia'] > 0 ? $conditionSummary['anemia'] . ' ibu' : '–' }}
            </span>
            @if ($conditionSummary['anemia'] > 0)
                <i class="fa-solid fa-chevron-right text-gray-300 text-[10px]"></i>
            @endif
            @if ($conditionSummary['anemia'] > 0)
                </a>
            @else
                </div>
            @endif
            </div>
        </section>

        {{-- ANC TERBARU --}}
        <section class="space-y-3">
            <div class="flex justify-between items-center px-1">
                <h4 class="text-sm font-bold text-gray-700">ANC Terbaru</h4>
                <a href="{{ route('doctor.patients') }}" class="text-[11px] text-blue-500 font-semibold hover:underline">
                    Lihat Semua →
                </a>
            </div>

            @if ($recentAnc->isEmpty())
                <p class="text-xs text-gray-400 px-1">Belum ada data ANC terkunci.</p>
            @else
                <div class="space-y-3">
                    @foreach ($recentAnc as $anc)
                        <a href="{{ route('doctor.patients.show', $anc->mother_id) }}"
                            class="block bg-white rounded-3xl p-4 border border-gray-100 shadow-sm hover:shadow-md transition active:scale-[0.98]">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-full border-2 border-blue-100 p-0.5 shrink-0">
                                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $anc->mother->user->name }}"
                                        class="rounded-full" alt="{{ $anc->mother->user->name }}">
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h5 class="text-sm font-bold text-gray-800 truncate">{{ $anc->mother->user->name }}
                                    </h5>
                                    <p class="text-xs text-gray-400">
                                        ANC ke-{{ $anc->schedule->anc_number }} ·
                                        {{ \Carbon\Carbon::parse($anc->schedule->schedule_date)->translatedFormat('d M Y') }}
                                    </p>
                                </div>
                                <span
                                    class="text-[10px] bg-green-50 text-green-600 font-semibold px-2.5 py-1 rounded-full whitespace-nowrap">
                                    ✔ Terkunci
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            @endif
        </section>

        {{-- BATASAN PERAN --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl p-4 text-xs text-blue-700">
            <div class="flex gap-3">
                <i class="fa-solid fa-circle-info mt-0.5 shrink-0"></i>
                <p class="leading-relaxed">
                    Sebagai Dokter/Nutrisionis, Anda dapat <strong>mereview data ANC</strong> dan
                    <strong>memberikan rekomendasi</strong>. Validasi dan penguncian data dilakukan oleh Bidan.
                </p>
            </div>
        </div>

    </main>

@endsection
