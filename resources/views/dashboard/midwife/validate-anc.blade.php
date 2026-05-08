@extends('layouts.midwife-plain')

@section('title', 'BumilSehat - Validasi ANC')

@section('content')

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

        @if ($ancRecord->is_locked)
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-lock mr-1"></i> Data sudah dikunci dan tidak dapat diubah.
            </div>
        @endif

        {{-- Info jadwal --}}
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
                    Disubmit: {{ $ancRecord->submitted_at?->translatedFormat('d F Y, H:i') }} WIB
                </p>
            </div>
        </section>

        {{-- Antropometri --}}
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
                    <span
                        class="text-sm font-bold text-gray-800">{{ $ancRecord->weight ? $ancRecord->weight . ' kg' : '—' }}</span>
                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-ruler-vertical text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">Tinggi Badan</span>
                    </div>
                    <span
                        class="text-sm font-bold text-gray-800">{{ $ancRecord->height ? $ancRecord->height . ' cm' : '—' }}</span>
                </div>

                <div class="flex items-center justify-between py-3">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-orange-50 rounded-xl flex items-center justify-center text-orange-400">
                            <i class="fa-solid fa-circle-dot text-xs"></i>
                        </div>
                        <span class="text-sm text-gray-600">LILA</span>
                    </div>
                    <span
                        class="text-sm font-bold {{ $ancRecord->lila && $ancRecord->lila < 23.5 ? 'text-orange-600' : 'text-gray-800' }}">
                        {{ $ancRecord->lila ? $ancRecord->lila . ' cm' : '—' }}
                        @if ($ancRecord->lila && $ancRecord->lila < 23.5)
                            ⚠️
                        @endif
                    </span>
                </div>

            </div>
        </section>

        {{-- Tanda Vital --}}
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
                    @php $isHighBp = ($ancRecord->systolic >= 140 || $ancRecord->diastolic >= 90); @endphp
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
                    <span
                        class="text-sm font-bold {{ $ancRecord->hb && $ancRecord->hb < 11 ? 'text-red-600' : 'text-gray-800' }}">
                        {{ $ancRecord->hb ? $ancRecord->hb . ' g/dL' : '—' }}
                        @if ($ancRecord->hb && $ancRecord->hb < 11)
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
                        {{ match ($ancRecord->tt_status) {'done' => 'Sudah','not_done' => 'Belum',default => '—'} }}
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

        {{-- USG --}}
        @if ($ancRecord->usgImage)
            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
                <h3 class="text-sm font-bold text-gray-700">Foto USG</h3>
                <img src="{{ asset('storage/' . $ancRecord->usgImage->image_url) }}"
                    class="w-full rounded-2xl object-cover" alt="USG">
            </div>
        @endif

        {{-- Form Validasi --}}
        @if (!$ancRecord->is_locked)
            <form method="POST" action="{{ route('midwife.anc.validate.store', $ancRecord->id) }}" class="space-y-5">
                @csrf

                {{-- Kondisi terdeteksi --}}
                @if ($ancRecord->detectedConditions->isNotEmpty())
                    <div class="space-y-3">
                        <div class="flex items-center gap-2 px-1">
                            <i class="fa-solid fa-stethoscope text-rose-500 text-xs"></i>
                            <h3 class="text-sm font-bold text-gray-700">Konfirmasi Kondisi</h3>
                        </div>
                        <p class="text-xs text-gray-500 px-1 leading-relaxed">
                            Bidan perlu mengkonfirmasi setiap kondisi yang terdeteksi sistem. Keputusan Bidan bersifat
                            final.
                        </p>

                        @foreach ($ancRecord->detectedConditions as $i => $condition)
                            @php
                                $alertConfig = match ($condition->condition_type) {
                                    'preeclampsia' => [
                                        'bg' => 'bg-red-50 border-red-200',
                                        'iconBg' => 'bg-red-100',
                                        'iconColor' => 'text-red-500',
                                        'icon' => 'fa-heart-pulse',
                                        'titleColor' => 'text-red-700',
                                        'triggerText' =>
                                            'TD ' .
                                            $ancRecord->systolic .
                                            '/' .
                                            $ancRecord->diastolic .
                                            ' mmHg (≥ 140/90)',
                                    ],
                                    'kek' => [
                                        'bg' => 'bg-orange-50 border-orange-200',
                                        'iconBg' => 'bg-orange-100',
                                        'iconColor' => 'text-orange-500',
                                        'icon' => 'fa-weight-scale',
                                        'titleColor' => 'text-orange-700',
                                        'triggerText' => 'LILA ' . $ancRecord->lila . ' cm (< 23,5 cm)',
                                    ],
                                    'anemia' => [
                                        'bg' => 'bg-yellow-50 border-yellow-200',
                                        'iconBg' => 'bg-yellow-100',
                                        'iconColor' => 'text-yellow-600',
                                        'icon' => 'fa-droplet',
                                        'titleColor' => 'text-yellow-700',
                                        'triggerText' => 'Hb ' . $ancRecord->hb . ' g/dL (< 11 g/dL)',
                                    ],
                                    default => [
                                        'bg' => 'bg-gray-50 border-gray-200',
                                        'iconBg' => 'bg-gray-100',
                                        'iconColor' => 'text-gray-500',
                                        'icon' => 'fa-circle-exclamation',
                                        'titleColor' => 'text-gray-700',
                                        'triggerText' => '',
                                    ],
                                };
                            @endphp

                            {{-- Alert card per kondisi --}}
                            <div class="border rounded-2xl p-4 flex items-start gap-3 {{ $alertConfig['bg'] }}">
                                <div
                                    class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $alertConfig['iconBg'] }}">
                                    <i
                                        class="fa-solid {{ $alertConfig['icon'] }} text-sm {{ $alertConfig['iconColor'] }}"></i>
                                </div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold {{ $alertConfig['titleColor'] }}">{{ $condition->label }}
                                    </p>
                                    <p class="text-xs mt-0.5 opacity-80">{{ $alertConfig['triggerText'] }}</p>
                                    <p class="text-[10px] mt-1 opacity-60">Terdeteksi otomatis oleh sistem</p>
                                </div>
                                <span
                                    class="text-[9px] font-bold px-2 py-1 rounded-full whitespace-nowrap opacity-70
                    {{ $alertConfig['iconBg'] }} {{ $alertConfig['iconColor'] }}">
                                    Belum dikonfirmasi
                                </span>
                            </div>

                            {{-- Form konfirmasi per kondisi --}}
                            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">
                                <input type="hidden" name="conditions[{{ $i }}][id]"
                                    value="{{ $condition->id }}">

                                <div class="flex gap-2">
                                    <label
                                        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 cursor-pointer transition
                        {{ old("conditions.$i.action") === 'confirm' ? 'border-red-400 bg-red-50 text-red-600' : 'border-gray-200 text-gray-500 hover:border-red-300 hover:bg-red-50 hover:text-red-600' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]"
                                            value="confirm" class="hidden"
                                            {{ old("conditions.$i.action") === 'confirm' ? 'checked' : '' }}>
                                        <span class="text-xs font-semibold">✓ Konfirmasi Kondisi</span>
                                    </label>
                                    <label
                                        class="flex-1 flex items-center justify-center gap-2 py-2.5 rounded-xl border-2 cursor-pointer transition
                        {{ old("conditions.$i.action") === 'reject' ? 'border-green-400 bg-green-50 text-green-600' : 'border-gray-200 text-gray-500 hover:border-green-300 hover:bg-green-50 hover:text-green-600' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]"
                                            value="reject" class="hidden"
                                            {{ old("conditions.$i.action") === 'reject' ? 'checked' : '' }}>
                                        <span class="text-xs font-semibold">✗ Bukan Kondisi Ini</span>
                                    </label>
                                </div>

                                <div>
                                    <label class="text-[10px] font-bold text-gray-400 uppercase tracking-wide">Catatan
                                        Bidan (opsional)</label>
                                    <textarea name="conditions[{{ $i }}][notes]" rows="2" placeholder="Tambahkan catatan klinis..."
                                        class="w-full mt-1.5 rounded-xl border border-gray-200 px-3 py-2.5 text-xs focus:ring-rose-200 focus:ring-2 outline-none resize-none text-gray-700 leading-relaxed">{{ old("conditions.$i.notes") }}</textarea>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    {{-- State tidak ada kondisi --}}
                    <div class="bg-green-50 border border-green-200 rounded-2xl p-4 flex items-center gap-3">
                        <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check text-green-600 text-base"></i>
                        </div>
                        <div class="flex-1">
                            <p class="text-sm font-bold text-green-700">Tidak Ada Kondisi Terdeteksi</p>
                            <p class="text-xs text-green-600 mt-0.5 leading-relaxed">
                                Semua nilai dalam batas normal. Tidak ada konfirmasi kondisi yang diperlukan.
                            </p>
                        </div>
                    </div>
                @endif

                {{-- Catatan Bidan --}}
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">Catatan Bidan</label>
                    <textarea name="notes" rows="3" placeholder="Tambahkan catatan atau rekomendasi untuk ibu hamil..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm resize-none">{{ old('notes') }}</textarea>
                </div>

                <div class="bg-rose-50 border border-rose-200 rounded-2xl px-4 py-3 text-xs text-rose-700">
                    <i class="fa-solid fa-circle-info mr-1"></i>
                    Setelah disimpan, data ANC akan <strong>dikunci</strong> dan tidak dapat diubah lagi.
                </div>

                <button type="button" id="openLockBtn" onclick="openLockModal()"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-500/30 transition-all active:scale-95"
                    disabled>
                    <i class="fa-solid fa-lock mr-2"></i>
                    Kunci & Selesaikan Validasi
                </button>
                <p id="lockHint" class="text-[10px] text-center text-gray-400 leading-relaxed">
                    @if ($ancRecord->detectedConditions->isNotEmpty())
                        Konfirmasi semua kondisi terlebih dahulu untuk mengaktifkan tombol kunci.
                    @else
                        Siap dikunci.
                    @endif
                </p>
            </form>
        @else
            {{-- Sudah divalidasi --}}
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

        {{-- Lock Modal --}}
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
                    <button onclick="document.getElementById('validationForm').submit()"
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

    </main>

@endsection

@push('scripts')
    <script>
        const conditionCount = {{ $ancRecord->detectedConditions->count() }};
        let confirmedCount = 0;

        function updateLockButton() {
            const allRadios = document.querySelectorAll('input[type="radio"]');
            const answeredGroups = new Set();
            allRadios.forEach(r => {
                if (r.checked) answeredGroups.add(r.name.replace(/\[action\]$/, ''));
            });

            const lockBtn = document.getElementById('openLockBtn');
            const lockHint = document.getElementById('lockHint');
            const conditionGroups = document.querySelectorAll('input[name$="[action]"]');
            const totalGroups = new Set([...conditionGroups].map(r => r.name.replace(/\[action\]$/, ''))).size;

            if (answeredGroups.size >= totalGroups) {
                lockBtn.removeAttribute('disabled');
                lockHint.textContent = 'Semua kondisi sudah dikonfirmasi. Siap dikunci.';
                lockHint.className = 'text-[10px] text-center text-green-500 leading-relaxed';
            }
        }

        // Jika tidak ada kondisi, langsung aktifkan tombol
        if (conditionCount === 0) {
            document.getElementById('openLockBtn')?.removeAttribute('disabled');
            const hint = document.getElementById('lockHint');
            if (hint) {
                hint.textContent = 'Siap dikunci.';
                hint.className = 'text-[10px] text-center text-green-500';
            }
        }

        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const group = this.name;
                document.querySelectorAll(`input[name="${group}"]`).forEach(r => {
                    const label = r.closest('label');
                    const isConfirm = r.value === 'confirm';
                    if (r.checked) {
                        label.classList.add(isConfirm ? 'border-red-400' : 'border-green-400');
                        label.classList.add(isConfirm ? 'bg-red-50' : 'bg-green-50');
                        label.classList.add(isConfirm ? 'text-red-600' : 'text-green-600');
                        label.classList.remove('border-gray-200', 'text-gray-500');
                    } else {
                        label.classList.remove('border-red-400', 'bg-red-50', 'text-red-600',
                            'border-green-400', 'bg-green-50', 'text-green-600');
                        label.classList.add('border-gray-200', 'text-gray-500');
                    }
                });
                updateLockButton();
            });
        });

        // Tambahkan id pada form
        document.querySelector('form[action*="validate"]')?.setAttribute('id', 'validationForm');

        function openLockModal() {
            const noteVal = document.querySelector('textarea[name="notes"]')?.value?.trim();
            document.getElementById('summaryNote').textContent = noteVal ? '✓ Ada catatan' : '— Tidak ada catatan';

            const radios = [...document.querySelectorAll('input[type="radio"]:checked')];
            if (radios.length === 0) {
                document.getElementById('summaryConditions').textContent = '✓ Tidak ada kondisi';
            } else {
                document.getElementById('summaryConditions').textContent =
                    radios.map(r => r.value === 'confirm' ? '⚠️ Dikonfirmasi' : '✓ Ditolak').join(' · ');
            }

            document.getElementById('lockModal').classList.remove('hidden');
        }
    </script>
@endpush
