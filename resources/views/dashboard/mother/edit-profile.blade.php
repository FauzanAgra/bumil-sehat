@extends('layouts.mother-plain')

@section('title', 'BumilSehat - Edit Profil')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.setting') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Edit Profil</h1>
            <p class="text-xs text-gray-400">Perbarui informasi data Bunda</p>
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

        {{-- Avatar --}}
        <div class="flex flex-col items-center gap-3">
            <div class="relative">
                <div class="w-24 h-24 rounded-full border-4 border-rose-200 p-0.5 bg-white shadow-lg shadow-rose-100">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}"
                        class="rounded-full w-full h-full object-cover bg-rose-50" alt="avatar">
                </div>
            </div>
            <p class="text-xs text-gray-400">Avatar dibuat otomatis dari nama Bunda</p>
        </div>

        <form method="POST" action="{{ route('mother.profile.update') }}" class="space-y-6">
            @csrf

            {{-- Data Diri --}}
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Data Diri</h3>
                <div class="space-y-3">

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Nama Lengkap</label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-user text-xs"></i>
                            </div>
                            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">NIK <span
                                class="text-gray-300 font-normal">(tidak dapat diubah)</span></label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-gray-50 rounded-xl flex items-center justify-center text-gray-300">
                                <i class="fa-solid fa-id-card text-xs"></i>
                            </div>
                            <input type="text"
                                value="{{ $motherProfile ? $motherProfile->getMaskedNik('mother') : '—' }}"
                                readonly
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-dashed border-gray-200 text-sm text-gray-400 cursor-not-allowed font-mono">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Tanggal Lahir</label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-cake-candles text-xs"></i>
                            </div>
                            <input type="date" name="birth_date"
                                value="{{ old('birth_date', $motherProfile?->birth_date?->format('Y-m-d')) }}"
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Nomor HP</label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-phone text-xs"></i>
                            </div>
                            <input type="tel" name="phone" value="{{ old('phone', $user->phone) }}" required
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Email <span
                                class="text-gray-300 font-normal">(opsional)</span></label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-envelope text-xs"></i>
                            </div>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                        </div>
                    </div>
                </div>
            </section>

            {{-- Alamat --}}
            <section class="space-y-3">
                <h3 class="text-sm font-bold text-gray-700 px-1">Alamat</h3>
                <div class="space-y-3">

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Kelurahan</label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-map-pin text-xs"></i>
                            </div>
                            <select name="subdistrict_id" id="subdistrict_id" required onchange="handleSubdistrictChange()"
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition appearance-none">
                                @foreach ($subdistricts as $s)
                                    <option value="{{ $s->id }}"
                                        {{ old('subdistrict_id', $motherProfile?->subdistrict_id) == $s->id ? 'selected' : '' }}>
                                        {{ $s->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="flex gap-3">
                        <div class="flex-1 space-y-1.5">
                            <label class="text-xs font-bold text-gray-500 px-1">RW</label>
                            <select name="area_id" id="area_id" required onchange="handleWilayahChange()"
                                class="w-full px-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 transition">
                                @foreach ($areas as $area)
                                    <option value="{{ $area->id }}" data-subdistrict="{{ $area->subdistrict_id }}"
                                        {{ old('area_id', $motherProfile?->area_id) == $area->id ? 'selected' : '' }}>
                                        RW {{ $area->rw_number }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex-1 space-y-1.5">
                            <label class="text-xs font-bold text-gray-500 px-1">RT</label>
                            <input type="text" name="rt" value="{{ old('rt', $motherProfile?->rt) }}"
                                class="w-full px-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 transition"
                                placeholder="001">
                        </div>
                    </div>

                    <div id="wilayahWarning"
                        class="hidden bg-yellow-50 border border-yellow-200 rounded-2xl px-4 py-3 text-xs text-yellow-700">
                        <div class="flex gap-2 items-start">
                            <i class="fa-solid fa-triangle-exclamation mt-0.5 shrink-0"></i>
                            <p class="leading-relaxed">
                                Perubahan <strong>Kelurahan atau RW</strong> akan mempengaruhi penentuan Bidan Pendamping.
                            </p>
                        </div>
                    </div>

                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">Detail Alamat</label>
                        <div class="relative">
                            <div
                                class="absolute left-4 top-5 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                                <i class="fa-solid fa-house text-xs"></i>
                            </div>
                            <textarea name="address" rows="3"
                                class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700 focus:outline-none focus:ring-2 focus:ring-rose-200 transition resize-none leading-relaxed"
                                placeholder="Detail alamat lengkap">{{ old('address', $motherProfile?->address) }}</textarea>
                        </div>
                    </div>
                </div>
            </section>

            <button type="submit"
                class="w-full bg-linear-to-r from-rose-500 to-rose-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-200 active:scale-[0.98] transition-all">
                Simpan Perubahan
            </button>
        </form>
    </main>

@endsection

@push('scripts')
    <script>
        const initialSubdistrict = "{{ $motherProfile?->subdistrict_id }}";
        const initialArea = "{{ $motherProfile?->area_id }}";
        const subdistrictSelect = document.getElementById('subdistrict_id');
        const areaSelect = document.getElementById('area_id');
        const allAreaOptions = Array.from(areaSelect.options).filter(option => option.value !== '');

        function filterAreasForSubdistrict(subdistrictId, selectedAreaId = '') {
            const subId = String(subdistrictId || '');
            const filtered = subId
                ? allAreaOptions.filter(option => option.dataset.subdistrict === subId)
                : [];

            areaSelect.innerHTML = '';

            const placeholder = document.createElement('option');
            placeholder.value = '';
            placeholder.textContent = !subId
                ? 'Pilih Kelurahan dulu'
                : (filtered.length === 0 ? 'Belum ada RW' : 'Pilih RW');
            areaSelect.appendChild(placeholder);

            filtered.forEach(option => {
                const clone = option.cloneNode(true);
                clone.selected = String(selectedAreaId) === option.value;
                areaSelect.appendChild(clone);
            });

            if (!Array.from(areaSelect.options).some(option => option.selected && option.value !== '')) {
                areaSelect.value = '';
            }
        }

        function handleSubdistrictChange() {
            filterAreasForSubdistrict(subdistrictSelect.value);
            handleWilayahChange();
        }

        function handleWilayahChange() {
            const subdistrictId = subdistrictSelect.value;
            const areaId = areaSelect.value;
            const warning = document.getElementById('wilayahWarning');

            if (subdistrictId !== initialSubdistrict || areaId !== initialArea) {
                warning.classList.remove('hidden');
            } else {
                warning.classList.add('hidden');
            }
        }

        filterAreasForSubdistrict(
            subdistrictSelect.value,
            "{{ old('area_id', $motherProfile?->area_id) }}"
        );
        handleWilayahChange();
    </script>
@endpush
