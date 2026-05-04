@extends('layouts.app')

@section('title', 'BumilSehat - HPHT')
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
                    <i class="fa-solid fa-calendar-day text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wide">HPHT Bunda</h1>
                <p class="text-xs opacity-80 mt-1">Langkah terakhir sebelum dashboard</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <div class="flex items-center justify-between">
                <a href="{{ route('register.mother.identity') }}"
                    class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
                    <i class="fa-solid fa-arrow-left"></i>
                </a>
                <div class="text-right">
                    <p class="text-xs text-gray-400 font-medium">
                        Langkah <span class="text-rose-500 font-semibold">3</span> dari 3
                    </p>
                    <div class="flex justify-end gap-1.5 mt-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                    </div>
                </div>
            </div>

            <div class="bg-rose-50 border border-rose-200 rounded-3xl p-4 text-xs text-rose-700">
                <div class="flex gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <p class="leading-relaxed">
                        <b>HPHT</b> adalah <i>Hari Pertama Haid Terakhir</i>.
                        Tanggal ini digunakan untuk menghitung usia kehamilan dan perkiraan hari lahir (HPL).
                    </p>
                </div>
            </div>

            {{-- Info bidan pendamping --}}
            <div class="bg-white border-2 border-rose-50 rounded-4xl p-5 shadow-sm flex items-center gap-4">
                <div
                    class="w-14 h-14 bg-linear-to-tr from-rose-100 to-rose-200 rounded-2xl flex items-center justify-center text-rose-500 text-2xl shadow-inner">
                    <i class="fa-solid fa-user-nurse"></i>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] font-bold text-rose-400 uppercase tracking-widest">Bidan Pendamping</p>
                    @if ($midwife)
                        <h4 class="font-bold text-gray-800">{{ $midwife->name }}</h4>
                        <p class="text-[10px] text-gray-400">Sudah ditentukan berdasarkan wilayah</p>
                    @else
                        <h4 class="font-bold text-gray-800">Akan ditentukan otomatis</h4>
                        <p class="text-[10px] text-gray-400">Berdasarkan Kelurahan & RW</p>
                    @endif
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register.mother.hpht.store') }}" class="space-y-5">
                @csrf

                <div>
                    <label class="block text-sm font-semibold text-gray-600 ml-1 mb-2">Tanggal HPHT</label>
                    <input id="hpht" type="date" name="hpht_date" required
                        class="w-full px-4 py-4 rounded-xl border-2 border-rose-100 bg-rose-50/10 focus:bg-white focus:border-rose-500 outline-none transition-all text-gray-700 text-lg font-medium">
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">Pilih tanggal pertama haid terakhir</p>
                </div>

                <div id="preview" class="hidden bg-rose-50 border border-rose-200 rounded-3xl p-4 text-sm space-y-2">
                    <p class="font-bold text-rose-500">Ringkasan Kehamilan</p>
                    <p>Usia Kehamilan: <span id="week" class="font-bold"></span> minggu</p>
                    <p>Perkiraan HPL: <span id="hpl" class="font-bold"></span></p>
                </div>

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-4 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                    Simpan & Masuk Dashboard
                </button>
            </form>
        </div>

        <div class="h-2 bg-rose-500"></div>
    </div>
@endsection

@push('scripts')
    <script>
        document.getElementById('hpht').addEventListener('change', function() {
            const hpht = new Date(this.value);
            const today = new Date();
            const days = Math.floor((today - hpht) / (1000 * 60 * 60 * 24));
            const weeks = Math.max(0, Math.floor(days / 7));
            const hpl = new Date(hpht);
            hpl.setDate(hpl.getDate() + 280);

            document.getElementById('week').textContent = weeks;
            document.getElementById('hpl').textContent = hpl.toLocaleDateString('id-ID', {
                day: 'numeric',
                month: 'long',
                year: 'numeric'
            });
            document.getElementById('preview').classList.remove('hidden');
        });
    </script>
@endpush
