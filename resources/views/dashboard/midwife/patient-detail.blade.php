@extends('layouts.midwife')

@section('title', 'BumilSehat - Detail Pasien')

@section('content')

    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40 shrink-0">
        <a href="{{ route('midwife.patients') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Detail Pasien</h1>
            <p class="text-xs text-gray-400">Ringkasan kehamilan & riwayat ANC</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-32">

        {{-- PROFIL --}}
        <section
            class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2rem] p-5 text-white relative overflow-hidden shadow-lg shadow-rose-200">

            <div class="absolute -right-4 -bottom-4 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>

            <div class="relative z-10 flex items-center gap-4">

                <div class="w-16 h-16 rounded-2xl border-2 border-white/40 p-0.5 shrink-0">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $motherProfile->user->name }}"
                        class="rounded-xl w-full h-full object-cover bg-rose-300" alt="{{ $motherProfile->user->name }}">
                </div>

                <div class="flex-1">

                    <p class="text-rose-100 text-[10px] font-bold uppercase tracking-widest">
                        Ibu Hamil
                    </p>

                    <h2 class="text-base font-bold leading-tight">
                        {{ $motherProfile->user->name }}
                    </h2>

                    <p class="text-rose-100 text-xs mt-0.5">
                        NIK:
                        {{ $motherProfile->getMaskedNik(auth()->user()->role) }}
                    </p>

                    <div class="flex items-center gap-2 mt-2 flex-wrap">

                        <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                            {{ $pregnancy?->gestational_week ?? '—' }} Minggu
                        </span>

                        @if ($pregnancy)
                            <span class="text-[10px] bg-white/25 px-2.5 py-1 rounded-full font-semibold">
                                HPL:
                                {{ $pregnancy->hpl_date->translatedFormat('d M Y') }}
                            </span>
                        @endif

                    </div>

                </div>

            </div>

        </section>

        {{-- ALERT RISIKO --}}
        @if ($activeConditions->isNotEmpty())

            <div class="bg-red-50 border border-red-200 rounded-2xl p-4 flex gap-3">

                <i class="fa-solid fa-triangle-exclamation text-red-400 mt-0.5 shrink-0"></i>

                <div>

                    <p class="text-xs font-bold text-red-700 mb-1">
                        Kondisi Aktif Terdeteksi
                    </p>

                    <div class="flex flex-wrap gap-1.5">

                        @foreach ($activeConditions as $condition)
                            <span
                                class="text-[10px]
                                {{ $conditionStyles[$condition->condition_type]['bg'] ?? 'bg-gray-100' }}
                                {{ $conditionStyles[$condition->condition_type]['text'] ?? 'text-gray-600' }}
                                font-semibold px-2 py-0.5 rounded-full">

                                ⚠️ {{ $condition->label }}

                            </span>
                        @endforeach

                    </div>

                </div>

            </div>

        @endif

        {{-- DATA KEHAMILAN --}}
        <section class="space-y-3">

            <h3 class="text-sm font-bold text-gray-700 px-1">
                Data Kehamilan
            </h3>

            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-0">

                <div class="flex items-center justify-between py-3 border-b border-gray-50">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                            <i class="fa-regular fa-calendar text-xs"></i>
                        </div>

                        <span class="text-sm text-gray-600">
                            HPHT
                        </span>

                    </div>

                    <span class="text-sm font-bold text-gray-800">
                        {{ $pregnancy?->hpht_date?->translatedFormat('d M Y') ?? '—' }}
                    </span>

                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 bg-blue-50 rounded-xl flex items-center justify-center text-blue-400">
                            <i class="fa-solid fa-baby text-xs"></i>
                        </div>

                        <span class="text-sm text-gray-600">
                            Usia Kehamilan
                        </span>

                    </div>

                    <span class="text-sm font-bold text-gray-800">
                        {{ $pregnancy?->gestational_week ?? '—' }} Minggu
                    </span>

                </div>

                <div class="flex items-center justify-between py-3 border-b border-gray-50">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 bg-purple-50 rounded-xl flex items-center justify-center text-purple-400">
                            <i class="fa-solid fa-star text-xs"></i>
                        </div>

                        <span class="text-sm text-gray-600">
                            HPL (Perkiraan)
                        </span>

                    </div>

                    <span class="text-sm font-bold text-gray-800">
                        {{ $pregnancy?->hpl_date?->translatedFormat('d M Y') ?? '—' }}
                    </span>

                </div>

                <div class="flex items-center justify-between py-3">

                    <div class="flex items-center gap-3">

                        <div class="w-8 h-8 bg-green-50 rounded-xl flex items-center justify-center text-green-400">
                            <i class="fa-solid fa-map-pin text-xs"></i>
                        </div>

                        <span class="text-sm text-gray-600">
                            Wilayah
                        </span>

                    </div>

                    <span class="text-sm font-bold text-gray-800">
                        {{ $motherProfile->subdistrict->name }}
                        · RW {{ $motherProfile->area->rw_number }}
                    </span>

                </div>

            </div>

        </section>

        {{-- TIMELINE ANC --}}
        <section class="space-y-4">

            <div class="flex justify-between items-center px-1">

                <h3 class="text-sm font-bold text-gray-700">
                    Timeline ANC (6 Pertemuan)
                </h3>

                <a href="{{ route('midwife.schedules.create', ['mother_id' => $motherProfile->id]) }}"
                    class="text-[11px] text-rose-500 font-bold flex items-center gap-1 hover:underline">

                    <i class="fa-solid fa-plus text-[9px]"></i>
                    Tambah Jadwal

                </a>

            </div>

            {{-- PROGRESS --}}
            <div class="px-1">

                <div class="flex justify-between text-[10px] text-gray-400 mb-1.5">

                    <span>Progress ANC</span>

                    <span class="font-semibold text-rose-500">
                        {{ $completedAnc }} dari 6 selesai
                    </span>

                </div>

                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">

                    <div class="h-full bg-linear-to-r from-rose-400 to-rose-500 rounded-full"
                        style="width: {{ $progressPercentage }}%">
                    </div>

                </div>

            </div>

            {{-- TIMELINE ITEMS --}}
            <div class="space-y-3">
                @foreach ($timeline as $item)
                    {{-- BELUM ADA JADWAL --}}
                    @if (!$item['schedule'])
                        <div
                            class="bg-gray-50 border border-dashed border-gray-200 rounded-3xl p-4 flex items-center gap-3">
                            <div
                                class="w-11 h-11 bg-white border border-dashed border-gray-200 rounded-2xl flex items-center justify-center shrink-0">
                                <span class="text-sm font-bold text-gray-300">
                                    {{ $item['anc_number'] }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-400">
                                    ANC ke-{{ $item['anc_number'] }}
                                </p>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    Belum dijadwalkan
                                </p>
                                @if ($item['need_usg'])
                                    <p class="text-[10px] text-blue-400 mt-0.5">
                                        🔵 Wajib USG
                                    </p>
                                @endif
                            </div>
                        </div>
                    @elseif ($item['is_locked'])
                        <div class="bg-white border border-gray-100 rounded-3xl p-4 shadow-sm flex items-center gap-3">
                            <div class="w-11 h-11 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-lock text-green-500 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <p class="text-sm font-bold text-gray-800">
                                        ANC ke-{{ $item['anc_number'] }}
                                    </p>
                                    @if ($item['need_usg'])
                                        <span
                                            class="text-[9px] bg-blue-50 text-blue-500 font-bold px-1.5 py-0.5 rounded-full">
                                            USG
                                        </span>
                                    @endif
                                </div>
                                <p class="text-xs text-gray-400 mt-0.5">
                                    {{ $item['schedule']->schedule_date->translatedFormat('d M Y') }}
                                    · Terkunci
                                </p>
                            </div>
                            <a href="{{ route('midwife.anc.validate', $item['record']->id) }}"
                                class="text-[11px] bg-green-50 text-green-600 font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap">
                                Lihat →
                            </a>
                        </div>
                    @elseif ($item['has_pending'] || $item['has_risk'])
                        <div class="bg-white border border-yellow-200 rounded-3xl p-4 shadow-sm flex items-center gap-3">

                            <div class="w-11 h-11 bg-yellow-50 rounded-2xl flex items-center justify-center shrink-0">

                                <i class="fa-solid fa-clipboard-check text-yellow-500 text-sm"></i>

                            </div>

                            <div class="flex-1 min-w-0">

                                <p class="text-sm font-bold text-gray-800">
                                    ANC ke-{{ $item['anc_number'] }}
                                </p>

                                <p class="text-xs text-yellow-500 font-semibold mt-0.5">

                                    {{ $item['has_risk'] ? '⚠️ Risiko terdeteksi' : '⏳ Menunggu Validasi' }}

                                </p>

                            </div>

                            <a href="{{ route('midwife.anc.validate', $item['record']->id) }}"
                                class="text-[11px] bg-yellow-400 text-white font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap shadow-sm">

                                Validasi →

                            </a>

                        </div>
                    @elseif ($item['is_missed'])
                        <div
                            class="bg-white border border-gray-100 rounded-3xl p-4 shadow-sm flex items-center gap-3 opacity-80">
                            <div class="w-11 h-11 bg-red-50 rounded-2xl flex items-center justify-center shrink-0">
                                <i class="fa-solid fa-calendar-xmark text-red-400 text-sm"></i>
                            </div>

                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-700">
                                    ANC ke-{{ $item['anc_number'] }}
                                </p>

                                <p class="text-xs text-gray-400 mt-0.5">
                                    ❌ ANC Terlewat · {{ $item['schedule']->schedule_date->translatedFormat('d M Y') }}
                                </p>
                            </div>

                            <span
                                class="text-[10px] bg-red-50 text-red-400 font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap">
                                Missed
                            </span>
                        </div>
                    @elseif ($item['is_scheduled'])
                        <div
                            class="bg-white border border-gray-100 rounded-3xl p-4 shadow-sm flex items-center gap-3 opacity-70">
                            <div class="w-11 h-11 bg-blue-50 rounded-2xl flex items-center justify-center shrink-0">
                                <i class="fa-regular fa-calendar text-blue-400 text-sm"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-bold text-gray-800">
                                    ANC ke-{{ $item['anc_number'] }}
                                </p>
                                <p class="text-xs text-blue-500 font-semibold mt-0.5">
                                    📅 Terjadwal:
                                    {{ $item['schedule']->schedule_date->translatedFormat('d M Y') }}
                                </p>
                            </div>

                            <div class="flex gap-2 shrink-0">
                                <button
                                    onclick="openEditModal({{ $item['schedule']->id }},'{{ $item['schedule']->schedule_date->format('Y-m-d') }}','{{ $item['anc_number'] }}')"
                                    class="text-[11px] bg-blue-50 text-blue-500 font-semibold px-3 py-1.5 rounded-xl whitespace-nowrap hover:bg-blue-100 transition">
                                    Edit
                                </button>
                            </div>

                        </div>
                    @endif
                @endforeach

            </div>

        </section>

        {{-- RIWAYAT KONDISI --}}
        <section class="space-y-3">

            <h3 class="text-sm font-bold text-gray-700 px-1">
                Riwayat Kondisi
            </h3>

            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-3">

                @forelse ($activeConditions as $condition)
                    <div class="flex items-center gap-3">
                        <div
                            class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 {{ $conditionStyles[$condition->condition_type]['bg'] ?? 'bg-gray-100' }}">
                            <i
                                class="fa-solid {{ $conditionStyles[$condition->condition_type]['icon'] ?? 'fa-circle' }}
                        {{ $conditionStyles[$condition->condition_type]['text'] ?? 'text-gray-500' }} text-sm">
                            </i>

                        </div>

                        <div class="flex-1">
                            <p class="text-xs font-bold text-gray-800">
                                {{ $condition->label }}
                            </p>
                            <p class="text-[10px] text-gray-400">
                                Terdeteksi ANC ke-{{ $condition->ancRecord?->schedule?->anc_number }} ·

                                @if ($condition->status === 'confirmed')
                                    Sudah dikonfirmasi
                                @elseif ($condition->status === 'rejected')
                                    Ditolak
                                @else
                                    Belum dikonfirmasi
                                @endif
                            </p>
                        </div>

                        @if ($condition->status === 'confirmed')
                            <span
                                class="text-[9px] font-bold bg-green-50 text-green-500 px-2 py-1 rounded-full whitespace-nowrap">
                                Confirmed
                            </span>
                        @elseif ($condition->status === 'rejected')
                            <span
                                class="text-[9px] font-bold bg-red-50 text-red-500 px-2 py-1 rounded-full whitespace-nowrap">
                                Rejected
                            </span>
                        @else
                            <span
                                class="text-[9px] font-bold bg-yellow-50 text-yellow-600 px-2 py-1 rounded-full whitespace-nowrap">
                                Pending
                            </span>
                        @endif

                    </div>

                    @unless ($loop->last)
                        <div class="h-px bg-gray-50"></div>
                    @endunless

                @empty

                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 bg-gray-50 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-circle-check text-gray-300 text-sm"></i>
                        </div>

                        <div class="flex-1">

                            <p class="text-xs font-bold text-gray-400">
                                Tidak ada kondisi risiko
                            </p>

                            <p class="text-[10px] text-gray-300">
                                Semua pemeriksaan normal
                            </p>

                        </div>

                        <span
                            class="text-[9px] font-bold bg-green-50 text-green-500 px-2 py-1 rounded-full whitespace-nowrap">
                            Normal
                        </span>

                    </div>
                @endforelse

            </div>

        </section>

        {{-- PEMANTAUAN TTD --}}
        <section class="space-y-3">
            <h3 class="text-sm font-bold text-gray-700 px-1">
                Pemantauan TTD
            </h3>

            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm space-y-4">

                <div class="flex items-center justify-between">

                    <div class="flex items-center gap-3">

                        <div class="w-9 h-9 bg-rose-50 rounded-xl flex items-center justify-center shrink-0">
                            <i class="fa-solid fa-pills text-rose-400 text-sm"></i>
                        </div>

                        <div>

                            <p class="text-sm font-bold text-gray-800">
                                Status TTD
                            </p>

                            <p class="text-xs text-gray-400">

                                @if ($ttdStatus?->is_active)
                                    Aktif sejak usia {{ $ttdStartedWeek }} minggu
                                @else
                                    Belum aktif
                                @endif

                            </p>

                        </div>

                    </div>

                    <span
                        class="text-[10px] px-2.5 py-1 rounded-full font-bold
                {{ $ttdStatus?->is_active ? 'bg-green-50 text-green-600' : 'bg-gray-100 text-gray-400' }}">

                        {{ $ttdStatus?->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-[10px] text-gray-400">
                        <span>Kepatuhan konsumsi</span>
                        <span class="font-bold text-rose-500">
                            {{ $ttdTaken }} / {{ $ttdTarget }} hari
                        </span>
                    </div>

                    <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                        <div class="h-full bg-linear-to-r from-rose-400 to-rose-500 rounded-full"
                            style="width: {{ $ttdPercentage }}%">
                        </div>
                    </div>
                    <p class="text-[10px] text-gray-400">
                        {{ $ttdPercentage }}% kepatuhan
                    </p>
                </div>

                <div class="border-t border-gray-50 pt-3 flex items-center justify-between">
                    <p class="text-xs text-gray-500">
                        Aktifkan TTD manual
                    </p>

                    <button id="ttdToggle" onclick="toggleTtd()"
                        class="w-12 h-6 rounded-full relative transition-all duration-300 flex items-center px-1
        {{ $ttdStatus?->is_active ? 'bg-rose-400 justify-end' : 'bg-gray-200 justify-start' }}">

                        <div class="w-4 h-4 bg-white rounded-full shadow-sm"></div>

                    </button>
                </div>
            </div>


        </section>

        {{-- KONTAK IBU --}}
        <section class="space-y-3">

            <h3 class="text-sm font-bold text-gray-700 px-1">
                Kontak Ibu
            </h3>

            <div class="bg-white border border-gray-100 rounded-3xl p-5 shadow-sm flex items-center gap-4">

                <div class="w-11 h-11 bg-green-50 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fa-brands fa-whatsapp text-green-500 text-lg"></i>
                </div>

                <div class="flex-1">

                    <p class="text-sm font-bold text-gray-800">
                        {{ $motherProfile->user->phone ?? '-' }}
                    </p>

                    <p class="text-xs text-gray-400">
                        WhatsApp aktif
                    </p>

                </div>

                @if ($motherProfile->user->phone)
                    <a href="https://wa.me/62{{ ltrim($motherProfile->user->phone, '0') }}" target="_blank"
                        class="text-[11px] bg-green-500 text-white font-semibold px-4 py-2 rounded-xl shadow-sm">

                        Hubungi

                    </a>
                @endif

            </div>

        </section>

        {{-- MODAL EDIT --}}
        <div id="editModal"
            class="fixed inset-0 bg-black/40 backdrop-blur-sm flex items-end justify-center z-[999] hidden">
            <div class="w-full max-w-md bg-white rounded-t-[2.5rem] p-6 space-y-5 shadow-2xl">
                <div class="w-12 h-1.5 bg-gray-200 rounded-full mx-auto"></div>
                <div class="text-center space-y-1 pt-1">
                    <div class="w-14 h-14 bg-blue-50 rounded-2xl flex items-center justify-center mx-auto">
                        <i class="fa-solid fa-calendar text-blue-500 text-xl"></i>
                    </div>
                    <h3 class="text-base font-bold text-gray-800 mt-2">
                        Edit Jadwal
                    </h3>
                    <p class="text-xs text-gray-500" id="editAncLabel"></p>
                </div>
                <form id="editForm" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="space-y-1.5">
                        <label class="text-xs font-bold text-gray-500 px-1">
                            Tanggal Baru
                        </label>
                        <input type="date" name="schedule_date" id="editScheduleDate"
                            class="w-full px-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700">
                    </div>
                    <div class="space-y-1.5 mt-4">
                        <label class="text-xs font-bold text-gray-500 px-1">Jam Kunjungan</label>
                        <select name="schedule_time" id="editScheduleTime"
                            class="w-full px-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-700">
                            <option value="">— Opsional —</option>
                            @foreach (['08:00', '09:00', '10:00', '11:00', '13:00', '14:00'] as $time)
                                <option value="{{ $time }}">{{ $time }} WIB</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="space-y-3 pt-4">
                        <button type="submit"
                            class="w-full bg-blue-500 hover:bg-blue-600 text-white font-bold py-3.5 rounded-2xl transition">
                            Simpan Perubahan
                        </button>
                        <button type="button" onclick="closeEditModal()"
                            class="w-full text-sm font-semibold text-gray-500 py-2 hover:text-gray-700 transition">
                            Batal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>

@endsection

@push('scripts')
    <script>
        function openEditModal(scheduleId, date, ancNumber, currentTime) {
            document.getElementById('editModal').classList.remove('hidden');
            document.getElementById('editAncLabel').innerText = `ANC ke-${ancNumber}`;
            document.getElementById('editScheduleDate').value = date;
            document.getElementById('editForm').action = `/midwife/schedules/${scheduleId}`;
            const timeSelect = document.getElementById('editScheduleTime');
            if (timeSelect && currentTime) timeSelect.value = currentTime;
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        async function toggleTtd() {
            const response = await fetch(
                "{{ route('midwife.pregnancies.toggle-ttd', $pregnancy) }}", {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                    }
                }
            );

            if (response.ok) {
                window.location.reload();
            }
        }
    </script>
@endpush
