@extends('layouts.app')

@section('title', 'BumilSehat - Registrasi Tenaga Medis')
@section('body-class', 'bg-rose-50 min-h-screen flex items-center justify-center text-gray-800')

@section('content')
    <div
        class="w-full max-w-md bg-white sm:rounded-4xl sm:shadow-2xl h-screen sm:h-auto overflow-hidden flex flex-col fade-in">

        <div id="header"
            class="h-32 bg-rose-500 rounded-b-3xl flex items-center justify-center text-white relative shrink-0">
            <div class="absolute inset-0 opacity-10"
                style="background-image: radial-gradient(#fff 1px, transparent 1px); background-size: 10px 10px;"></div>
            <div class="relative z-10 text-center">
                <div
                    class="w-12 h-12 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center mx-auto mb-2 border border-white/30">
                    <i id="role-icon" class="fa-solid fa-user-nurse text-2xl"></i>
                </div>
                <h1 id="role-title" class="text-xl font-bold tracking-wide">Registrasi Tenaga Medis</h1>
                <p class="text-xs opacity-80 mt-1">Akses profesional BumilSehat</p>
            </div>
        </div>

        <div class="flex-1 overflow-y-auto p-6 space-y-5">

            {{-- Info verifikasi --}}
            <div class="bg-yellow-50 border border-yellow-200 rounded-3xl p-4 text-xs text-yellow-700">
                <div class="flex gap-3">
                    <i class="fa-solid fa-circle-info mt-0.5 shrink-0"></i>
                    <p class="leading-relaxed">
                        Akun tenaga medis <b>harus diverifikasi oleh Admin</b> sebelum dapat digunakan.
                        Pastikan data yang diisi benar dan sesuai identitas profesional.
                    </p>
                </div>
            </div>

            @if ($errors->any())
                <div class="bg-red-50 border border-red-200 text-red-600 text-xs rounded-2xl px-4 py-3 space-y-1">
                    @foreach ($errors->all() as $error)
                        <p>• {{ $error }}</p>
                    @endforeach
                </div>
            @endif

            <form method="POST" action="{{ route('register.medical.store') }}" class="space-y-4">
                @csrf

                {{-- Spesialisasi --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                        Profesi / Spesialisasi
                    </label>
                    <select name="specialization_id" id="specializationSelect" required
                        onchange="handleSpecializationChange()"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none bg-white text-sm">
                        <option value="" disabled selected>Pilih profesi</option>
                        @foreach ($specializations as $spec)
                            @if ($spec->role_group === 'midwife')
                                <optgroup label="Bidan">
                                @elseif ($loop->first || $specializations[$loop->index - 1]->role_group !== 'doctor')
                                <optgroup label="Dokter & Tenaga Kesehatan Lain">
                            @endif
                            <option value="{{ $spec->id }}" data-group="{{ $spec->role_group }}"
                                data-icon="{{ $spec->icon ?? 'fa-user-doctor' }}" data-color="{{ $spec->color ?? 'blue' }}"
                                {{ old('specialization_id') == $spec->id ? 'selected' : '' }}>
                                {{ $spec->name }}
                            </option>
                        @endforeach
                    </select>
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">
                        Profesi yang tersedia ditentukan oleh Admin Puskesmas
                    </p>
                </div>

                {{-- Nama --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name') }}" required
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                        placeholder="Nama sesuai STR / SIP">
                </div>

                {{-- Gelar --}}
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                            Gelar Depan <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                        </label>
                        <input type="text" name="degree_prefix" value="{{ old('degree_prefix') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="dr. / Ns.">
                    </div>
                    <div>
                        <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                            Gelar Belakang <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                        </label>
                        <input type="text" name="degree_suffix" value="{{ old('degree_suffix') }}"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="Sp.OG / A.Md.Keb">
                    </div>
                </div>

                {{-- Nomor STR --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Nomor STR / SIP</label>
                    <div class="relative">
                        <i class="fa-solid fa-id-badge absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="text" name="license_number" value="{{ old('license_number') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="Nomor STR atau SIP aktif">
                    </div>
                    <p class="text-[11px] text-gray-400 mt-1 ml-1">
                        Digunakan Admin untuk memverifikasi keabsahan identitas profesional
                    </p>
                </div>

                {{-- Nomor HP --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Nomor HP</label>
                    <div class="relative">
                        <i class="fa-solid fa-phone absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input type="tel" name="phone" value="{{ old('phone') }}" required
                            class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="08xxxxxxxxxx">
                    </div>
                </div>

                {{-- Jam Tersedia --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">
                        Jam Tersedia <span class="text-gray-400 font-normal text-xs">(opsional)</span>
                    </label>
                    <input type="text" name="availability" value="{{ old('availability') }}"
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                        placeholder="contoh: Senin - Jumat, 08:00 - 17:00">
                </div>

                {{-- Password --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input id="password" type="password" name="password" required minlength="6"
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="Minimal 6 karakter">
                        <button type="button" onclick="togglePass('password','eye1')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500">
                            <i id="eye1" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                {{-- Konfirmasi Password --}}
                <div>
                    <label class="text-sm font-semibold text-gray-600 ml-1 mb-1 block">Konfirmasi Kata Sandi</label>
                    <div class="relative">
                        <i class="fa-solid fa-lock absolute left-3 top-1/2 -translate-y-1/2 text-rose-400"></i>
                        <input id="password_confirmation" type="password" name="password_confirmation" required
                            class="w-full pl-10 pr-10 py-3 rounded-xl border border-gray-200 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 outline-none text-sm"
                            placeholder="Ulangi kata sandi">
                        <button type="button" onclick="togglePass('password_confirmation','eye2')"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-rose-500">
                            <i id="eye2" class="fa-solid fa-eye"></i>
                        </button>
                    </div>
                </div>

                <button type="submit"
                    class="w-full bg-rose-500 hover:bg-rose-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95">
                    Daftar & Kirim Verifikasi
                </button>
            </form>

            <p class="text-[11px] text-center text-gray-400 leading-relaxed px-4">
                Dengan mendaftar, Anda menyetujui bahwa data akan diverifikasi oleh Admin BumilSehat sebelum akun
                diaktifkan.
            </p>

            <div class="text-center pb-4">
                <p class="text-sm text-gray-500">Sudah punya akun?</p>
                <a href="{{ route('login') }}" class="text-rose-500 font-bold hover:underline mt-1 inline-block">
                    Masuk di sini
                </a>
            </div>
        </div>

        <div class="h-2 bg-rose-500 shrink-0" id="footer-accent"></div>
    </div>
@endsection

@push('scripts')
    <script>
        {{-- Mapping warna header per role_group --}}
        const headerColors = {
            midwife: 'bg-linear-to-br from-rose-500 to-rose-600',
            doctor: 'bg-linear-to-br from-blue-500 to-blue-600',
        };
        const footerColors = {
            midwife: 'bg-rose-500',
            doctor: 'bg-blue-500',
        };

        function handleSpecializationChange() {
            const select = document.getElementById('specializationSelect');
            const option = select.selectedOptions[0];
            const group = option?.dataset.group;
            const icon = option?.dataset.icon ?? 'fa-user-doctor';
            const header = document.getElementById('header');
            const roleIcon = document.getElementById('role-icon');
            const roleTitle = document.getElementById('role-title');
            const footer = document.getElementById('footer-accent');

            if (!group) return;

            // Update header color
            header.className =
                `h-32 ${headerColors[group] ?? headerColors.doctor} rounded-b-3xl flex items-center justify-center text-white relative shrink-0`;
            footer.className = `h-2 ${footerColors[group] ?? footerColors.doctor} shrink-0`;

            // Update icon & title
            roleIcon.className = `fa-solid ${icon} text-2xl`;
            roleTitle.textContent = group === 'midwife' ?
                'Registrasi Bidan' :
                'Registrasi Dokter / Tenaga Kesehatan';
        }

        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            input.type === 'password' ?
                (input.type = 'text', icon.classList.replace('fa-eye', 'fa-eye-slash')) :
                (input.type = 'password', icon.classList.replace('fa-eye-slash', 'fa-eye'));
        }

        // Trigger jika ada old value setelah validation error
        @if (old('specialization_id'))
            document.addEventListener('DOMContentLoaded', () => {
                handleSpecializationChange();
            });
        @endif
    </script>
@endpush
