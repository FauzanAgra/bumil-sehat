@extends('layouts.mother')

@section('title', 'BumilSehat - ' . $article->title)

@section('content')

    <header class="px-6 py-4 flex justify-between gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.articles') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Edukasi Kehamilan</h1>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto pb-28">

        {{-- Hero Image --}}
        <div class="relative">
            @if ($article->thumbnail)
                <img src="{{ asset('storage/' . $article->thumbnail) }}" alt="{{ $article->title }}"
                    class="w-full h-64 object-cover">
            @else
                <div class="w-full h-48 bg-rose-50 flex items-center justify-center">
                    <i class="fa-regular fa-image text-rose-200 text-5xl"></i>
                </div>
            @endif
            <div class="absolute inset-0 bg-linear-to-t from-black/60 via-black/20 to-transparent"></div>
            <div class="absolute top-4 left-6">
                <span class="bg-white/95 backdrop-blur-sm text-rose-600 text-xs font-bold px-3 py-1.5 rounded-full">
                    {{ $article->week_range_label }}
                </span>
            </div>
        </div>

        {{-- Judul --}}
        <section class="px-6 pt-5 space-y-3">
            <h2 class="text-xl font-bold text-gray-800 leading-tight">
                {{ $article->title }}
            </h2>
            <div class="flex items-center gap-4 text-xs text-gray-500">
                <div class="flex items-center gap-1.5">
                    <i class="fa-regular fa-calendar"></i>
                    <span>{{ $article->published_at?->translatedFormat('d F Y') }}</span>
                </div>
                <div class="flex items-center gap-1.5">
                    <i class="fa-solid fa-user-nurse text-rose-400"></i>
                    <span>{{ $article->createdBy?->name }}</span>
                </div>
            </div>
        </section>

        {{-- Konten --}}
        <section
            class="px-6 pt-5 pb-5 text-sm text-gray-700 leading-relaxed prose prose-sm max-w-none
        prose-headings:text-gray-800 prose-headings:font-bold
        prose-p:text-gray-700 prose-p:leading-relaxed
        prose-li:text-gray-700 prose-ul:marker:text-rose-400">
            {!! $article->sanitized_content !!}
        </section>

        {{-- Info box --}}
        <div class="px-6 pb-5">
            <div class="bg-linear-to-r from-blue-50 to-blue-100 border-l-4 border-blue-400 rounded-2xl p-5 space-y-2">
                <div class="flex items-center gap-2 text-blue-700 font-bold text-sm">
                    <i class="fa-solid fa-circle-info"></i>
                    <span>Perlu Diingat</span>
                </div>
                <p class="text-xs text-blue-700 leading-relaxed">
                    Informasi di artikel ini bersifat umum. Untuk kondisi khusus,
                    Bidan akan memberikan penjelasan sesuai kondisi Bunda. Jangan
                    ragu untuk berkonsultasi jika ada yang mengganggu.
                </p>
            </div>
        </div>

        {{-- Artikel Terkait --}}
        @if ($related->isNotEmpty())
            <section class="px-6 space-y-4 pb-5">
                <h3 class="text-base font-bold text-gray-800">Artikel Terkait</h3>
                <div class="space-y-3">
                    @foreach ($related as $rel)
                        <a href="{{ route('mother.articles.show', $rel->slug) }}"
                            class="flex gap-3 p-3 bg-gray-50 rounded-xl hover:bg-rose-50 transition">
                            <div class="w-20 h-20 rounded-lg overflow-hidden shrink-0 bg-rose-100">
                                @if ($rel->thumbnail)
                                    <img src="{{ asset('storage/' . $rel->thumbnail) }}" alt="{{ $rel->title }}"
                                        class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center">
                                        <i class="fa-regular fa-image text-rose-300"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="flex-1 space-y-1">
                                <h4 class="text-sm font-bold text-gray-800 line-clamp-2 leading-snug">
                                    {{ $rel->title }}
                                </h4>
                                <p class="text-xs text-gray-500">{{ $rel->week_range_label }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

    </main>

@endsection
