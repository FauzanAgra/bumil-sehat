@extends('layouts.app')

@section('title', 'BumilSehat - Registrasi Ibu Hamil')
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
                    <i class="fa-solid fa-person-pregnant text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wide">Registrasi Ibu Hamil</h1>
                <p class="text-xs opacity-80 mt-1">Langkah awal pendampingan kehamilan</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-6">

            <div class="flex items-center justify-end">
                <div class="text-right">
                    <p class="text-xs text-gray-400 font-medium">
                        Langkah <span class="text-rose-500 font-semibold">1</span> dari 3
                    </p>
                    <div class="flex justify-end gap-1.5 mt-1">
                        <div class="w-2.5 h-2.5 rounded-full bg-rose-500"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div>
                        <div class="w-2.5 h-2.5 rounded-full bg-gray-200"></div>
                    </div>
                </div>
            </div>

            <div class="text-center">
                <h2 class="text-lg font-bold">Selamat datang, Bunda 🤍</h2>
                <p class="text-xs text-gray-500 mt-1 leading-relaxed">
                    Silakan buat akun untuk memulai pemantauan kehamilan bersama Bidan Pendamping.
                </p>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.mother.store') }}" class="space-y-4">
                @csrf

                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Nama Lengkap</label>
                    <div class="relative">
                        <i class="fa-solid fa-user absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="text" name="name" value="{{ old('name') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none"
                            placeholder="Nama sesuai KTP">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Nomor HP</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none"
                            placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input id="password" type="password" name="password" required minlength="6"
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePass('password','eye1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500">
                            <i id="eye1" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none"
                            placeholder="Ulangi kata sandi">
                        <button type="button" onclick="togglePass('password_confirmation','eye2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500">
                            <i id="eye2" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                    Daftar & Lanjutkan
                </button>
            </form>

            <p class="text-[11px] text-center text-gray-400 leading-relaxed px-4">
                Setelah mendaftar, Bunda akan diminta mengisi data identitas dan tanggal HPHT.
            </p>

            <div class="text-center">
                <p class="text-sm text-gray-500">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="text-rose-500 font-bold hover:underline mt-1 inline-block">Masuk di
                    sini</a>
            </div>
        </div>

        <div class="h-2 bg-rose-500"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePass(inputId, iconId) {
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
    </script>
@endpush
