@extends('layouts.doctor')

@section('title', 'BumilSehat - Detail Pasien')

@section('content')

    {{-- HEADER --}}
    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('doctor.patients') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-blue-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Detail Pasien</h1>
            <p class="text-xs text-gray-400">Supervisi ANC & catatan rekomendasi</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-32">

        {{-- SUCCESS FLASH --}}
        @if (session('success'))
            <div
                class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-700 text-xs rounded-2xl px-4 py-3 space-y-1">
                <p class="font-bold"><i class="fa-solid fa-circle-exclamation mr-1"></i> Periksa kembali input rekomendasi:</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- PROFIL PASIEN --}}
        <section
            class="bg-linear-to-br from-blue-500 to-blue-700 rounded-[2rem] p-5 text-white relative overflow-hidden shadow-lg shadow-blue-200">
            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-16 h-16 rounded-2xl border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $motherProfile->user->name }}"
                        class="rounded-xl w-full h-full object-cover bg-blue-300" alt="avatar">
                </div>
                <div class="flex-1">
                    <p class="text-blue-100 text-[10px] font-bold uppercase tracking-widest">Ibu Hamil</p>
                    <h2 class="text-base font-bold leading-tight">{{ $motherProfile->user->name }}</h2>
                    <p class="text-blue-100 text-xs mt-0.5">
                        NIK: {{ $motherProfile->getMaskedNik() ?? '–' }}
                    </p>
                    <div class="flex items-center gap-2 mt-2 flex-wrap">
                        @if ($pregnancy)
                            <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                                {{ $pregnancy->gestational_week }} Minggu
                            </span>
                            <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                                HPL: {{ $pregnancy->hpl_date?->translatedFormat('d M Y') ?? '–' }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- BADGE READ-ONLY --}}
        <div class="bg-blue-50 border border-blue-200 rounded-2xl px-4 py-3 flex items-center gap-3">
            <i class="fa-solid fa-stethoscope text-blue-500 shrink-0"></i>
            <p class="text-xs text-blue-700 leading-relaxed">
                Data pengukuran ANC bersifat <strong>read-only</strong> (validasi & penguncian oleh Bidan pendamping).
                Anda dapat menambahkan <strong>catatan rekomendasi tindak lanjut</strong> di bawah.
            </p>
        </div>

        {{-- KONDISI AKTIF (jika ada) --}}
        @if ($activeConditions->isNotEmpty())
            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex gap-3">
                <i class="fa-solid fa-triangle-exclamation text-red-400 mt-0.5 shrink-0"></i>
                <div>
                    <p class="text-xs font-bold text-red-700 mb-1.5">Kondisi Aktif Terdeteksi</p>
                    <div class="flex flex-wrap gap-1.5">
                        @foreach ($activeConditions as $cond)
                            @php
                                $cs = match ($cond->condition_type) {
                                    'preeclampsia' => 'bg-red-100 text-red-600',
                                    'kek' => 'bg-orange-100 text-orange-600',
                                    'anemia' => 'bg-yellow-100 text-yellow-700',
                                    default => 'bg-gray-100 text-gray-600',
                                };
                            @endphp
                            <span class="text-[10px] {{ $cs }} font-semibold px-2 py-0.5 rounded-full">
                                ⚠️ {{ $cond->label }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- DATA KEHAMILAN --}}
        @if ($pregnancy)
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Data Kehamilan</h3>
                <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-0">

                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                                <i class="fa-regular fa-calendar text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-600">HPHT</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">
                            {{ $pregnancy->hpht_date?->translatedFormat('d M Y') ?? '–' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                                <i class="fa-solid fa-baby text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-600">Usia Kehamilan</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">{{ $pregnancy->gestational_week }} Minggu</span>
                    </div>

                    <div class="flex items-center justify-between py-3 border-b border-gray-50">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center text-purple-400">
                                <i class="fa-solid fa-star text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-600">HPL (Perkiraan)</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">
                            {{ $pregnancy->hpl_date?->translatedFormat('d M Y') ?? '–' }}
                        </span>
                    </div>

                    <div class="flex items-center justify-between py-3">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-user-nurse text-xs"></i>
                            </div>
                            <span class="text-sm text-gray-600">Bidan Pendamping</span>
                        </div>
                        <span class="text-sm font-bold text-gray-800">
                            {{ $motherProfile->assignedMidwife?->name ?? '–' }}
                        </span>
                    </div>

                </div>
            </section>

            {{-- GRAFIK TREN KLINIS ANC 1-6 --}}
            <x-anc-trend-charts :schedules="$pregnancy->ancSchedules" theme="blue" />
        @endif

        {{-- RIWAYAT ANC --}}
        <section class="space-y-4">
            <h3 class="text-sm font-bold text-gray-700 px-1">Riwayat ANC</h3>

            @foreach ($ancTimeline as $item)
                @php
                    $schedule = $item['schedule'];
                    $record = $item['record'];
                    $n = $item['number'];
                    $isUsg = in_array($n, [1, 5]);
                @endphp

                @if ($record && $record->is_locked)
                    {{-- ANC Selesai & Terkunci --}}
                    <div class="bg-white border border-gray-100 rounded-3xl p-4 shadow-sm">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-lock text-green-500 text-sm"></i>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-gray-800">ANC ke-{{ $n }}</p>
                                    @if ($isUsg && $record->usgImage)
                                        <span
                                            class="text-[9px] bg-blue-50 text-blue-500 font-bold px-1.5 py-0.5 rounded-full">USG</span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d M Y') }} ·
                                    Terkunci
                                </p>
                            </div>
                            <button type="button" onclick="toggleAncDetail('anc{{ $n }}')"
                                data-id="anc{{ $n }}"
                                class="text-[11px] bg-gray-50 text-gray-500 font-semibold px-3 py-1.5 rounded-xl hover:bg-gray-100 transition">
                                Detail
                            </button>
                        </div>

                        {{-- Detail collapsible --}}
                        <div id="anc{{ $n }}" class="hidden border-t border-gray-50 pt-3 mt-3 space-y-2.5">
                            <div class="grid grid-cols-2 gap-2 text-xs">

                                @if ($record->weight)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">Berat Badan</p>
                                        <p class="font-bold text-gray-700 mt-0.5">{{ $record->weight }} kg</p>
                                    </div>
                                @endif

                                @if ($record->lila)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">LILA</p>
                                        <p
                                            class="font-bold mt-0.5 {{ $record->lila < 23.5 ? 'text-orange-600' : 'text-gray-700' }}">
                                            {{ $record->lila }} cm{{ $record->lila < 23.5 ? ' ⚠️' : '' }}
                                        </p>
                                    </div>
                                @endif

                                @if ($record->systolic && $record->diastolic)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">Tekanan Darah</p>
                                        @php $highBp = $record->systolic >= 140 || $record->diastolic >= 90; @endphp
                                        <p class="font-bold mt-0.5 {{ $highBp ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $record->systolic }}/{{ $record->diastolic }}{{ $highBp ? ' ⚠️' : '' }}
                                        </p>
                                    </div>
                                @endif

                                @if ($record->hb)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">HB</p>
                                        <p
                                            class="font-bold mt-0.5 {{ $record->hb < 11 ? 'text-red-600' : 'text-gray-700' }}">
                                            {{ $record->hb }} g/dL{{ $record->hb < 11 ? ' ⚠️' : '' }}
                                        </p>
                                    </div>
                                @endif

                                @if ($record->fetal_heart_rate)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">DJJ</p>
                                        <p class="font-bold text-gray-700 mt-0.5">{{ $record->fetal_heart_rate }} bpm</p>
                                    </div>
                                @endif

                                @if ($record->fetal_position)
                                    <div class="bg-gray-50 rounded-xl p-2.5">
                                        <p class="text-gray-400">Letak Janin</p>
                                        <p class="font-bold text-gray-700 mt-0.5">
                                            {{ match ($record->fetal_position) {
                                                'head_down' => 'Kepala Bawah',
                                                'transverse' => 'Lintang',
                                                'breech' => 'Sungsang',
                                                default => $record->fetal_position,
                                            } }}
                                        </p>
                                    </div>
                                @endif

                            </div>

                            {{-- USG image --}}
                            @if ($record->usgImage)
                                <img src="{{ asset('storage/' . $record->usgImage->image_url) }}"
                                    class="w-full rounded-2xl object-cover max-h-48" alt="Foto USG">
                            @endif

                            {{-- Catatan Bidan --}}
                            @if ($record->validation?->notes)
                                <div class="bg-blue-50 rounded-xl p-3">
                                    <p class="text-[10px] font-bold text-blue-600 mb-1">Catatan Bidan</p>
                                    <p class="text-xs text-gray-600 leading-relaxed">{{ $record->validation->notes }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @elseif ($schedule)
                    {{-- ANC Terjadwal tapi belum input / belum dikunci --}}
                    <div class="bg-gray-50 border border-dashed border-gray-200 rounded-3xl p-4 flex items-center gap-3">
                        <div
                            class="w-10 h-10 bg-white border border-dashed border-gray-200 rounded-2xl flex items-center justify-center shrink-0">
                            <span class="text-sm font-bold text-gray-300">{{ $n }}</span>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-gray-400">ANC ke-{{ $n }}</p>
                            @if ($schedule->schedule_date)
                                <p class="text-xs text-blue-400 font-semibold">
                                    Terjadwal:
                                    {{ \Carbon\Carbon::parse($schedule->schedule_date)->translatedFormat('d M Y') }}
                                </p>
                            @endif
                        </div>
                        <span class="text-[10px] text-gray-400 bg-white border border-gray-200 px-2.5 py-1 rounded-xl">
                            {{ $record ? 'Menunggu validasi' : 'Belum input' }}
                        </span>
                    </div>
                @else
                    {{-- Slot belum dijadwalkan --}}
                    <div
                        class="bg-gray-50 border border-dashed border-gray-100 rounded-3xl p-4 flex items-center gap-3 opacity-50">
                        <div
                            class="w-10 h-10 bg-white border border-dashed border-gray-200 rounded-2xl flex items-center justify-center shrink-0">
                            <span class="text-sm font-bold text-gray-300">{{ $n }}</span>
                        </div>
                        <p class="text-sm font-bold text-gray-400">ANC ke-{{ $n }}</p>
                        <span
                            class="ml-auto text-[10px] text-gray-300 bg-white border border-gray-100 px-2.5 py-1 rounded-xl">
                            Belum dijadwalkan
                        </span>
                    </div>
                @endif
            @endforeach
        </section>

        {{-- RIWAYAT KONDISI --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">Riwayat Kondisi</h3>
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">

                @php
                    $conditionDefs = [
                        'preeclampsia' => [
                            'label' => 'Preeklamsia',
                            'icon' => 'fa-heart-pulse',
                            'bg' => 'bg-red-50',
                            'color' => 'text-red-400',
                        ],
                        'kek' => [
                            'label' => 'KEK',
                            'icon' => 'fa-weight-scale',
                            'bg' => 'bg-orange-50',
                            'color' => 'text-orange-400',
                        ],
                        'anemia' => [
                            'label' => 'Anemia',
                            'icon' => 'fa-droplet',
                            'bg' => 'bg-blue-50',
                            'color' => 'text-blue-300',
                        ],
                    ];

                    // Ambil semua kondisi yang pernah ada untuk ibu ini
                    $allDetected =
                        $pregnancy?->ancSchedules
                            ->map(fn($s) => $s->ancRecord)
                            ->filter()
                            ->flatMap(fn($r) => $r->detectedConditions)
                            ->groupBy('condition_type') ?? collect();
                @endphp

                @foreach ($conditionDefs as $type => $def)
                    @php
                        $conds = $allDetected->get($type, collect());
                        $latestCond = $conds->sortByDesc('detected_at')->first();
                        $hasHistory = $conds->whereIn('status', ['confirmed', 'detected'])->isNotEmpty();
                        $ancNumber = null;
                        if ($latestCond) {
                            $ancNumber = $pregnancy?->ancSchedules->firstWhere(
                                'id',
                                $latestCond->ancRecord?->schedule_id,
                            )?->anc_number;
                        }
                    @endphp

                    @unless ($loop->first)
                        <div class="h-px bg-gray-50"></div>
                    @endunless

                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 {{ $def['bg'] }} rounded-xl flex items-center justify-center shrink-0">
                            <i
                                class="fa-solid {{ $def['icon'] }} {{ $hasHistory ? $def['color'] : 'text-gray-300' }} text-sm"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-xs font-bold {{ $hasHistory ? 'text-gray-800' : 'text-gray-400' }}">
                                {{ $def['label'] }}
                            </p>
                            <p class="text-[10px] {{ $hasHistory ? 'text-gray-400' : 'text-gray-300' }}">
                                @if ($latestCond && $ancNumber)
                                    Terdeteksi ANC ke-{{ $ancNumber }} ·
                                    {{ match ($latestCond->status) {
                                        'confirmed' => 'Dikonfirmasi Bidan',
                                        'rejected' => 'Ditolak Bidan',
                                        'detected' => 'Menunggu konfirmasi',
                                        default => $latestCond->status,
                                    } }}
                                @else
                                    Tidak terdeteksi
                                @endif
                            </p>
                        </div>
                        <span
                            class="text-[9px] font-bold px-2 py-1 rounded-full {{ $hasHistory ? ($type === 'preeclampsia' ? 'bg-red-50 text-red-500' : ($type === 'kek' ? 'bg-orange-50 text-orange-500' : 'bg-yellow-50 text-yellow-600')) : 'bg-green-50 text-green-500' }}">
                            {{ $hasHistory ? 'Pernah Terjadi' : 'Normal' }} </span>
                    </div>
                @endforeach

            </div>
        </section>

        {{-- CATATAN REKOMENDASI DOKTER --}}
        <section class="space-y-4">
            <div class="flex items-center justify-between px-1">
                <h3 class="text-sm font-bold text-gray-700">Catatan Rekomendasi Dokter</h3>
                @if ($recommendations->isNotEmpty())
                    <span class="text-[10px] font-bold bg-blue-50 text-blue-600 px-2.5 py-0.5 rounded-full">
                        {{ $recommendations->count() }} catatan
                    </span>
                @endif
            </div>

            {{-- Form Tulis Rekomendasi --}}
            <form method="POST" action="{{ route('doctor.patients.recommendations.store', $motherProfile->id) }}"
                class="bg-white border border-blue-100 rounded-3xl p-5 shadow-sm space-y-4">
                @csrf

                <div class="space-y-1.5">
                    <label for="follow_up_level" class="text-xs font-bold text-gray-600 px-1">
                        Tingkat Tindak Lanjut
                    </label>
                    <select id="follow_up_level" name="follow_up_level" required
                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                        <option value="routine" @selected(old('follow_up_level', 'routine') === 'routine')>
                            Kontrol Rutin
                        </option>
                        <option value="monitor" @selected(old('follow_up_level') === 'monitor')>
                            Perlu Pemantauan
                        </option>
                        <option value="refer" @selected(old('follow_up_level') === 'refer')>
                            Rujukan Faskes Lanjutan
                        </option>
                    </select>
                </div>

                @if ($ancRecordsForSelect->isNotEmpty())
                    <div class="space-y-1.5">
                        <label for="anc_record_id" class="text-xs font-bold text-gray-600 px-1">
                            Tautkan ke Kunjungan ANC (Opsional)
                        </label>
                        <select id="anc_record_id" name="anc_record_id"
                            class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">
                            <option value="">Umum (seluruh kehamilan)</option>
                            @foreach ($ancRecordsForSelect as $sched)
                                <option value="{{ $sched->ancRecord->id }}" @selected((string) old('anc_record_id') === (string) $sched->ancRecord->id)>
                                    ANC ke-{{ $sched->anc_number }} —
                                    {{ $sched->schedule_date->translatedFormat('d M Y') }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="space-y-1.5">
                    <label for="recommendation" class="text-xs font-bold text-gray-600 px-1">
                        Isi Catatan Rekomendasi
                    </label>
                    <textarea id="recommendation" name="recommendation" rows="3" required minlength="5" maxlength="1000"
                        placeholder="Tulis saran tindak lanjut, nutrisi, pemantauan tekanan darah/Hb, atau indikasi rujukan untuk Bidan & Ibu..."
                        class="w-full px-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-xs text-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-300">{{ old('recommendation') }}</textarea>
                </div>

                <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold py-3.5 rounded-2xl shadow-sm transition flex items-center justify-center gap-2">
                    <i class="fa-solid fa-paper-plane text-xs"></i>
                    Simpan & Kirim Rekomendasi
                </button>
            </form>

            {{-- Riwayat Rekomendasi --}}
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                @forelse ($recommendations as $rec)
                    <div class="space-y-2">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-xs font-bold text-gray-800">{{ $rec->doctor_display_name }}</p>
                                <p class="text-[10px] text-gray-400">
                                    {{ $rec->doctor_specialization }}
                                    @if ($rec->ancRecord?->schedule)
                                        · ANC ke-{{ $rec->ancRecord->schedule->anc_number }}
                                    @endif
                                    · {{ $rec->created_at->translatedFormat('d M Y, H:i') }}
                                </p>
                            </div>
                            <span
                                class="text-[9px] font-bold px-2.5 py-1 rounded-full whitespace-nowrap {{ $rec->level_badge_class }}">
                                {{ $rec->level_label }}
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 leading-relaxed bg-gray-50 rounded-2xl p-3">
                            {{ $rec->recommendation }}
                        </p>
                    </div>
                    @unless ($loop->last)
                        <div class="h-px bg-gray-100"></div>
                    @endunless
                @empty
                    <div class="text-center py-3">
                        <p class="text-xs font-semibold text-gray-400">Belum ada catatan rekomendasi</p>
                        <p class="text-[10px] text-gray-300 mt-0.5">
                            Rekomendasi yang Anda tulis akan langsung tampil pada layar Bidan pendamping dan Ibu Hamil.
                        </p>
                    </div>
                @endforelse
            </div>
        </section>
    </main>

@endsection

@push('scripts')
    <script>
        function toggleAncDetail(id) {
            const el = document.getElementById(id);
            const btn = document.querySelector(`[data-id="${id}"]`);
            const isHidden = el.classList.contains('hidden');
            el.classList.toggle('hidden');
            btn.textContent = isHidden ? 'Tutup' : 'Detail';
        }
    </script>
@endpush
