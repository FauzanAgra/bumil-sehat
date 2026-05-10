@extends('layouts.midwife-plain')

@section('title', 'BumilSehat - Tambah Jadwal ANC')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('midwife.schedules') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Tambah Jadwal ANC</h1>
            <p class="text-xs text-gray-400">Tentukan pertemuan & tanggal kunjungan</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-10">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('midwife.schedules.store') }}" class="space-y-6">
            @csrf

            {{-- ─── STEP 1: PILIH IBU ──────────────────────────────── --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        1
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Pilih Ibu Hamil</h3>
                </div>

                <div class="relative">
                    <i
                        class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                    <input type="text" id="searchInput" placeholder="Cari nama ibu..."
                        oninput="filterPatients(this.value)"
                        class="w-full pl-10 pr-4 py-3.5 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                </div>

                <input type="hidden" name="mother_profile_id" id="selectedMotherId"
                    value="{{ old('mother_profile_id', $selectedMother?->id) }}">

                <div id="patientList" class="space-y-2.5 max-h-64 overflow-y-auto">
                    @foreach ($mothers as $m)
                        @php
                            $completedAnc = $m->activePregnancy?->completed_anc_count ?? 0;
                            $scheduledAnc = $m->activePregnancy?->ancSchedules->pluck('anc_number')->toArray() ?? [];
                            $isSelected = old('mother_profile_id', $selectedMother?->id) == $m->id;
                            $hasRisk = $m->has_risk ?? false;
                        @endphp

                        <button type="button"
                            onclick="selectPatient(
                                {{ $m->id }},
                                '{{ addslashes($m->user->name) }}',
                                {{ $m->activePregnancy?->gestational_week ?? 0 }},
                                {{ $completedAnc }},
                                {{ json_encode($scheduledAnc) }},
                                {{ $hasRisk ? 'true' : 'false' }},
                                this
                            )"
                            class="patient-card w-full bg-white border {{ $isSelected ? 'border-rose-400 bg-rose-50' : 'border-gray-100' }}
                                rounded-3xl p-4 flex items-center gap-3 hover:border-rose-300 hover:shadow-md transition text-left">

                            <div class="w-10 h-10 rounded-full border-2 border-gray-200 p-0.5 shrink-0">
                                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $m->user->name }}"
                                    class="rounded-full" alt="{{ $m->user->name }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-gray-800">{{ $m->user->name }}</p>
                                    @if ($hasRisk)
                                        <span
                                            class="text-[9px] bg-red-50 text-red-500 font-bold px-1.5 py-0.5 rounded-full">
                                            ⚠️ Risiko
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400">
                                    {{ $m->activePregnancy?->gestational_week ?? '—' }} minggu
                                    · ANC ke-{{ $completedAnc }} selesai
                                </p>
                            </div>
                        </button>
                    @endforeach
                </div>

                {{-- Preview nama yang dipilih --}}
                <div id="selectedPreview"
                    class="{{ $selectedMother ? '' : 'hidden' }} bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3 flex items-center justify-between">
                    <div>
                        <p class="text-xs font-semibold text-rose-600">
                            Dipilih: <span id="selectedName">{{ $selectedMother?->user->name }}</span>
                        </p>
                        <p id="selectedWeek" class="text-[10px] text-rose-400 mt-0.5"></p>
                    </div>
                    <button type="button" onclick="resetPatient()"
                        class="text-xs text-rose-400 font-semibold hover:text-rose-600">
                        Ganti
                    </button>
                </div>

                {{-- B4: Risk banner — tampil jika pasien dipilih & ada kondisi risiko --}}
                <div id="riskBanner" class="hidden bg-red-50 border border-red-200 rounded-2xl p-3 flex gap-2 items-start">
                    <i class="fa-solid fa-triangle-exclamation text-red-400 text-xs mt-0.5 shrink-0"></i>
                    <p class="text-xs text-red-600 leading-relaxed">
                        Pasien ini memiliki kondisi risiko aktif. Pertimbangkan interval kunjungan yang lebih dekat.
                    </p>
                </div>
            </section>

            {{-- ─── STEP 2: PEMERIKSAAN KE- ─────────────────────────── --}}
            <section class="space-y-3" id="step2"
                style="{{ $selectedMother ? '' : 'opacity:0.5;pointer-events:none' }}">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        2
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Pemeriksaan Ke-</h3>
                </div>

                {{-- Grid tombol ANC 1–6 --}}
                <div id="ancGrid" class="grid grid-cols-3 gap-2.5">
                    @for ($i = 1; $i <= 6; $i++)
                        <button type="button" onclick="selectAnc({{ $i }}, this)"
                            id="ancBtn{{ $i }}"
                            class="anc-btn py-3 rounded-2xl border-2 border-gray-200 text-xs font-bold text-gray-500 hover:border-rose-300 transition
                                {{ old('anc_number') == $i ? 'border-rose-500 bg-rose-50 text-rose-500' : '' }}">
                            ANC ke-{{ $i }}
                        </button>
                    @endfor
                </div>

                <input type="hidden" name="anc_number" id="selectedAncNumber" value="{{ old('anc_number') }}">

                {{-- B4: USG note banner — tampil saat ANC ke-1 atau ke-5 dipilih --}}
                <div id="usgNoteBanner"
                    class="hidden bg-blue-50 border border-blue-200 rounded-2xl p-3 flex gap-2 items-start">
                    <i class="fa-solid fa-circle-info text-blue-400 text-xs mt-0.5 shrink-0"></i>
                    <p class="text-xs text-blue-600 leading-relaxed">
                        <strong id="usgNoteText"></strong>
                        Ingatkan ibu untuk mempersiapkan hasil USG saat kunjungan.
                    </p>
                </div>
            </section>

            {{-- ─── STEP 3: TANGGAL & WAKTU ─────────────────────────── --}}
            <section class="space-y-4" id="step3"
                style="{{ $selectedMother ? '' : 'opacity:0.5;pointer-events:none' }}">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        3
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">Tanggal & Waktu</h3>
                </div>

                {{-- Summary chip pilihan sebelumnya --}}
                <div id="summaryChips"
                    class="{{ $selectedMother ? '' : 'hidden' }} bg-gray-50 rounded-2xl px-4 py-3 flex items-center gap-3">
                    <div id="summaryChipPatient"
                        class="text-xs font-bold text-rose-600 bg-rose-50 px-3 py-1 rounded-full truncate max-w-[45%]">
                        {{ $selectedMother?->user->name }}
                    </div>
                    <i class="fa-solid fa-chevron-right text-gray-300 text-[10px] shrink-0"></i>
                    <div id="summaryChipAnc" class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full">
                        @if (old('anc_number'))
                            ANC ke-{{ old('anc_number') }}
                        @endif
                    </div>
                </div>

                {{-- Input tanggal --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-500 px-1">
                        Tanggal Kunjungan
                    </label>
                    <div class="relative">
                        <div
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400 pointer-events-none">
                            <i class="fa-regular fa-calendar text-xs"></i>
                        </div>
                        <input type="date" name="schedule_date" value="{{ old('schedule_date') }}"
                            min="{{ today()->toDateString() }}"
                            class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                    </div>
                </div>

                {{-- B4: Jam — select preset, bukan free-type --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-500 px-1">
                        Jam Kunjungan
                        <span class="font-normal text-gray-400">(opsional)</span>
                    </label>
                    <div class="relative">
                        <div
                            class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400 pointer-events-none">
                            <i class="fa-regular fa-clock text-xs"></i>
                        </div>
                        <select name="schedule_time"
                            class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700
                                   focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition appearance-none">
                            <option value="">— Pilih jam —</option>
                            @foreach (['08:00', '09:00', '10:00', '11:00', '13:00', '14:00'] as $time)
                                <option value="{{ $time }}"
                                    {{ old('schedule_time') === $time ? 'selected' : '' }}>
                                    {{ $time }} WIB
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- Catatan / lokasi --}}
                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-500 px-1">
                        Catatan / Lokasi
                        <span class="font-normal text-gray-400">(opsional)</span>
                    </label>
                    <input type="text" name="note" value="{{ old('note') }}"
                        placeholder="contoh: Puskesmas Mawar, ruang KIA"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                </div>
            </section>

            <button type="submit"
                class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                Simpan Jadwal ANC
            </button>

        </form>
    </main>

@endsection

@push('scripts')
    <script>
        const allPatients = Array.from(document.querySelectorAll('.patient-card'));

        // ──────────────────────────────────────────────────────────────
        // Pencarian pasien
        // ──────────────────────────────────────────────────────────────
        function filterPatients(query) {
            allPatients.forEach(card => {
                const name = card.querySelector('p')?.textContent?.toLowerCase() ?? '';
                card.style.display = name.includes(query.toLowerCase()) ? '' : 'none';
            });
        }

        // ──────────────────────────────────────────────────────────────
        // Pilih pasien
        // ──────────────────────────────────────────────────────────────
        function selectPatient(id, name, week, completedAnc, scheduledAnc, hasRisk, btn) {
            document.getElementById('selectedMotherId').value = id;
            document.getElementById('selectedName').textContent = name;
            document.getElementById('selectedWeek').textContent = week + ' minggu kehamilan';

            document.getElementById('selectedPreview').classList.remove('hidden');

            // Summary chip
            document.getElementById('summaryChips').classList.remove('hidden');
            document.getElementById('summaryChipPatient').textContent = name;

            // B4: Risk banner
            document.getElementById('riskBanner').classList.toggle('hidden', !hasRisk);

            // Style kartu terpilih
            allPatients.forEach(card => {
                card.classList.remove('border-rose-400', 'bg-rose-50');
                card.classList.add('border-gray-100');
            });
            btn.classList.add('border-rose-400', 'bg-rose-50');
            btn.classList.remove('border-gray-100');

            // Aktifkan step 2 & 3
            document.getElementById('step2').style = '';
            document.getElementById('step3').style = '';

            // Update state tombol ANC grid
            updateAncButtons(completedAnc, scheduledAnc);
        }

        function resetPatient() {
            document.getElementById('selectedMotherId').value = '';
            document.getElementById('selectedPreview').classList.add('hidden');
            document.getElementById('riskBanner').classList.add('hidden');
            document.getElementById('summaryChips').classList.add('hidden');
            document.getElementById('step2').style = 'opacity:0.5;pointer-events:none';
            document.getElementById('step3').style = 'opacity:0.5;pointer-events:none';
            document.getElementById('selectedAncNumber').value = '';
            document.getElementById('usgNoteBanner').classList.add('hidden');

            allPatients.forEach(card => {
                card.classList.remove('border-rose-400', 'bg-rose-50');
                card.classList.add('border-gray-100');
            });

            // Reset tombol ANC
            for (let i = 1; i <= 6; i++) {
                const btn = document.getElementById('ancBtn' + i);
                btn.disabled = false;
                btn.textContent = 'ANC ke-' + i;
                btn.className =
                    'anc-btn py-3 rounded-2xl border-2 border-gray-200 text-xs font-bold text-gray-500 hover:border-rose-300 transition';
            }
        }

        // ──────────────────────────────────────────────────────────────
        // Update state tombol ANC grid
        // completed = ANC yang sudah selesai (is_locked)
        // scheduled = ANC yang sudah terjadwal (tapi belum tentu selesai)
        // ──────────────────────────────────────────────────────────────
        function updateAncButtons(completed, scheduled) {
            for (let i = 1; i <= 6; i++) {
                const btn = document.getElementById('ancBtn' + i);

                if (i <= completed) {
                    // ANC sudah selesai — disabled dengan tanda centang
                    btn.disabled = true;
                    btn.textContent = 'ANC ke-' + i + ' ✓';
                    btn.className =
                        'anc-btn py-3 rounded-2xl border-2 border-gray-100 bg-gray-50 text-xs font-bold text-gray-300 cursor-not-allowed';
                } else if (scheduled.includes(i)) {
                    // Sudah terjadwal tapi belum selesai — disabled dengan ikon kalender
                    btn.disabled = true;
                    btn.textContent = 'ANC ke-' + i + ' 📅';
                    btn.className =
                        'anc-btn py-3 rounded-2xl border-2 border-blue-100 bg-blue-50 text-xs font-bold text-blue-400 cursor-not-allowed';
                } else {
                    // Bisa dipilih
                    btn.disabled = false;
                    btn.textContent = 'ANC ke-' + i;
                    btn.className =
                        'anc-btn py-3 rounded-2xl border-2 border-gray-200 text-xs font-bold text-gray-600 hover:border-rose-300 hover:bg-rose-50 hover:text-rose-600 transition';
                }
            }
        }

        // ──────────────────────────────────────────────────────────────
        // Pilih nomor ANC
        // ──────────────────────────────────────────────────────────────
        function selectAnc(num, btn) {
            document.getElementById('selectedAncNumber').value = num;
            document.getElementById('summaryChipAnc').textContent = 'ANC ke-' + num;

            // Update styling semua tombol aktif
            document.querySelectorAll('.anc-btn:not(:disabled)').forEach(b => {
                b.classList.remove('border-rose-500', 'bg-rose-50', 'text-rose-600');
                b.classList.add('border-gray-200', 'text-gray-600');
            });
            btn.classList.add('border-rose-500', 'bg-rose-50', 'text-rose-600');
            btn.classList.remove('border-gray-200', 'text-gray-600');

            // B4: USG note banner
            const usgBanner = document.getElementById('usgNoteBanner');
            const usgText = document.getElementById('usgNoteText');
            if ([1, 5].includes(num)) {
                usgText.textContent = 'ANC ke-' + num + ' wajib USG.';
                usgBanner.classList.remove('hidden');
            } else {
                usgBanner.classList.add('hidden');
            }
        }

        // ──────────────────────────────────────────────────────────────
        // Restore old state setelah validation error
        // ──────────────────────────────────────────────────────────────
        @if (old('mother_profile_id') && old('anc_number'))
            document.getElementById('step2').style = '';
            document.getElementById('step3').style = '';
            document.getElementById('summaryChips').classList.remove('hidden');
            document.getElementById('summaryChipAnc').textContent = 'ANC ke-{{ old('anc_number') }}';

            const oldAncNum = {{ old('anc_number') }};
            if ([1, 5].includes(oldAncNum)) {
                document.getElementById('usgNoteText').textContent = 'ANC ke-' + oldAncNum + ' wajib USG.';
                document.getElementById('usgNoteBanner').classList.remove('hidden');
            }
        @endif
    </script>
@endpush
