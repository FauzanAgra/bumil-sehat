@extends('layouts.app')

@section('title', 'BumilSehat - Daftar Sebagai')

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
                <p class="text-xs opacity-80 mt-1">Daftar Akun Baru</p>
            </div>
        </div>

        <!-- Content -->
        <div class="flex-1 overflow-y-auto p-6">

            <div class="mb-8">
                <h2 class="text-2xl font-bold">Daftar Sebagai</h2>
                <p class="text-sm text-gray-500 mt-1">
                    Pilih peran kamu untuk melanjutkan pendaftaran.
                </p>
            </div>

            <div class="space-y-4">

                <!-- Ibu Hamil -->
                <a href="{{ route('register.mother') }}"
                    class="flex items-center gap-4 p-5 rounded-3xl border-2 border-rose-100 bg-rose-50/50 hover:border-rose-400 hover:bg-rose-50 transition-all active:scale-95 group">
                    <div
                        class="w-14 h-14 bg-rose-500 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-rose-200 shrink-0">
                        <i class="fa-solid fa-person-pregnant"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800">Ibu Hamil</h3>
                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                            Pantau kehamilan, jadwal ANC, dan konsumsi tablet tambah darah.
                        </p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-rose-300 group-hover:text-rose-500 transition"></i>
                </a>

                <!-- Tenaga Medis -->
                <a href="{{ route('register.medical') }}"
                    class="flex items-center gap-4 p-5 rounded-3xl border-2 border-blue-100 bg-blue-50/50 hover:border-blue-400 hover:bg-blue-50 transition-all active:scale-95 group">
                    <div
                        class="w-14 h-14 bg-blue-500 rounded-2xl flex items-center justify-center text-white text-2xl shadow-lg shadow-blue-200 shrink-0">
                        <i class="fa-solid fa-user-nurse"></i>
                    </div>
                    <div class="flex-1">
                        <h3 class="font-bold text-gray-800">Tenaga Medis</h3>
                        <p class="text-xs text-gray-500 mt-0.5 leading-relaxed">
                            Bidan, Dokter, Nutrisionis, atau tenaga kesehatan lainnya.
                        </p>
                    </div>
                    <i class="fa-solid fa-chevron-right text-blue-300 group-hover:text-blue-500 transition"></i>
                </a>

            </div>

            <!-- Back to login -->
            <div class="mt-10 text-center">
                <p class="text-sm text-gray-500">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="text-rose-500 font-bold hover:underline mt-1 inline-block">
                    Masuk di sini
                </a>
            </div>
        </div>

        <div class="h-2 bg-rose-500"></div>
    </div>
@endsection
