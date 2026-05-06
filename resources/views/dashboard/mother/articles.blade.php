@extends('layouts.mother')

@section('title', 'BumilSehat - Edukasi Kehamilan')

@section('content')

    <header class="px-6 py-4 flex justify-between gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Edukasi Kehamilan</h1>
            <p class="text-xs text-gray-400">Informasi ringan untuk menemani Bunda</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-6 pb-28">

        {{-- Search --}}
        <form method="GET" action="{{ route('mother.articles') }}" class="mb-6">
            <div class="relative">
                <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari artikel kesehatan..."
                    class="w-full pl-11 pr-4 py-3 rounded-2xl bg-gray-50 border border-gray-200 text-sm focus:outline-none focus:ring-2 focus:ring-rose-200 focus:border-rose-300">
                <i class="fa-solid fa-magnifying-glass absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
            </div>
        </form>

        {{-- Filter minggu kehamilan --}}
        @if ($week)
            <div class="mb-5 flex items-center gap-2">
                <span class="text-xs text-gray-500">Menampilkan artikel untuk</span>
                <span class="text-xs font-bold text-rose-500 bg-rose-50 px-2.5 py-1 rounded-full">
                    Minggu ke-{{ $week }}
                </span>
                <a href="{{ route('mother.articles') }}"
                    class="text-xs text-gray-400 hover:text-rose-500 underline ml-auto">
                    Lihat semua
                </a>
            </div>
        @endif

        {{-- Artikel --}}
        @if ($articles->isEmpty())
            <div class="text-center py-16 space-y-3">
                <div class="mx-auto w-16 h-16 rounded-2xl bg-gray-100 flex items-center justify-center">
                    <i class="fa-regular fa-newspaper text-gray-400 text-2xl"></i>
                </div>
                <p class="text-sm font-semibold text-gray-700">Belum ada artikel</p>
                <p class="text-xs text-gray-500">Admin belum mempublish artikel.</p>
            </div>
        @else
            <section class="space-y-5">
                @foreach ($articles as $article)
                    <a href="{{ route('mother.articles.show', $article->slug) }}"
                        class="block bg-white rounded-3xl overflow-hidden shadow-lg hover:shadow-xl transition">

                        {{-- Thumbnail --}}
                        <div class="relative w-full h-44 bg-rose-50">
                            @if ($article->thumbnail)
                                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}"
                                    class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fa-regular fa-image text-rose-200 text-4xl"></i>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold text-rose-500 uppercase tracking-wide">
                                    {{ $article->week_range_label }}
                                </span>
                            </div>

                            <h3 class="text-base font-bold text-gray-800 leading-snug">
                                {{ $article->title }}
                            </h3>

                            <p class="text-sm text-gray-600 leading-relaxed line-clamp-2">
                                {{ $article->excerpt }}
                            </p>

                            <div class="flex items-center justify-between pt-2 border-t border-gray-100">
                                <span class="text-xs text-gray-400">
                                    {{ $article->published_at?->diffForHumans() }}
                                </span>
                                <span class="text-rose-500 text-sm font-semibold">
                                    Baca selengkapnya →
                                </span>
                            </div>
                        </div>
                    </a>
                @endforeach
            </section>

            {{-- Pagination --}}
            @if ($articles->hasPages())
                <div class="mt-6 flex justify-center gap-2">
                    @if ($articles->onFirstPage())
                        <span class="px-4 py-2 text-xs text-gray-300 bg-gray-50 rounded-xl">← Sebelumnya</span>
                    @else
                        <a href="{{ $articles->previousPageUrl() }}"
                            class="px-4 py-2 text-xs text-rose-500 bg-rose-50 rounded-xl hover:bg-rose-100">
                            ← Sebelumnya
                        </a>
                    @endif

                    @if ($articles->hasMorePages())
                        <a href="{{ $articles->nextPageUrl() }}"
                            class="px-4 py-2 text-xs text-rose-500 bg-rose-50 rounded-xl hover:bg-rose-100">
                            Selanjutnya →
                        </a>
                    @else
                        <span class="px-4 py-2 text-xs text-gray-300 bg-gray-50 rounded-xl">Selanjutnya →</span>
                    @endif
                </div>
            @endif
        @endif

    </main>

@endsection
