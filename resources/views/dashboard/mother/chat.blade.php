@extends('layouts.mother')

@section('title', 'BumilSehat - Konsultasi')

@section('content')

    <header class="px-6 py-4 flex justify-between gap-3 border-b border-gray-100 sticky top-0 bg-white z-40">
        <a href="{{ route('mother.dashboard') }}"
            class="w-10 h-10 rounded-full flex items-center justify-center text-gray-400 hover:bg-rose-50 transition">
            <i class="fa-solid fa-arrow-left"></i>
        </a>
        <div class="text-right">
            <h1 class="text-base font-bold text-gray-700">Konsultasi</h1>
            <p class="text-xs text-gray-400">Hubungi tenaga kesehatan pendamping</p>
        </div>
    </header>

    <main class="flex-1 overflow-y-auto px-6 py-5 pb-28 space-y-6">

        {{-- Banner info --}}
        <div class="bg-linear-to-r from-rose-50 to-pink-50 rounded-2xl p-4 border border-rose-100">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-rose-100 flex items-center justify-center shrink-0 mt-0.5">
                    <i class="fa-brands fa-whatsapp text-rose-500 text-sm"></i>
                </div>
                <p class="text-xs text-gray-700 leading-relaxed">
                    <span class="font-semibold">Klik kartu</span> untuk memulai konsultasi via WhatsApp.
                    Siapkan pertanyaan Anda dengan jelas untuk mendapat bantuan lebih cepat.
                </p>
            </div>
        </div>

        {{-- Bidan Pendamping --}}
        @if ($midwife)
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <div class="h-px flex-1 bg-linear-to-r from-transparent to-rose-200"></div>
                    <h3 class="text-xs font-bold text-rose-500 uppercase tracking-wide">Bidan Pendamping Anda</h3>
                    <div class="h-px flex-1 bg-linear-to-l from-transparent to-rose-200"></div>
                </div>

                @php
                    $midwifePhone = $midwife->medicalProfile?->phone ?? $midwife->phone;
                    $midwifeWa =
                        $midwife->medicalProfile?->specialization?->wa_greeting ??
                        'Halo Bu Bidan, saya ingin konsultasi.';
                    $waNumber = '62' . ltrim($midwifePhone, '0');
                @endphp

                <a href="https://wa.me/{{ $waNumber }}?text={{ urlencode($midwifeWa) }}" target="_blank"
                    class="bg-linear-to-br from-rose-50 to-pink-50 rounded-3xl p-5 shadow-md border-2 border-rose-200 flex items-center gap-4 hover:shadow-xl hover:scale-[1.02] transition-all active:scale-[0.98] relative overflow-hidden block">
                    <div
                        class="absolute top-0 right-0 w-32 h-32 bg-rose-100 rounded-full blur-3xl opacity-50 -mr-16 -mt-16">
                    </div>
                    <div
                        class="w-16 h-16 rounded-2xl bg-white shadow-sm flex items-center justify-center text-rose-500 text-2xl relative z-10 shrink-0">
                        <i class="fa-solid fa-user-nurse"></i>
                    </div>
                    <div class="flex-1 relative z-10">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <h4 class="text-sm font-bold text-gray-800">{{ $midwife->name }}</h4>
                            <span
                                class="px-2 py-1 bg-rose-500 text-white text-[10px] font-bold rounded-full whitespace-nowrap shrink-0">
                                BIDAN ANDA
                            </span>
                        </div>
                        <p class="text-xs text-gray-600 mb-1">Penanggung jawab kehamilan Bunda</p>
                        @if ($midwife->medicalProfile?->availability)
                            <div class="flex items-center gap-1 text-[11px] text-gray-500">
                                <i class="fa-regular fa-clock text-rose-400"></i>
                                <span>{{ $midwife->medicalProfile->availability }}</span>
                            </div>
                        @endif
                    </div>
                    <div class="absolute bottom-4 right-4 text-rose-300 text-2xl opacity-50">
                        <i class="fa-brands fa-whatsapp"></i>
                    </div>
                </a>
            </section>
        @endif

        {{-- Tenaga Kesehatan Lain --}}
        @php
            $otherStaff = $staff->filter(fn($s) => $s->id !== $midwife?->id);
        @endphp

        @if ($otherStaff->isNotEmpty())
            <section>
                <div class="flex items-center gap-2 mb-3">
                    <div class="h-px flex-1 bg-linear-to-r from-transparent to-gray-200"></div>
                    <h3 class="text-xs font-bold text-gray-500 uppercase tracking-wide">Tenaga Kesehatan Tersedia</h3>
                    <div class="h-px flex-1 bg-linear-to-l from-transparent to-gray-200"></div>
                </div>

                <div class="space-y-3">
                    @foreach ($otherStaff as $s)
                        @php
                            $spec = $s->medicalProfile?->specialization;
                            $color = $spec?->color ?? 'blue';
                            $icon = $spec?->icon ?? 'fa-user-doctor';
                            $greeting = $spec?->wa_greeting ?? 'Halo, saya ingin berkonsultasi.';
                            $phone = $s->medicalProfile?->phone ?? $s->phone;
                            $waNum = '62' . ltrim($phone, '0');

                            $colorMap = [
                                'rose' => ['bg' => 'bg-rose-50', 'text' => 'text-rose-500'],
                                'blue' => ['bg' => 'bg-blue-50', 'text' => 'text-blue-500'],
                                'green' => ['bg' => 'bg-green-50', 'text' => 'text-green-500'],
                                'purple' => ['bg' => 'bg-purple-50', 'text' => 'text-purple-500'],
                                'teal' => ['bg' => 'bg-teal-50', 'text' => 'text-teal-500'],
                            ];
                            $c = $colorMap[$color] ?? $colorMap['blue'];
                        @endphp

                        <a href="https://wa.me/{{ $waNum }}?text={{ urlencode($greeting) }}" target="_blank"
                            class="bg-white rounded-3xl p-4 shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md transition-all active:scale-[0.98] block">
                            <div
                                class="w-14 h-14 rounded-2xl {{ $c['bg'] }} flex items-center justify-center {{ $c['text'] }} text-xl shrink-0">
                                <i class="fa-solid {{ $icon }}"></i>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-gray-800">{{ $s->medicalProfile->full_name }}</h4>
                                <p class="text-xs {{ $c['text'] }} font-medium">
                                    {{ $spec?->name ?? 'Tenaga Kesehatan' }}</p>
                                @if ($s->medicalProfile?->availability)
                                    <div class="flex items-center gap-1 text-[11px] text-gray-500 mt-1">
                                        <i class="fa-regular fa-clock {{ $c['text'] }}"></i>
                                        <span>{{ $s->medicalProfile->availability }}</span>
                                    </div>
                                @endif
                            </div>
                            <div class="text-gray-300 text-xl shrink-0">
                                <i class="fa-brands fa-whatsapp"></i>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Emergency --}}
        <div class="bg-red-50 border border-red-100 rounded-2xl p-4">
            <div class="flex items-start gap-3">
                <div class="w-8 h-8 rounded-full bg-red-100 flex items-center justify-center shrink-0">
                    <i class="fa-solid fa-triangle-exclamation text-red-500 text-sm"></i>
                </div>
                <div class="flex-1">
                    <h4 class="text-xs font-bold text-red-700 mb-1">Kondisi Darurat?</h4>
                    <p class="text-xs text-red-600 leading-relaxed mb-2">
                        Jika mengalami pendarahan hebat, kontraksi sangat kuat, atau kondisi darurat lainnya, segera
                        hubungi:
                    </p>
                    <a href="tel:119" class="inline-flex items-center gap-2 text-xs font-bold text-red-600">
                        <i class="fa-solid fa-phone"></i>
                        <span>119 (Ambulans)</span>
                    </a>
                </div>
            </div>
        </div>

    </main>

@endsection
