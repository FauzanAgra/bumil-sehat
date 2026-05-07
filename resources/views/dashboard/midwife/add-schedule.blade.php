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

            {{-- Step 1: Pilih Ibu --}}
            <section class="space-y-3">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        1
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">
                        Pilih Ibu Hamil
                    </h3>
                </div>

                <div class="relative">
                    <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 text-xs">
                    </i>
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

                            $isSelected = old('mother_profile_id', $selectedMother?->id) == $m->id;
                        @endphp

                        <button type="button"
                            onclick="selectPatient({{ $m->id }},'{{ $m->user->name }}',{{ $m->activePregnancy?->gestational_week ?? 0 }},{{ $completedAnc }},
                        this)"
                            class="patient-card w-full bg-white border {{ $isSelected ? 'border-rose-400 bg-rose-50' : 'border-gray-100' }}
                    rounded-3xl p-4 flex items-center gap-3 hover:border-rose-300 hover:shadow-md transition text-left">

                            <div class="w-10 h-10 rounded-full border-2 border-gray-200 p-0.5 shrink-0">
                                <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $m->user->name }}"
                                    class="rounded-full" alt="{{ $m->user->name }}">
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800">
                                    {{ $m->user->name }}
                                </p>
                                <p class="text-xs text-gray-400">
                                    {{ $m->activePregnancy?->gestational_week ?? '—' }} minggu
                                    · ANC ke-{{ $completedAnc }} selesai
                                </p>
                            </div>
                        </button>
                    @endforeach

                </div>

                {{-- Preview --}}
                <div id="selectedPreview"
                    class="{{ $selectedMother ? '' : 'hidden' }} bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3">
                    <p class="text-xs font-semibold text-rose-600">
                        Dipilih:
                        <span id="selectedName">
                            {{ $selectedMother?->user->name }}
                        </span>
                    </p>
                </div>
            </section>

            {{-- Step 2 --}}
            <section class="space-y-3" id="step2"
                style="{{ $selectedMother ? '' : 'opacity:0.5;pointer-events:none' }}">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        2
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">
                        Pemeriksaan Ke-
                    </h3>
                </div>

                <div id="ancButtons" class="flex gap-2 flex-wrap">
                    @for ($i = 1; $i <= 6; $i++)
                        <button type="button" onclick="selectAnc({{ $i }}, this)"
                            id="ancBtn{{ $i }}"
                            class="anc-btn w-12 h-12 rounded-2xl border-2 border-gray-200 text-sm font-bold text-gray-500 hover:border-rose-300 transition
                    {{ old('anc_number') == $i ? 'border-rose-500 bg-rose-50 text-rose-500' : '' }}">
                            {{ $i }}
                        </button>
                    @endfor
                </div>
                <input type="hidden" name="anc_number" id="selectedAncNumber" value="{{ old('anc_number') }}">
                <p id="ancInfo" class="text-[11px] text-gray-400 px-1"></p>
            </section>

            {{-- Step 3 --}}
            <section class="space-y-3" id="step3"
                style="{{ $selectedMother ? '' : 'opacity:0.5;pointer-events:none' }}">
                <div class="flex items-center gap-2 px-1">
                    <div
                        class="w-6 h-6 rounded-full bg-rose-500 text-white text-[10px] font-bold flex items-center justify-center shrink-0">
                        3
                    </div>
                    <h3 class="text-sm font-bold text-gray-700">
                        Tanggal & Waktu
                    </h3>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-500 px-1">
                            Tanggal
                        </label>
                        <input type="date" name="schedule_date" value="{{ old('schedule_date') }}"
                            min="{{ today()->toDateString() }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-semibold text-gray-500 px-1">
                            Jam
                            <span class="font-normal text-gray-400">
                                (opsional)
                            </span>
                        </label>
                        <input type="time" name="schedule_time" value="{{ old('schedule_time') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                    </div>

                </div>

                <div class="space-y-1.5">
                    <label class="text-xs font-semibold text-gray-500 px-1">
                        Catatan / Lokasi
                        <span class="font-normal text-gray-400">
                            (opsional)
                        </span>
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
        const allPatients =
            Array.from(document.querySelectorAll('.patient-card'));

        function filterPatients(query) {
            allPatients.forEach(card => {

                const name =
                    card.querySelector('p')
                    .textContent
                    .toLowerCase();

                card.style.display =
                    name.includes(query.toLowerCase()) ?
                    '' :
                    'none';

            });
        }

        function selectPatient(
            id,
            name,
            week,
            completedAnc,
            btn
        ) {
            document.getElementById('selectedMotherId').value = id;

            document.getElementById('selectedName').textContent = name;

            document.getElementById('selectedPreview')
                .classList.remove('hidden');

            // Style selected
            allPatients.forEach(card => {

                card.classList.remove(
                    'border-rose-400',
                    'bg-rose-50'
                );

                card.classList.add('border-gray-100');

            });

            btn.classList.add(
                'border-rose-400',
                'bg-rose-50'
            );

            btn.classList.remove('border-gray-100');

            // Enable step
            document.getElementById('step2').style = '';

            document.getElementById('step3').style = '';

            updateAncButtons(completedAnc);
        }

        function updateAncButtons(completed) {
            for (let i = 1; i <= 6; i++) {

                const btn =
                    document.getElementById('ancBtn' + i);

                if (i <= completed) {

                    btn.classList.add(
                        'opacity-40',
                        'cursor-not-allowed'
                    );

                    btn.disabled = true;

                } else {

                    btn.classList.remove(
                        'opacity-40',
                        'cursor-not-allowed'
                    );

                    btn.disabled = false;

                }

            }
        }

        function selectAnc(num, btn) {
            document.getElementById('selectedAncNumber').value = num;

            document.querySelectorAll('.anc-btn').forEach(button => {

                button.classList.remove(
                    'border-rose-500',
                    'bg-rose-50',
                    'text-rose-500'
                );

                button.classList.add(
                    'border-gray-200',
                    'text-gray-500'
                );

            });

            btn.classList.add(
                'border-rose-500',
                'bg-rose-50',
                'text-rose-500'
            );

            btn.classList.remove(
                'border-gray-200',
                'text-gray-500'
            );

            const isUsg = [1, 5].includes(num);

            document.getElementById('ancInfo').textContent =
                isUsg ?
                '📷 ANC ke-' + num + ' wajib menyertakan foto USG.' :
                '';
        }

        // Restore old state
        @if (old('mother_profile_id') && old('anc_number'))

            document.getElementById('step2').style = '';

            document.getElementById('step3').style = '';
        @endif
    </script>
@endpush
