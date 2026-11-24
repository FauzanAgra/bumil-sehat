@props([
    'schedules' => collect(),
    'theme' => 'rose',
    'title' => 'Grafik Tren Klinis ANC 1–6',
])

@php
    $points = collect($schedules)
        ->filter(fn ($schedule) => $schedule->ancRecord !== null)
        ->sortBy('anc_number')
        ->values()
        ->map(function ($schedule) {
            $record = $schedule->ancRecord;

            return [
                'anc_number' => (int) $schedule->anc_number,
                'label' => 'ANC '.$schedule->anc_number,
                'date' => $schedule->schedule_date
                    ? \Illuminate\Support\Carbon::parse($schedule->schedule_date)->translatedFormat('d M Y')
                    : '-',
                'weight' => $record->weight !== null ? (float) $record->weight : null,
                'systolic' => $record->systolic !== null ? (int) $record->systolic : null,
                'diastolic' => $record->diastolic !== null ? (int) $record->diastolic : null,
                'hb' => $record->hb !== null ? (float) $record->hb : null,
                'lila' => $record->lila !== null ? (float) $record->lila : null,
            ];
        });

    $hasClinicalData = $points->contains(
        fn ($point) => $point['weight'] !== null
            || $point['systolic'] !== null
            || $point['diastolic'] !== null
            || $point['hb'] !== null
            || $point['lila'] !== null
    );

    $defaultMetric = match (true) {
        $points->contains(fn ($p) => $p['systolic'] !== null || $p['diastolic'] !== null) => 'bp',
        $points->contains(fn ($p) => $p['weight'] !== null) => 'weight',
        $points->contains(fn ($p) => $p['hb'] !== null) => 'hb',
        default => 'lila',
    };

    $chartPayload = [
        'defaultMetric' => $defaultMetric,
        'points' => $points->all(),
    ];

    $isBlue = $theme === 'blue';
    $emptyIconBg = $isBlue ? 'bg-blue-50 text-blue-400' : 'bg-rose-50 text-rose-400';
    $focusRing = $isBlue ? 'focus:ring-blue-300' : 'focus:ring-rose-300';
@endphp

<section class="space-y-3">
    <div class="flex items-center justify-between px-1">
        <h3 class="text-sm font-bold text-gray-700">{{ $title }}</h3>
        @if ($hasClinicalData)
            <span class="text-[10px] font-semibold text-gray-400">
                {{ $points->count() }} kunjungan tercatat
            </span>
        @endif
    </div>

    @if ($hasClinicalData)
        <div data-anc-trend-chart data-theme="{{ $isBlue ? 'blue' : 'rose' }}"
            class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">
            <script type="application/json" data-chart-payload>@json($chartPayload)</script>

            <div class="flex items-center justify-between gap-2">
                <div>
                    <p data-metric-title class="text-[10px] font-bold uppercase tracking-wider text-gray-400">
                        Tekanan Darah (mmHg)
                    </p>
                    <p data-latest-value class="text-lg font-bold text-gray-800">—</p>
                </div>
                <span data-status-badge
                    class="text-[10px] font-bold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500 whitespace-nowrap">
                    Memuat...
                </span>
            </div>

            {{-- Metric Tabs --}}
            <div role="tablist" aria-label="Pilih metrik grafik ANC"
                class="grid grid-cols-4 gap-1.5 bg-gray-50 p-1 rounded-2xl">
                @foreach ([
                    'weight' => 'BB',
                    'bp' => 'TD',
                    'hb' => 'Hb',
                    'lila' => 'LILA',
                ] as $metricKey => $metricLabel)
                    <button type="button" role="tab" data-metric-tab="{{ $metricKey }}"
                        aria-selected="{{ $defaultMetric === $metricKey ? 'true' : 'false' }}"
                        class="text-[11px] font-semibold py-2 rounded-xl transition focus:outline-none focus:ring-2 {{ $focusRing }} text-gray-500 hover:text-gray-800">
                        {{ $metricLabel }}
                    </button>
                @endforeach
            </div>

            {{-- Threshold Info --}}
            <div class="flex items-center justify-between bg-gray-50 rounded-2xl px-3.5 py-2 text-[11px]">
                <span class="text-gray-400 font-medium">Ambang Risiko</span>
                <span data-threshold-text class="font-bold text-gray-700 text-right">—</span>
            </div>

            {{-- Chart Canvas --}}
            <div class="bg-slate-50/70 border border-gray-100 rounded-2xl p-3">
                <div data-canvas-wrap class="h-[190px] w-full relative">
                    <canvas data-chart-canvas aria-label="Grafik tren klinis ANC 1 sampai 6"></canvas>
                </div>
                <p data-empty-metric class="hidden text-center text-xs text-gray-400 py-10 leading-relaxed"></p>
            </div>

            {{-- Legend --}}
            <div data-chart-legend
                class="flex items-center justify-between text-[10px] text-gray-500 px-1 flex-wrap gap-2"></div>
        </div>
    @else
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm flex items-center gap-3.5">
            <div class="w-11 h-11 rounded-2xl flex items-center justify-center shrink-0 {{ $emptyIconBg }}">
                <i class="fa-solid fa-chart-line text-base"></i>
            </div>
            <div class="space-y-0.5">
                <p class="text-xs font-bold text-gray-700">Belum ada data klinis ANC</p>
                <p class="text-[11px] text-gray-400 leading-relaxed">
                    Grafik tren Berat Badan, Tekanan Darah, Hb, dan LILA akan tampil otomatis setelah minimal 1
                    kunjungan ANC terisi.
                </p>
            </div>
        </div>
    @endif
</section>
