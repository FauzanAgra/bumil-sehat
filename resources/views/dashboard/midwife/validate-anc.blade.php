@extends('layouts.midwife-plain')

@section('title', 'BumilSehat - Validasi ANC')

@section('content')

    {{-- ─── STICKY HEADER ─────────────────────────────────────────── --}}
    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40 shrink-0">
        <a href="{{ route('midwife.patients.show', $ancRecord->mother_id) }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Validasi Data ANC</h1>
            <p class="text-[11px] text-gray-400">
                {{ $ancRecord->mother->user->name }} · ANC ke-{{ $ancRecord->schedule->anc_number }}
            </p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-10 pt-5 space-y-5">

        {{-- ─── PATIENT CARD ────────────────────────────────────────── --}}
        <section
            class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2rem] p-5 text-white relative overflow-hidden shadow-lg shadow-rose-200">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-14 h-14 rounded-2xl border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $ancRecord->mother->user->name }}"
                        class="rounded-xl w-full h-full object-cover bg-rose-300" alt="avatar">
                </div>
                <div class="flex-1">
                    <p class="text-rose-100 text-[10px] font-bold uppercase tracking-widest">Ibu Hamil</p>
                    <h2 class="text-base font-bold leading-tight">{{ $ancRecord->mother->user->name }}</h2>
                    <div class="flex items-center gap-2 mt-1.5 flex-wrap">
                        <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                            ANC ke-{{ $ancRecord->schedule->anc_number }}
                        </span>
                        <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                            {{ \Carbon\Carbon::parse($ancRecord->schedule->schedule_date)->translatedFormat('d M Y') }}
                        </span>
                        @if ($ancRecord->detectedConditions->where('status', 'detected')->isNotEmpty())
                            <span
                                class="text-[10px] bg-yellow-400/30 text-yellow-100 px-2.5 py-1 rounded-full font-semibold">
                                <i class="fa-solid fa-triangle-exclamation mr-1"></i>Ada Kondisi Risiko
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ─── BANNER: SUDAH DIKUNCI ───────────────────────────────── --}}
        @if ($ancRecord->is_locked)
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-lock mr-1"></i> Data sudah dikunci dan tidak dapat diubah.
            </div>
        @endif

        {{-- ─── INFO KUNJUNGAN ──────────────────────────────────────── --}}
        <section class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                <i class="fa-regular fa-calendar text-blue-500 text-lg"></i>
            </div>
            <div>
                <p class="text-[10px] text-gray-400 font-semibold uppercase tracking-wide">Tanggal Kunjungan</p>
                <p class="text-sm font-bold text-gray-800 mt-0.5">
                    {{ \Carbon\Carbon::parse($ancRecord->schedule->schedule_date)->translatedFormat('l, d F Y') }}
                </p>
                <p class="text-xs text-gray-400 mt-0.5">
                    @if ($ancRecord->schedule->schedule_time)
                        Pukul {{ \Carbon\Carbon::parse($ancRecord->schedule->schedule_time)->format('H:i') }} WIB
                        @if ($ancRecord->submitted_at)
                            ·
                        @endif
                    @endif
                    @if ($ancRecord->submitted_at)
                        Disubmit: {{ $ancRecord->submitted_at->translatedFormat('d F Y, H:i') }} WIB
                    @endif
                </p>
            </div>
        </section>

        {{-- ─── KONDISI TERDETEKSI (alert cards per kondisi) ───────── --}}
        @if ($ancRecord->detectedConditions->isNotEmpty())
            <section class="space-y-2">
                <div class="flex items-center gap-2 px-1">
                    <i class="fa-solid fa-triangle-exclamation text-amber-500 text-xs"></i>
                    <h3 class="text-xs font-bold text-amber-600 uppercase tracking-wide">Kondisi Terdeteksi Sistem</h3>
                </div>

                @foreach ($ancRecord->detectedConditions as $condition)
                    @php
                        $alertCfg = match ($condition->condition_type) {
                            'preeclampsia' => [
                                'wrap' => 'bg-red-50 border-red-200',
                                'iconBg' => 'bg-red-100',
                                'iconColor' => 'text-red-500',
                                'icon' => 'fa-heart-pulse',
                                'title' => 'text-red-700',
                                'trigger' =>
                                    'TD ' .
                                    ($ancRecord->systolic ?? '?') .
                                    '/' .
                                    ($ancRecord->diastolic ?? '?') .
                                    ' mmHg (≥ 140/90)',
                                'badge' => 'bg-red-100 text-red-500',
                            ],
                            'kek' => [
                                'wrap' => 'bg-orange-50 border-orange-200',
                                'iconBg' => 'bg-orange-100',
                                'iconColor' => 'text-orange-500',
                                'icon' => 'fa-weight-scale',
                                'title' => 'text-orange-700',
                                'trigger' => 'LILA ' . ($ancRecord->lila ?? '?') . ' cm (< 23,5 cm)',
                                'badge' => 'bg-orange-100 text-orange-500',
                            ],
                            'anemia' => [
                                'wrap' => 'bg-yellow-50 border-yellow-200',
                                'iconBg' => 'bg-yellow-100',
                                'iconColor' => 'text-yellow-600',
                                'icon' => 'fa-droplet',
                                'title' => 'text-yellow-700',
                                'trigger' => 'Hb ' . ($ancRecord->hb ?? '?') . ' g/dL (< 11 g/dL)',
                                'badge' => 'bg-yellow-100 text-yellow-600',
                            ],
                            default => [
                                'wrap' => 'bg-gray-50 border-gray-200',
                                'iconBg' => 'bg-gray-100',
                                'iconColor' => 'text-gray-500',
                                'icon' => 'fa-circle-exclamation',
                                'title' => 'text-gray-700',
                                'trigger' => '',
                                'badge' => 'bg-gray-100 text-gray-500',
                            ],
                        };
                    @endphp

                    <div class="border rounded-2xl p-4 flex items-start gap-3 {{ $alertCfg['wrap'] }}">
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $alertCfg['iconBg'] }}">
                            <i class="fa-solid {{ $alertCfg['icon'] }} text-sm {{ $alertCfg['iconColor'] }}"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold {{ $alertCfg['title'] }}">{{ $condition->label }}</p>
                            @if ($alertCfg['trigger'])
                                <p class="text-xs mt-0.5 opacity-80">{{ $alertCfg['trigger'] }}</p>
                            @endif
                            <p class="text-[10px] mt-1 opacity-60">Terdeteksi otomatis oleh sistem</p>
                        </div>
                        <span
                            class="text-[9px] font-bold px-2 py-1 rounded-full whitespace-nowrap {{ $alertCfg['badge'] }}">
                            Belum dikonfirmasi
                        </span>
                    </div>
                @endforeach
            </section>
        @else
            {{-- State tidak ada kondisi --}}
            <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
                <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-circle-check text-green-600 text-base"></i>
                </div>
                <div class="flex-1">
                    <p class="text-sm font-bold text-green-700">Tidak Ada Kondisi Terdeteksi</p>
                    <p class="text-xs text-green-600 mt-0.5 leading-relaxed">
                        Semua nilai dalam batas normal.
                    </p>
                </div>
            </div>
        @endif

        {{-- ─── ANTROPOMETRI (layout list row, sesuai prototype) ────── --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Antropometri</h3>
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-0">

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-weight text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Berat Badan</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $ancRecord->weight ? $ancRecord->weight . ' kg' : '—' }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-ruler-vertical text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Tinggi Badan</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $ancRecord->height ? $ancRecord->height . ' cm' : '—' }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-50 rounded-xl flex items-center justify-center text-orange-400">
                            <i class="fa-solid fa-circle-dot text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">LILA</span>
                    </div>
                    @php $lilaAbnormal = $ancRecord->lila && $ancRecord->lila < 23.5; @endphp
                    <span class="text-sm font-bold {{ $lilaAbnormal ? 'text-orange-600' : 'text-gray-800' }}">
                        {{ $ancRecord->lila ? $ancRecord->lila . ' cm' : '—' }}
                        @if ($lilaAbnormal)
                            ⚠️
                        @endif
                    </span>
                </div>

            </div>
        </section>

        {{-- ─── TANDA VITAL ─────────────────────────────────────────── --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Tanda Vital</h3>
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm">
                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-red-50 rounded-xl flex items-center justify-center text-red-400">
                            <i class="fa-solid fa-heart-pulse text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Tekanan Darah</span>
                    </div>
                    @php
                        $isHighBp =
                            ($ancRecord->systolic !== null && $ancRecord->systolic >= 140) ||
                            ($ancRecord->diastolic !== null && $ancRecord->diastolic >= 90);
                    @endphp
                    <span class="text-sm font-bold {{ $isHighBp ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $ancRecord->systolic && $ancRecord->diastolic
                            ? $ancRecord->systolic . '/' . $ancRecord->diastolic . ' mmHg'
                            : '—' }}
                        @if ($isHighBp && $ancRecord->systolic)
                            ⚠️
                        @endif
                    </span>
                </div>
            </div>
        </section>

        {{-- ─── LABORATORIUM & OBSERVASI ────────────────────────────── --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Laboratorium & Observasi</h3>
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-0">

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-solid fa-droplet text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Kadar Hb</span>
                    </div>
                    @php $isLowHb = $ancRecord->hb !== null && $ancRecord->hb < 11; @endphp
                    <span class="text-sm font-bold {{ $isLowHb ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $ancRecord->hb ? $ancRecord->hb . ' g/dL' : '—' }}
                        @if ($isLowHb)
                            ⚠️
                        @endif
                    </span>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center text-green-400">
                            <i class="fa-solid fa-syringe text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Status TT</span>
                    </div>
                    <span
                        class="text-sm font-bold {{ $ancRecord->tt_status === 'done' ? 'text-green-600' : 'text-gray-800' }}">
                        {{ match ($ancRecord->tt_status) {
                            'done' => 'Sudah',
                            'not_done' => 'Belum',
                            default => '—',
                        } }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center text-purple-400">
                            <i class="fa-solid fa-baby text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Letak Janin</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ match ($ancRecord->fetal_position) {
                            'head_down' => 'Kepala di Bawah',
                            'transverse' => 'Lintang',
                            'breech' => 'Sungsang',
                            default => '—',
                        } }}
                    </span>
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-pink-50 rounded-xl flex items-center justify-center text-pink-400">
                            <i class="fa-solid fa-heart text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">DJJ</span>
                    </div>
                    <span class="text-sm font-bold text-gray-800">
                        {{ $ancRecord->fetal_heart_rate ? $ancRecord->fetal_heart_rate . ' bpm' : '—' }}
                    </span>
                </div>

            </div>
        </section>

        {{-- ─── FOTO USG ────────────────────────────────────────────── --}}
        @if ($ancRecord->usgImage)
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Dokumen USG</h3>
                <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                    <img src="{{ asset('storage/' . $ancRecord->usgImage->image_url) }}"
                        class="w-full rounded-2xl object-cover" alt="Foto USG">
                </div>
            </section>
        @endif

        {{-- ═══════════════════════════════════════════════════════════ --}}
        {{-- FORM VALIDASI (hanya tampil jika belum dikunci)             --}}
        {{-- ═══════════════════════════════════════════════════════════ --}}
        @if (!$ancRecord->is_locked)
            <form id="validationForm" method="POST" action="{{ route('midwife.anc.validate.store', $ancRecord->id) }}"
                class="space-y-5">
                @csrf

                {{-- ─── KONFIRMASI KONDISI ─────────────────────────── --}}
                @if ($ancRecord->detectedConditions->isNotEmpty())
                    <section class="space-y-4">
                        <div class="flex items-center gap-2 px-1">
                            <i class="fa-solid fa-stethoscope text-rose-500 text-xs"></i>
                            <h3 class="text-sm font-bold text-gray-700">Konfirmasi Kondisi</h3>
                        </div>
                        <p class="text-xs text-gray-500 px-1 leading-relaxed">
                            Bidan perlu mengkonfirmasi setiap kondisi yang terdeteksi sistem.
                            Keputusan Bidan bersifat final.
                        </p>

                        @foreach ($ancRecord->detectedConditions as $i => $condition)
                            @php
                                $cardCfg = match ($condition->condition_type) {
                                    'preeclampsia' => [
                                        'iconBg' => 'bg-red-50',
                                        'iconColor' => 'text-red-400',
                                        'icon' => 'fa-heart-pulse',
                                        'sub' =>
                                            'TD ' .
                                            ($ancRecord->systolic ?? '?') .
                                            '/' .
                                            ($ancRecord->diastolic ?? '?') .
                                            ' mmHg — perlu konfirmasi klinis',
                                    ],
                                    'kek' => [
                                        'iconBg' => 'bg-orange-50',
                                        'iconColor' => 'text-orange-400',
                                        'icon' => 'fa-weight-scale',
                                        'sub' => 'LILA ' . ($ancRecord->lila ?? '?') . ' cm — perlu konfirmasi klinis',
                                    ],
                                    'anemia' => [
                                        'iconBg' => 'bg-yellow-50',
                                        'iconColor' => 'text-yellow-500',
                                        'icon' => 'fa-droplet',
                                        'sub' => 'Hb ' . ($ancRecord->hb ?? '?') . ' g/dL — perlu konfirmasi klinis',
                                    ],
                                    default => [
                                        'iconBg' => 'bg-gray-50',
                                        'iconColor' => 'text-gray-400',
                                        'icon' => 'fa-circle-info',
                                        'sub' => '',
                                    ],
                                };
                                $oldAction = old("conditions.$i.action");
                            @endphp

                            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">
                                <input type="hidden" name="conditions[{{ $i }}][id]"
                                    value="{{ $condition->id }}">

                                <div class="flex items-start gap-3">
                                    <div
                                        class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $cardCfg['iconBg'] }}">
                                        <i
                                            class="fa-solid {{ $cardCfg['icon'] }} text-sm {{ $cardCfg['iconColor'] }}"></i>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-sm font-bold text-gray-800">{{ $condition->label }}</p>
                                        @if ($cardCfg['sub'])
                                            <p class="text-xs text-gray-500 mt-0.5">{{ $cardCfg['sub'] }}</p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Toggle tombol konfirmasi / tolak --}}
                                <div class="flex gap-2">
                                    <label
                                        class="condition-label flex-1 flex items-center justify-center py-2.5 rounded-xl border-2 cursor-pointer transition text-xs font-semibold
                                        {{ $oldAction === 'confirm'
                                            ? 'border-red-400 bg-red-50 text-red-600'
                                            : 'border-gray-200 text-gray-500 hover:border-red-300 hover:bg-red-50 hover:text-red-600' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]"
                                            value="confirm" class="hidden"
                                            {{ $oldAction === 'confirm' ? 'checked' : '' }}>
                                        ✓ Konfirmasi Kondisi
                                    </label>
                                    <label
                                        class="condition-label flex-1 flex items-center justify-center py-2.5 rounded-xl border-2 cursor-pointer transition text-xs font-semibold
                                        {{ $oldAction === 'reject'
                                            ? 'border-green-400 bg-green-50 text-green-600'
                                            : 'border-gray-200 text-gray-500 hover:border-green-300 hover:bg-green-50 hover:text-green-600' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]"
                                            value="reject" class="hidden" {{ $oldAction === 'reject' ? 'checked' : '' }}>
                                        ✗ Bukan Kondisi Ini
                                    </label>
                                </div>

                                {{-- Catatan per kondisi --}}
                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">
                                        Catatan Bidan (opsional)
                                    </label>
                                    <textarea name="conditions[{{ $i }}][notes]" rows="2" placeholder="Tambahkan catatan klinis..."
                                        class="w-full mt-1.5 rounded-xl border border-gray-200 px-3 py-2.5 text-xs focus:ring-rose-200 focus:ring-2 outline-none resize-none text-gray-700 leading-relaxed">{{ old("conditions.$i.notes") }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </section>
                @endif

                {{-- ─── CATATAN UMUM UNTUK IBU ─────────────────────── --}}
                <section class="space-y-3">
                    <h3 class="text-sm font-bold text-gray-700 px-1">Catatan Umum untuk Ibu</h3>
                    <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm">
                        <textarea id="generalNote" name="notes" rows="3"
                            placeholder="Tulis catatan yang akan terlihat oleh Ibu di halaman riwayat ANC..."
                            class="w-full rounded-xl border border-gray-200 px-4 py-3 text-sm focus:ring-rose-200 focus:ring-2 outline-none resize-none text-gray-700 leading-relaxed">{{ old('notes') }}</textarea>
                    </div>
                </section>

                {{-- ─── INFO KUNCI ──────────────────────────────────── --}}
                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-4 text-xs text-amber-700 flex gap-3">
                    <i class="fa-solid fa-lock text-amber-400 mt-0.5 shrink-0"></i>
                    <p class="leading-relaxed">
                        Setelah dikunci, data tidak dapat diubah lagi.
                        Pastikan semua kondisi sudah dikonfirmasi dan catatan sudah sesuai sebelum mengunci.
                    </p>
                </div>

                {{-- ─── TOMBOL KUNCI (B3: disabled sampai semua kondisi dijawab) ── --}}
                <div class="space-y-3">
                    <button id="lockBtn" type="button" onclick="openLockModal()"
                        class="w-full bg-rose-500 text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-200 transition-all
                               disabled:opacity-40 disabled:cursor-not-allowed hover:bg-rose-600 active:scale-[0.98]"
                        @if ($ancRecord->detectedConditions->isNotEmpty()) disabled @endif>
                        <i class="fa-solid fa-lock mr-2"></i>
                        Kunci & Selesaikan Validasi
                    </button>
                    <p id="lockHint"
                        class="text-[10px] text-center leading-relaxed
                        {{ $ancRecord->detectedConditions->isEmpty() ? 'text-green-500' : 'text-gray-400' }}">
                        @if ($ancRecord->detectedConditions->isEmpty())
                            Tidak ada kondisi yang perlu dikonfirmasi. Siap dikunci.
                        @else
                            Konfirmasi semua kondisi terlebih dahulu untuk mengaktifkan tombol kunci.
                        @endif
                    </p>
                </div>

            </form>
        @else
            {{-- ─── SUDAH DIVALIDASI ────────────────────────────────── --}}
            <div class="bg-green-50 border border-green-200 rounded-3xl p-5 space-y-2">
                <p class="text-sm font-bold text-green-700">
                    <i class="fa-solid fa-circle-check mr-1"></i>
                    Divalidasi oleh {{ $ancRecord->validation->midwife->name }}
                </p>
                @if ($ancRecord->validation->notes)
                    <p class="text-xs text-gray-600 leading-relaxed">{{ $ancRecord->validation->notes }}</p>
                @endif
                <p class="text-[10px] text-gray-400">
                    {{ \Carbon\Carbon::parse($ancRecord->validation->locked_at)->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>
        @endif

    </main>

    {{-- ═══════════════════════════════════════════════════════════════ --}}
    {{-- B3: LOCK CONFIRMATION MODAL                                     --}}
    {{-- ═══════════════════════════════════════════════════════════════ --}}
    @if (!$ancRecord->is_locked)
        <div id="lockModal"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-end justify-center z-[999] hidden">
            <div class="w-full max-w-md bg-white rounded-t-[2.5rem] p-6 space-y-5 shadow-2xl">
                <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto"></div>

                <div class="text-center space-y-2 pt-1">
                    <div class="w-16 h-16 bg-rose-50 rounded-2xl flex items-center justify-center mx-auto">
                        <i class="fa-solid fa-lock text-rose-500 text-2xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-800 mt-3">Kunci Data Ini?</h3>
                    <p class="text-sm text-gray-500 leading-relaxed px-2">
                        Data ANC ke-{{ $ancRecord->schedule->anc_number }}
                        <strong>{{ $ancRecord->mother->user->name }}</strong>
                        akan dikunci dan tidak dapat diubah lagi setelah ini.
                    </p>
                </div>

                {{-- Ringkasan sebelum kunci --}}
                <div class="bg-gray-50 rounded-2xl p-4 space-y-2 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Kondisi dikonfirmasi</span>
                        <span id="summaryConditions" class="font-bold text-gray-800">–</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-500">Catatan umum</span>
                        <span id="summaryNote" class="font-bold text-gray-800">–</span>
                    </div>
                </div>

                <div class="space-y-3 pt-1">
                    <button onclick="submitValidation()"
                        class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-2xl shadow-lg shadow-rose-200/50 transition">
                        Ya, Kunci Sekarang
                    </button>
                    <button onclick="document.getElementById('lockModal').classList.add('hidden')"
                        class="w-full text-sm font-semibold text-gray-500 py-2 hover:text-gray-700 transition">
                        Cek ulang dulu
                    </button>
                </div>
            </div>
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        // ──────────────────────────────────────────────────────────────
        // Jumlah kondisi yang perlu dikonfirmasi
        // ──────────────────────────────────────────────────────────────
        const totalConditions = {{ $ancRecord->detectedConditions->count() }};

        // ──────────────────────────────────────────────────────────────
        // Styling radio buttons + cek apakah semua kondisi sudah dijawab
        // ──────────────────────────────────────────────────────────────
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const groupName = this.name;

                // Update styling semua label dalam grup yang sama
                document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => {
                    const label = r.closest('label');
                    const isConfirm = r.value === 'confirm';

                    label.classList.remove(
                        'border-red-400', 'bg-red-50', 'text-red-600',
                        'border-green-400', 'bg-green-50', 'text-green-600'
                    );
                    label.classList.add('border-gray-200', 'text-gray-500');

                    if (r.checked) {
                        label.classList.remove('border-gray-200', 'text-gray-500');
                        if (isConfirm) {
                            label.classList.add('border-red-400', 'bg-red-50', 'text-red-600');
                        } else {
                            label.classList.add('border-green-400', 'bg-green-50',
                            'text-green-600');
                        }
                    }
                });

                checkAllConditionsAnswered();
            });
        });

        function checkAllConditionsAnswered() {
            if (totalConditions === 0) return;

            // Kumpulkan semua nama grup unik (conditions[0][action], conditions[1][action], dst.)
            const allGroups = new Set(
                [...document.querySelectorAll('input[name$="[action]"]')]
                .map(r => r.name)
            );

            // Cek berapa grup yang sudah ada yang terpilih
            let answeredGroups = 0;
            allGroups.forEach(groupName => {
                const checked = document.querySelector(`input[name="${groupName}"]:checked`);
                if (checked) answeredGroups++;
            });

            const lockBtn = document.getElementById('lockBtn');
            const lockHint = document.getElementById('lockHint');

            if (answeredGroups >= allGroups.size) {
                lockBtn.removeAttribute('disabled');
                lockHint.textContent = 'Semua kondisi sudah dikonfirmasi. Siap dikunci.';
                lockHint.className = 'text-[10px] text-center text-green-500 leading-relaxed';
            }
        }

        // ──────────────────────────────────────────────────────────────
        // Buka lock modal + isi ringkasan
        // ──────────────────────────────────────────────────────────────
        function openLockModal() {
            // Ringkasan catatan
            const noteVal = document.getElementById('generalNote')?.value?.trim();
            document.getElementById('summaryNote').textContent =
                noteVal ? '✓ Ada catatan' : '— Tidak ada catatan';

            // Ringkasan kondisi
            const checkedRadios = [...document.querySelectorAll('input[name$="[action]"]:checked')];
            if (checkedRadios.length === 0) {
                document.getElementById('summaryConditions').textContent = '✓ Tidak ada kondisi';
            } else {
                const conditionLabels = checkedRadios.map(r => {
                    const label = r.closest('.bg-white')?.querySelector('.text-sm.font-bold.text-gray-800')
                        ?.textContent?.trim() ?? r.value;
                    return r.value === 'confirm' ? `⚠️ ${label}` : `✓ ${label} (ditolak)`;
                });
                document.getElementById('summaryConditions').textContent = conditionLabels.join(' · ');
            }

            document.getElementById('lockModal').classList.remove('hidden');
        }

        function submitValidation() {
            document.getElementById('lockModal').classList.add('hidden');
            document.getElementById('validationForm').submit();
        }

        // ──────────────────────────────────────────────────────────────
        // Restore old state saat validation error (back with input)
        // ──────────────────────────────────────────────────────────────
        @if (old('conditions'))
            checkAllConditionsAnswered();
        @endif
    </script>
@endpush
