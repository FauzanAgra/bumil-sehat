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
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-gray-700">Antropometri</h3>
            <div class="grid grid-cols-3 gap-3">
                <div class="bg-rose-50 rounded-2xl p-3 text-center">
                    <p class="text-[10px] text-gray-500">BB</p>
                    <p class="text-base font-bold text-gray-800">{{ $ancRecord->weight ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">kg</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-3 text-center">
                    <p class="text-[10px] text-gray-500">TB</p>
                    <p class="text-base font-bold text-gray-800">{{ $ancRecord->height ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">cm</p>
                </div>
                <div class="bg-rose-50 rounded-2xl p-3 text-center">
                    <p class="text-[10px] text-gray-500">LILA</p>
                    <p
                        class="text-base font-bold {{ $ancRecord->lila && $ancRecord->lila < 23.5 ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $ancRecord->lila ?? '-' }}
                    </p>
                    <p class="text-[10px] text-gray-400">cm</p>
                </div>
            </div>
        </div>

        {{-- Tanda Vital --}}
        <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">
            <h3 class="text-sm font-bold text-gray-700">Tanda Vital & Lab</h3>
            <div class="grid grid-cols-2 gap-3">
                <div class="bg-blue-50 rounded-2xl p-3">
                    <p class="text-[10px] text-gray-500">Tekanan Darah</p>
                    <p
                        class="text-base font-bold {{ $ancRecord->systolic >= 140 || $ancRecord->diastolic >= 90 ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $ancRecord->systolic && $ancRecord->diastolic ? $ancRecord->systolic . '/' . $ancRecord->diastolic : '-' }}
                    </p>
                    <p class="text-[10px] text-gray-400">mmHg</p>
                </div>
                <div class="bg-blue-50 rounded-2xl p-3">
                    <p class="text-[10px] text-gray-500">HB</p>
                    <p
                        class="text-base font-bold {{ $ancRecord->hb && $ancRecord->hb < 11 ? 'text-red-500' : 'text-gray-800' }}">
                        {{ $ancRecord->hb ?? '-' }}
                    </p>
                    <p class="text-[10px] text-gray-400">g/dL</p>
                </div>
                <div class="bg-blue-50 rounded-2xl p-3">
                    <p class="text-[10px] text-gray-500">DJJ</p>
                    <p class="text-base font-bold text-gray-800">{{ $ancRecord->fetal_heart_rate ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400">bpm</p>
                </div>
                <div class="bg-blue-50 rounded-2xl p-3">
                    <p class="text-[10px] text-gray-500">Status TT</p>
                    <p
                        class="text-sm font-bold {{ $ancRecord->tt_status === 'done' ? 'text-green-600' : 'text-gray-800' }}">
                        {{ match ($ancRecord->tt_status) {
                            'done' => 'Sudah',
                            'not_done' => 'Belum',
                            default => '-',
                        } }}
                    </p>
                </div>
            </div>
            <div class="bg-blue-50 rounded-2xl p-3">
                <p class="text-[10px] text-gray-500">Letak Janin</p>
                <p class="text-sm font-bold text-gray-800">
                    {{ match ($ancRecord->fetal_position) {
                        'head_down' => 'Kepala (Normal)',
                        'transverse' => 'Lintang',
                        'breech' => 'Sungsang',
                        default => '-',
                    } }}
                </p>
            </div>
        </div>

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
                    <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-5 space-y-4">
                        <h3 class="text-sm font-bold text-yellow-700">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            Kondisi Terdeteksi Sistem
                        </h3>
                        <p class="text-xs text-gray-600 leading-relaxed">
                            Konfirmasi atau tolak setiap kondisi berdasarkan penilaian klinis Bidan.
                        </p>

                        @foreach ($ancRecord->detectedConditions as $i => $condition)
                            <div class="bg-white rounded-2xl p-4 space-y-3">
                                <input type="hidden" name="conditions[{{ $i }}][id]"
                                    value="{{ $condition->id }}">

                                <div class="flex items-center justify-between">
                                    <p class="text-sm font-bold text-gray-800">{{ $condition->label }}</p>
                                    <span
                                        class="text-[10px] bg-yellow-100 text-yellow-600 px-2 py-0.5 rounded-full font-semibold">
                                        Terdeteksi
                                    </span>
                                </div>

                                <div class="flex gap-3">
                                    <label
                                        class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition
                                    {{ old("conditions.$i.action") === 'confirm' ? 'border-red-400 bg-red-50' : 'border-gray-200' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]"
                                            value="confirm" class="hidden"
                                            {{ old("conditions.$i.action") === 'confirm' ? 'checked' : '' }}>
                                        <i class="fa-solid fa-circle-check text-red-400"></i>
                                        <span class="text-sm font-semibold text-red-600">Konfirmasi</span>
                                    </label>
                                    <label
                                        class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition
                                    {{ old("conditions.$i.action") === 'reject' ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                                        <input type="radio" name="conditions[{{ $i }}][action]" value="reject"
                                            class="hidden" {{ old("conditions.$i.action") === 'reject' ? 'checked' : '' }}>
                                        <i class="fa-solid fa-circle-xmark text-green-500"></i>
                                        <span class="text-sm font-semibold text-green-600">Tolak</span>
                                    </label>
                                </div>

                                <input type="text" name="conditions[{{ $i }}][notes]"
                                    value="{{ old("conditions.$i.notes") }}" placeholder="Catatan kondisi (opsional)"
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:outline-none focus:border-rose-300">
                            </div>
                        @endforeach
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

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                    Simpan & Kunci Data ANC
                </button>
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

    </main>

@endsection

@push('scripts')
    <script>
        // Radio button styling untuk kondisi
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                const group = this.name;
                document.querySelectorAll(`input[name="${group}"]`).forEach(r => {
                    const label = r.closest('label');
                    const isConfirm = r.value === 'confirm';
                    if (r.checked) {
                        label.classList.add(isConfirm ? 'border-red-400' : 'border-green-400');
                        label.classList.add(isConfirm ? 'bg-red-50' : 'bg-green-50');
                        label.classList.remove('border-gray-200');
                    } else {
                        label.classList.remove('border-red-400', 'bg-red-50', 'border-green-400',
                            'bg-green-50');
                        label.classList.add('border-gray-200');
                    }
                });
            });
        });
    </script>
@endpush
