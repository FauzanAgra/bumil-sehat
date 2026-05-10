@extends('layouts.midwife-plain')

@section('title', 'BumilSehat - Edit Profil')

@section('content')

    <header
        class="px-6 py-4 flex justify-between items-center gap-3 border-b border-gray-100 sticky top-0 bg-white/95 backdrop-blur-sm z-40">
        <a href="{{ route('midwife.profile.setting') }}"
            class="w-10 h-10 flex items-center justify-center rounded-full text-gray-400 hover:text-rose-500 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-800">Edit Profil</h1>
            <p class="text-[11px] text-gray-400">Data diri</p>
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

        {{-- Avatar --}}
        <div class="flex justify-center">
            <div class="relative">
                <div class="w-20 h-20 rounded-full border-4 border-rose-100 p-0.5">
                    <img src="https://api.dicebear.com/9.x/notionists/svg?seed={{ $user->name }}"
                        class="rounded-full w-full h-full" alt="avatar">
                </div>
                <div
                    class="absolute -bottom-1 -right-1 w-7 h-7 bg-rose-500 rounded-full flex items-center justify-center border-2 border-white">
                    <i class="fa-solid fa-pen text-white text-[9px]"></i>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('midwife.profile.update') }}" class="space-y-5">
            @csrf

            {{-- Nama --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Nama Lengkap</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-user text-rose-400 text-xs"></i>
                    </div>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required
                        class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800
                               focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none transition"
                        placeholder="Masukkan nama lengkap">
                </div>
            </div>

            {{-- Email --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Email <span
                        class="font-normal text-gray-400">(opsional)</span></label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-rose-50 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-envelope text-rose-400 text-xs"></i>
                    </div>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}"
                        class="w-full pl-14 pr-4 py-4 rounded-2xl bg-gray-50 border border-gray-200 text-sm font-semibold text-gray-800
                               focus:bg-white focus:border-rose-400 focus:ring-2 focus:ring-rose-100 outline-none transition"
                        placeholder="email@contoh.com">
                </div>
            </div>

            {{-- No HP — read only --}}
            <div class="space-y-1.5">
                <label class="text-xs font-bold text-gray-500 px-1">Nomor HP</label>
                <div class="relative">
                    <div
                        class="absolute left-4 top-1/2 -translate-y-1/2 w-8 h-8 bg-gray-100 rounded-xl flex items-center justify-center">
                        <i class="fa-solid fa-phone text-gray-400 text-xs"></i>
                    </div>
                    <input type="text" value="{{ $user->phone }}" disabled
                        class="w-full pl-14 pr-10 py-4 rounded-2xl bg-gray-100 border border-gray-200 text-sm font-semibold text-gray-400 cursor-not-allowed">
                    <div class="absolute right-4 top-1/2 -translate-y-1/2">
                        <i class="fa-solid fa-lock text-gray-300 text-xs"></i>
                    </div>
                </div>
                <p class="text-[10px] text-gray-400 px-1">Nomor HP hanya dapat diubah melalui Admin.</p>
            </div>

            <div class="pt-2">
                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 active:scale-[0.98] text-white font-bold py-4 rounded-2xl shadow-lg shadow-rose-500/25 transition-all">
                    Simpan Perubahan
                </button>
            </div>
        </form>

    </main>

@endsection
