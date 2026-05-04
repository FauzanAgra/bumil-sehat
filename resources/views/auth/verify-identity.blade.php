@extends('layouts.app')

@section('title', 'BumilSehat - Verifikasi Identitas')
@section('body-class', 'bg-rose-50 min-h-screen flex items-center justify-center text-gray-800')

@section('content')
    <div
        class="w-full max-w-md bg-white sm:rounded-4xl sm:shadow-2xl h-screen sm:h-[90vh] overflow-hidden flex flex-col fade-in">

        <div class="h-32 bg-rose-500 rounded-b-3xl flex items-center justify-center text-white relative">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
            <div class="relative z-10 text-center">
                <div
                    class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                    <i class="fa-solid fa-id-card text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wide">Verifikasi Identitas</h1>
                <p class="text-xs opacity-80 mt-1">Agar bidan dapat mendampingi Bunda</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <div class="flex items-center justify-between">
                <a href="{{ route('register.mother') }}"
                    class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="text-right">
                    <p class="text-xs text-gray-400 font-medium">
                        Langkah <span class="text-rose-500 font-semibold">2</span> dari 3
                    </p>
                    <div class="flex justify-end gap-1.5 mt-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div>
                    </div>
                </div>
            </div>

            <div class="bg-rose-50 border border-rose-200 rounded-3xl p-4 text-xs text-rose-700">
                <div class="flex gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <p class="leading-relaxed">
                        Data ini digunakan untuk <b>rekam medis</b> dan <b>penentuan Bidan Pendamping</b>.
                        Informasi Bunda aman dan tidak dibagikan ke pihak lain.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.mother.identity.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">NIK (KTP)</label>
                    <input type="text" name="nik" value="{{ old('nik') }}" required maxlength="16"
                        class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm font-mono tracking-widest"
                        placeholder="16 digit NIK">
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">Digunakan sebagai identitas rekam medis</p>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">Tanggal Lahir</label>
                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                </div>

                <div class="pt-2 border-t border-dashed border-rose-200">
                    <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">Alamat Domisili</label>
                    <textarea name="address" required rows="2"
                        class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm resize-none"
                        placeholder="Nama jalan, nomor rumah...">{{ old('address') }}</textarea>
                </div>

                <div>
                    <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">Kelurahan</label>
                    <select name="subdistrict_id" id="subdistrict_id" required onchange="filterArea()"
                        class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                        <option value="">Pilih Kelurahan</option>
                        @foreach ($subdistricts as $s)
                            <option value="{{ $s->id }}" {{ old('subdistrict_id') == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">Digunakan untuk menentukan Bidan Pendamping</p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">RW</label>
                        <select name="area_id" id="area_id" required onchange="checkMidwife()"
                            class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm">
                            <option value="">Pilih dulu</option>
                            @foreach ($areas as $area)
                                <option value="{{ $area->id }}" data-subdistrict="{{ $area->subdistrict_id }}"
                                    data-rw="{{ $area->rw_number }}"
                                    data-midwife="{{ $area->activeMidwifeArea?->midwife?->name ?? '' }}"
                                    {{ old('area_id') == $area->id ? 'selected' : '' }}>
                                    RW {{ $area->rw_number }}
                                </option>
                            @endforeach
                        </select>
                        <p class="text-[10px] text-gray-400 mt-1 ml-1">Menentukan wilayah bidan</p>
                    </div>
                    <div>
                        <label class="text-xs font-bold uppercase text-gray-500 ml-1 mb-1 block">RT</label>
                        <input type="text" name="rt" value="{{ old('rt') }}" maxlength="10"
                            class="w-full px-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="001">
                        <p class="text-[10px] text-gray-400 mt-1 ml-1">Identitas alamat saja</p>
                    </div>
                </div>

                {{-- Alert: belum ada bidan --}}
                <div id="noBidanAlert"
                    class="hidden bg-yellow-50 border border-yellow-200 rounded-2xl p-4 text-xs text-yellow-700">
                    <div class="flex gap-3">
                        <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                        <p class="leading-relaxed">
                            <b>Bidan Pendamping belum tersedia</b> untuk wilayah ini.
                            Pendaftaran tetap dapat dilanjutkan dan Bidan akan ditentukan oleh Admin.
                        </p>
                    </div>
                </div>

                {{-- Preview bidan ditemukan --}}
                <div id="bidanPreview" class="hidden bg-green-50 border border-green-200 rounded-2xl p-4">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 bg-green-100 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-user-nurse text-green-600 text-sm"></i>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-green-600 uppercase tracking-wide">Bidan Pendamping
                                Ditemukan</p>
                            <p id="bidanPreviewName" class="text-sm font-bold text-gray-800"></p>
                        </div>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95 mt-4">
                    Simpan & Lanjutkan
                </button>
            </form>
        </div>

        <div class="h-2 bg-rose-500"></div>
    </div>
@endsection

@push('scripts')
    <script>
        const allAreaOptions = Array.from(document.getElementById('area_id').options).slice(1);

        function filterArea() {
            const subdistrictId = document.getElementById('subdistrict_id').value;
            const areaSelect = document.getElementById('area_id');

            areaSelect.innerHTML = '<option value="">Pilih RW</option>';
            document.getElementById('noBidanAlert').classList.add('hidden');
            document.getElementById('bidanPreview').classList.add('hidden');

            if (!subdistrictId) return;

            const filtered = allAreaOptions.filter(o => o.dataset.subdistrict === subdistrictId);
            filtered.length === 0 ?
                areaSelect.innerHTML = '<option value="">Belum ada RW</option>' :
                filtered.forEach(o => areaSelect.appendChild(o.cloneNode(true)));
        }

        function checkMidwife() {
            const selected = document.getElementById('area_id').selectedOptions[0];
            const noBidan = document.getElementById('noBidanAlert');
            const preview = document.getElementById('bidanPreview');

            if (!selected || !selected.value) {
                noBidan.classList.add('hidden');
                preview.classList.add('hidden');
                return;
            }

            const midwifeName = selected.dataset.midwife;
            if (midwifeName) {
                preview.classList.remove('hidden');
                noBidan.classList.add('hidden');
                document.getElementById('bidanPreviewName').textContent = midwifeName;
            } else {
                preview.classList.add('hidden');
                noBidan.classList.remove('hidden');
            }
        }

        // Restore old values jika ada validation error
        const oldSubdistrict = "{{ old('subdistrict_id') }}";
        if (oldSubdistrict) {
            document.getElementById('subdistrict_id').value = oldSubdistrict;
            filterArea();
            const oldArea = "{{ old('area_id') }}";
            if (oldArea) {
                document.getElementById('area_id').value = oldArea;
                checkMidwife();
            }
        }
    </script>
@endpush
