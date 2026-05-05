@extends('layouts.mother-plain')

@section('title', 'BumilSehat - Input ANC')

@section('content')

    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur-sm z-40">
        <a href="{{ route('mother.anc.history') }}"
            class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-800">Input Data ANC</h1>
            <p class="text-[11px] text-gray-400">Pemeriksaan Ke-{{ $schedule->anc_number }}</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pb-10 pt-5 space-y-6">

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <div class="bg-rose-50 border border-rose-200 rounded-3xl p-4 text-xs text-rose-700">
            <div class="flex gap-3">
                <i class="fa-solid fa-circle-info mt-0.5"></i>
                <p class="leading-relaxed">
                    Data ini akan divalidasi oleh Bidan. Isi sesuai hasil pemeriksaan.
                    Data tidak bisa diubah setelah dikunci Bidan.
                </p>
            </div>
        </div>

        <form method="POST" action="{{ route('mother.anc.store', $schedule) }}" enctype="multipart/form-data"
            class="space-y-6">
            @csrf

            {{-- Antropometri --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-weight-scale text-rose-400"></i> Antropometri
                </h3>
                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">BB (kg)</label>
                        <input type="number" name="weight" step="0.1"
                            value="{{ old('weight', $existingRecord?->weight) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="0.0">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">TB (cm)</label>
                        <input type="number" name="height" step="0.1"
                            value="{{ old('height', $existingRecord?->height) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="0.0">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">LILA (cm)</label>
                        <input type="number" name="lila" step="0.1"
                            value="{{ old('lila', $existingRecord?->lila) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="0.0">
                    </div>
                </div>
            </div>

            {{-- Tanda Vital --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-heart-pulse text-rose-400"></i> Tanda Vital
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">Sistolik</label>
                        <input type="number" name="systolic" value="{{ old('systolic', $existingRecord?->systolic) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="120">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">Diastolik</label>
                        <input type="number" name="diastolic" value="{{ old('diastolic', $existingRecord?->diastolic) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="80">
                    </div>
                </div>
            </div>

            {{-- Laboratorium --}}
            <div class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 flex items-center gap-2">
                    <i class="fa-solid fa-flask text-rose-400"></i> Laboratorium
                </h3>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">HB (g/dL)</label>
                        <input type="number" name="hb" step="0.1" value="{{ old('hb', $existingRecord?->hb) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="12.0">
                    </div>
                    <div>
                        <label class="text-[11px] font-semibold text-gray-500 ml-1 mb-1 block">DJJ (bpm)</label>
                        <input type="number" name="fetal_heart_rate"
                            value="{{ old('fetal_heart_rate', $existingRecord?->fetal_heart_rate) }}"
                            class="w-full px-3 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 outline-none text-sm text-center font-bold"
                            placeholder="140">
                    </div>
                </div>
            </div>

            {{-- Status TT --}}
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">Status Imunisasi TT</label>
                <div class="flex gap-3">
                    <label
                        class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer
                    {{ old('tt_status', $existingRecord?->tt_status) === 'done' ? 'border-green-400 bg-green-50' : 'border-gray-200' }}">
                        <input type="radio" name="tt_status" value="done" class="hidden"
                            {{ old('tt_status', $existingRecord?->tt_status) === 'done' ? 'checked' : '' }}>
                        <i
                            class="fa-solid fa-check-circle {{ old('tt_status', $existingRecord?->tt_status) === 'done' ? 'text-green-500' : 'text-gray-300' }}"></i>
                        <span class="text-sm font-semibold">Sudah</span>
                    </label>
                    <label
                        class="flex-1 flex items-center gap-2 p-3 rounded-xl border-2 cursor-pointer
                    {{ old('tt_status', $existingRecord?->tt_status) === 'not_done' ? 'border-red-300 bg-red-50' : 'border-gray-200' }}">
                        <input type="radio" name="tt_status" value="not_done" class="hidden"
                            {{ old('tt_status', $existingRecord?->tt_status) === 'not_done' ? 'checked' : '' }}>
                        <i
                            class="fa-solid fa-times-circle {{ old('tt_status', $existingRecord?->tt_status) === 'not_done' ? 'text-red-400' : 'text-gray-300' }}"></i>
                        <span class="text-sm font-semibold">Belum</span>
                    </label>
                </div>
            </div>

            {{-- Letak Janin --}}
            <div class="space-y-2">
                <label class="text-sm font-bold text-gray-700">Letak Janin</label>
                <select name="fetal_position"
                    class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-white focus:border-rose-500 outline-none text-sm">
                    <option value="">Pilih letak janin</option>
                    <option value="head_down"
                        {{ old('fetal_position', $existingRecord?->fetal_position) === 'head_down' ? 'selected' : '' }}>
                        Kepala (Normal)
                    </option>
                    <option value="transverse"
                        {{ old('fetal_position', $existingRecord?->fetal_position) === 'transverse' ? 'selected' : '' }}>
                        Lintang
                    </option>
                    <option value="breech"
                        {{ old('fetal_position', $existingRecord?->fetal_position) === 'breech' ? 'selected' : '' }}>
                        Sungsang
                    </option>
                </select>
            </div>

            {{-- USG (wajib ANC ke-1 dan ke-5) --}}
            @if ($usgRequired)
                <div class="space-y-2">
                    <label class="text-sm font-bold text-gray-700">
                        Foto USG
                        <span class="text-rose-500">*</span>
                        <span class="text-[10px] font-normal text-gray-400 ml-1">Wajib untuk ANC
                            ke-{{ $schedule->anc_number }}</span>
                    </label>
                    @if ($existingRecord?->usgImage)
                        <div class="relative">
                            <img src="{{ asset('storage/' . $existingRecord->usgImage->image_url) }}"
                                class="w-full rounded-2xl object-cover max-h-48" alt="USG">
                            <p class="text-[11px] text-gray-400 mt-1">Upload baru untuk mengganti foto lama.</p>
                        </div>
                    @endif
                    <label
                        class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-rose-300 rounded-2xl cursor-pointer hover:bg-rose-50 transition">
                        <i class="fa-solid fa-camera text-rose-400 text-2xl mb-2"></i>
                        <span class="text-xs text-gray-500">Ketuk untuk upload foto USG</span>
                        <input type="file" name="usg_image" accept="image/*" class="hidden" id="usgInput"
                            onchange="previewUSG(this)">
                    </label>
                    <img id="usgPreview" src="" class="hidden w-full rounded-2xl object-cover max-h-48"
                        alt="Preview">
                </div>
            @endif

            <button type="submit"
                class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                Simpan Data ANC
            </button>

        </form>
    </main>

@endsection

@push('scripts')
    <script>
        function previewUSG(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    const preview = document.getElementById('usgPreview');
                    preview.src = e.target.result;
                    preview.classList.remove('hidden');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Radio button TT styling
        document.querySelectorAll('input[name="tt_status"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('input[name="tt_status"]').forEach(r => {
                    const label = r.closest('label');
                    const icon = label.querySelector('i');
                    if (r.value === 'done') {
                        label.classList.toggle('border-green-400', r.checked);
                        label.classList.toggle('bg-green-50', r.checked);
                        label.classList.toggle('border-gray-200', !r.checked);
                        icon.classList.toggle('text-green-500', r.checked);
                        icon.classList.toggle('text-gray-300', !r.checked);
                    } else {
                        label.classList.toggle('border-red-300', r.checked);
                        label.classList.toggle('bg-red-50', r.checked);
                        label.classList.toggle('border-gray-200', !r.checked);
                        icon.classList.toggle('text-red-400', r.checked);
                        icon.classList.toggle('text-gray-300', !r.checked);
                    }
                });
            });
        });
    </script>
@endpush
