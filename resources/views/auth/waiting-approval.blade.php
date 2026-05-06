@extends('layouts.app')

@section('title', 'BumilSehat - Menunggu Verifikasi')
@section('body-class', 'bg-rose-50 min-h-screen flex items-center justify-center text-gray-800')

@section('content')
    <div
        class="w-full max-w-md bg-white sm:rounded-4xl sm:shadow-2xl h-screen sm:h-[90vh] overflow-hidden flex flex-col fade-in">

        <div
            class="h-32 bg-linear-to-br from-blue-500 to-blue-600 rounded-b-3xl flex items-center justify-center text-white relative">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
            <div class="relative z-10 text-center">
                <div
                    class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                    <i class="fa-solid fa-clock text-2xl"></i>
                </div>
                <h1 class="text-xl font-bold tracking-wide">Menunggu Verifikasi</h1>
                <p class="text-xs opacity-80 mt-1">Akun Anda sedang diproses</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 flex flex-col justify-center space-y-6 text-center">

            <div class="mx-auto w-24 h-24 rounded-full bg-blue-50 flex items-center justify-center">
                <i class="fa-solid fa-user-check text-blue-500 text-4xl"></i>
            </div>

            <div class="space-y-3 px-4">
                <h2 class="text-lg font-bold text-gray-800">Akun Anda Sedang Diverifikasi</h2>
                <p class="text-sm text-gray-500 leading-relaxed">
                    Terima kasih telah mendaftar sebagai <b>tenaga medis</b> di BumilSehat.
                    Saat ini data Anda sedang ditinjau oleh Admin Puskesmas.
                </p>
            </div>

            <div class="bg-blue-50 border border-blue-200 rounded-3xl p-4 text-xs text-blue-700 mx-4">
                <div class="flex gap-3 text-left">
                    <i class="fa-solid fa-circle-info mt-0.5"></i>
                    <p class="leading-relaxed">
                        Proses verifikasi dilakukan untuk memastikan keabsahan identitas dan kewenangan
                        tenaga medis demi keamanan data ibu hamil.
                    </p>
                </div>
            </div>

            <p class="text-[11px] text-gray-400 leading-relaxed px-6">
                Setelah akun disetujui, Anda dapat masuk dan mulai menggunakan fitur BumilSehat.
            </p>

            <div class="pt-4 space-y-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-blue-600 font-semibold text-sm hover:underline">
                        Keluar & Kembali ke Login
                    </button>
                </form>
            </div>
        </div>

        <div class="h-2 bg-blue-500"></div>
    </div>
@endsection
