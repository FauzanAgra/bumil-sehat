@extends('layouts.mother-plain')

@section('title', 'BumilSehat - Ubah Kata Sandi')

@section('content')

    <header class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.setting') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Ubah Kata Sandi</h1>
            <p class="text-xs text-gray-400">Perbarui kata sandi akun Bunda</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 space-y-6 pb-10">

        @if (session('success'))
            <div class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3">
                <i class="fa-solid fa-circle-check mr-1"></i> {{ session('success') }}
            </div>
        @endif

        {{-- Banner --}}
        <div
            class="bg-linear-to-br from-rose-500 to-rose-700 rounded-[2.5rem] p-6 text-white shadow-xl shadow-rose-200 relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
            <div class="relative z-10 flex items-center gap-4">
                <div class="w-14 h-14 bg-white/20 rounded-2xl flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-shield-halved text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-base font-bold leading-tight">Keamanan Akun</h2>
                    <p class="text-rose-100 text-xs mt-1 leading-relaxed">
                        Gunakan kata sandi yang kuat dan tidak mudah ditebak.
                    </p>
                </div>
            </div>
        </div>

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('mother.profile.password.update') }}" class="space-y-4">
            @csrf

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 px-1">Kata Sandi Lama</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                        <i class="fa-solid fa-lock text-xs"></i>
                    </div>
                    <input id="oldPassword" type="password" name="old_password" required
                        placeholder="Masukkan kata sandi lama"
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                    <button type="button" onclick="togglePass('oldPassword','eyeOld')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-400 transition">
                        <i id="eyeOld" class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 px-1">Kata Sandi Baru</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                        <i class="fa-solid fa-key text-xs"></i>
                    </div>
                    <input id="newPassword" type="password" name="password" required placeholder="Minimal 6 karakter"
                        oninput="checkStrength(this.value)"
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                    <button type="button" onclick="togglePass('newPassword','eyeNew')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-400 transition">
                        <i id="eyeNew" class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <div id="strengthWrapper" class="hidden px-1 space-y-1.5">
                    <div class="flex gap-1.5">
                        <div id="bar1" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                        <div id="bar2" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                        <div id="bar3" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                        <div id="bar4" class="h-1.5 flex-1 rounded-full bg-gray-200 transition-all duration-300"></div>
                    </div>
                    <p id="strengthLabel" class="text-[11px] text-gray-400"></p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="text-xs font-bold text-gray-600 px-1">Konfirmasi Kata Sandi Baru</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center text-rose-400">
                        <i class="fa-solid fa-lock-open text-xs"></i>
                    </div>
                    <input id="confirmPassword" type="password" name="password_confirmation" required
                        placeholder="Ulangi kata sandi baru" oninput="checkMatch()"
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300 transition">
                    <button type="button" onclick="togglePass('confirmPassword','eyeConfirm')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-400 transition">
                        <i id="eyeConfirm" class="fa-regular fa-eye"></i>
                    </button>
                </div>
                <p id="matchMsg" class="hidden text-[11px] px-1"></p>
            </div>

            <div class="bg-rose-50 rounded-2xl px-4 py-3 space-y-1.5">
                <p class="text-xs font-bold text-rose-500">Tips Kata Sandi Kuat</p>
                <ul class="text-[11px] text-gray-500 space-y-1">
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-rose-300 text-[10px]"></i> Minimal 6 karakter
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-rose-300 text-[10px]"></i> Kombinasi huruf besar & kecil
                    </li>
                    <li class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-rose-300 text-[10px]"></i> Mengandung angka atau simbol
                    </li>
                </ul>
            </div>

            <button type="submit"
                class="w-full bg-linear-to-r from-rose-500 to-rose-600 text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-200 active:scale-[0.98] transition-all">
                Simpan Kata Sandi
            </button>
        </form>
    </main>

@endsection

@push('scripts')
    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            input.type === 'password' ?
                (input.type = 'text', icon.classList.replace('fa-eye', 'fa-eye-slash')) :
                (input.type = 'password', icon.classList.replace('fa-eye-slash', 'fa-eye'));
        }

        function checkStrength(val) {
            const wrapper = document.getElementById('strengthWrapper');
            const label = document.getElementById('strengthLabel');
            const bars = [1, 2, 3, 4].map(i => document.getElementById('bar' + i));
            if (!val) {
                wrapper.classList.add('hidden');
                return;
            }
            wrapper.classList.remove('hidden');
            let score = 0;
            if (val.length >= 6) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;
            const colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];
            const labels = ['Sangat Lemah', 'Lemah', 'Cukup Kuat', 'Kuat'];
            const tc = ['text-red-400', 'text-orange-400', 'text-yellow-500', 'text-green-500'];
            bars.forEach((bar, i) => {
                bar.className = 'h-1.5 flex-1 rounded-full transition-all duration-300 ' + (i < score ? colors[
                    score - 1] : 'bg-gray-200');
            });
            label.textContent = labels[score - 1] || '';
            label.className = 'text-[11px] ' + (tc[score - 1] || 'text-gray-400');
        }

        function checkMatch() {
            const np = document.getElementById('newPassword').value;
            const cp = document.getElementById('confirmPassword').value;
            const msg = document.getElementById('matchMsg');
            if (!cp) {
                msg.classList.add('hidden');
                return;
            }
            msg.classList.remove('hidden');
            if (np === cp) {
                msg.textContent = '✓ Kata sandi cocok';
                msg.className = 'text-[11px] px-1 text-green-500';
            } else {
                msg.textContent = '✗ Kata sandi tidak cocok';
                msg.className = 'text-[11px] px-1 text-red-400';
            }
        }
    </script>
@endpush
