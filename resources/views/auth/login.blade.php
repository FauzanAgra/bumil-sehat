@extends('layouts.app')

@section('title', 'BumilSehat - Login')

@section('body-class', 'bg-rose-50 min-h-screen flex items-center justify-center text-gray-800')

@section('content')
    <div
        class="w-full max-w-md bg-white sm:rounded-4xl sm:shadow-2xl h-screen sm:h-[90vh] overflow-hidden flex flex-col fade-in">

        <!-- Header -->
        <div class="h-32 bg-rose-500 rounded-b-3xl flex items-center justify-center text-white relative">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;">
            </div>
            <div class="relative z-10 text-center">
                <div
                    class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                    <i class="fa-solid fa-baby text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wide">BumilSehat</h1>
                <p class="text-xs opacity-80 mt-1">Masuk ke akun Bunda</p>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">

            <div class="mb-6">
                <h2 class="text-2xl font-bold">Selamat Datang 👋</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Silakan masuk untuk melanjutkan pendampingan kehamilan.
                </p>
            </div>

            {{-- Error message --}}
            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Session error --}}
            @if (session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 text-sm rounded-xl px-4 py-3 mb-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}" class="space-y-4">
                @csrf

                <!-- Phone -->
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                        Nomor HP
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all"
                            placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                <!-- Password -->
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                        Kata Sandi
                    </label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input id="password" type="password" name="password" required
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-rose-100 bg-rose-50/30 focus:bg-white focus:border-rose-500 focus:ring-4 focus:ring-rose-500/10 outline-none transition-all"
                            placeholder="••••••••">
                        <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500">
                            <i id="eye" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95 mt-4">
                    Masuk
                </button>
            </form>

            <!-- Register link -->
            <div class="mt-8 text-center">
                <p class="text-sm text-gray-500">Belum punya akun?</p>
                <a href="{{ route('register') }}" class="text-rose-500 font-bold hover:underline mt-1 inline-block">
                    Daftar Akun Baru
                </a>

            </div>
        </div>

        <div class="h-2 bg-rose-500"></div>
    </div>
@endsection

@push('scripts')
    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('eye');
            if (input.type === "password") {
                input.type = "text";
                icon.classList.replace("fa-eye", "fa-eye-slash");
            } else {
                input.type = "password";
                icon.classList.replace("fa-eye-slash", "fa-eye");
            }
        }
    </script>
@endpush
