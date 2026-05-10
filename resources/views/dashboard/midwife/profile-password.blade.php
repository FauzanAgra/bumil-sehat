@extends('layouts.midwife-plain')

@section('title', 'BumilSehat - Ubah Password')

@section('content')

    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur-sm z-40">
        <a href="{{ route('midwife.profile.setting') }}"
            class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-800">Ubah Password</h1>
            <p class="text-[11px] text-gray-400">Keamanan akun</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 pt-6 pb-10 space-y-6">

        @if (session('success'))
            <div
                class="bg-green-50 border border-green-200 text-green-700 text-xs rounded-2xl px-4 py-3 flex items-center gap-2">
                <i class="fa-solid fa-circle-check text-green-500"></i>
                {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                @foreach ($errors->all() as $error)
                    <p>• {{ $error }}</p>
                @endforeach
            </div>
        @endif

        {{-- Info --}}
        <div class="bg-rose-50 border border-rose-200 rounded-2xl p-4 flex gap-3 text-xs text-rose-700">
            <i class="fa-solid fa-shield-halved mt-0.5 shrink-0"></i>
            <p class="leading-relaxed">Password harus minimal 8 karakter, mengandung huruf besar, huruf kecil, dan angka.
            </p>
        </div>

        <form method="POST" action="{{ route('midwife.profile.password.update') }}" class="space-y-5">
            @csrf

            {{-- Password Lama --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Password Saat Ini</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-lock text-rose-400 text-xs"></i>
                    </div>
                    <input type="password" name="current_password" id="current_password" required
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border
                               {{ $errors->has('current_password') ? 'border-red-300 bg-red-50' : 'border-gray-200' }}
                               text-sm font-semibold text-gray-800
                               focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none transition"
                        placeholder="Masukkan password saat ini">
                    <button type="button" onclick="togglePassword('current_password', 'eye-current')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-current" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
                @error('current_password')
                    <p class="text-xs text-red-500 px-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Baru --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Password Baru</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-key text-rose-400 text-xs"></i>
                    </div>
                    <input type="password" name="password" id="new_password" required
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800
                               focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none transition"
                        placeholder="Minimal 8 karakter">
                    <button type="button" onclick="togglePassword('new_password', 'eye-new')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-new" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>

                {{-- Strength indicator --}}
                <div class="flex gap-1.5 px-1 mt-2" id="strengthBars">
                    <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar1"></div>
                    <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar2"></div>
                    <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar3"></div>
                    <div class="h-1 flex-1 rounded-full bg-gray-200" id="bar4"></div>
                </div>
                <p id="strengthLabel" class="text-[10px] text-gray-400 px-1"></p>
            </div>

            {{-- Konfirmasi Password --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Konfirmasi Password Baru</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-key text-rose-400 text-xs"></i>
                    </div>
                    <input type="password" name="password_confirmation" id="confirm_password" required
                        class="w-full pl-14 pr-12 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800
                               focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none transition"
                        placeholder="Ulangi password baru">
                    <button type="button" onclick="togglePassword('confirm_password', 'eye-confirm')"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <i id="eye-confirm" class="fa-solid fa-eye text-sm"></i>
                    </button>
                </div>
                <p id="matchLabel" class="text-[10px] px-1 hidden"></p>
            </div>

            <div class="pt-2">
                <button type="submit" id="submitBtn"
                    class="w-full bg-rose-500 hover:bg-rose-600 active:scale-[0.98] text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-500/25 transition-all">
                    Ubah Password
                </button>
            </div>

        </form>

    </main>

@endsection

@push('scripts')
    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength
        document.getElementById('new_password').addEventListener('input', function() {
            const val = this.value;
            let score = 0;
            if (val.length >= 8) score++;
            if (/[A-Z]/.test(val)) score++;
            if (/[0-9]/.test(val)) score++;
            if (/[^A-Za-z0-9]/.test(val)) score++;

            const colors = ['bg-red-400', 'bg-orange-400', 'bg-yellow-400', 'bg-green-500'];
            const labels = ['', 'Lemah', 'Sedang', 'Kuat', 'Sangat Kuat'];
            const labelColors = ['', 'text-red-400', 'text-orange-400', 'text-yellow-500', 'text-green-600'];

            for (let i = 1; i <= 4; i++) {
                const bar = document.getElementById('bar' + i);
                bar.className = 'h-1 flex-1 rounded-full ' + (i <= score ? colors[score - 1] : 'bg-gray-200');
            }

            const label = document.getElementById('strengthLabel');
            label.textContent = labels[score];
            label.className = 'text-[10px] px-1 ' + (labelColors[score] || 'text-gray-400');

            checkMatch();
        });

        // Match check
        document.getElementById('confirm_password').addEventListener('input', checkMatch);

        function checkMatch() {
            const pw = document.getElementById('new_password').value;
            const cf = document.getElementById('confirm_password').value;
            const label = document.getElementById('matchLabel');
            if (!cf) {
                label.classList.add('hidden');
                return;
            }
            label.classList.remove('hidden');
            if (pw === cf) {
                label.textContent = '✓ Password cocok';
                label.className = 'text-[10px] px-1 text-green-600';
            } else {
                label.textContent = '✗ Password tidak cocok';
                label.className = 'text-[10px] px-1 text-red-500';
            }
        }
    </script>
@endpush
