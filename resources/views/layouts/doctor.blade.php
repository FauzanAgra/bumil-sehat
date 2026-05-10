<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BumilSehat - Dokter')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://kit.fontawesome.com/5bd8635687.js" crossorigin="anonymous" defer></script>
    @stack('head')
</head>

<body class="bg-blue-50 text-gray-800">
    <div class="min-h-screen max-w-md mx-auto bg-white flex flex-col">
        @yield('content')

        <nav
            class="fixed bottom-0 left-0 right-0 bg-white/90 backdrop-blur-md border-t border-gray-100 px-8 py-4 flex justify-between z-50">
            <a href="{{ route('doctor.dashboard') }}"
                class="{{ request()->routeIs('doctor.dashboard') ? 'text-blue-500' : 'text-gray-300 hover:text-blue-400 transition-colors' }}">
                <i class="fa-solid fa-house-chimney text-xl"></i>
            </a>
            <a href="{{ route('doctor.patients') }}"
                class="{{ request()->routeIs('doctor.patients*') ? 'text-blue-500' : 'text-gray-300 hover:text-blue-400 transition-colors' }}">
                <i class="fa-solid fa-people-group text-xl"></i>
            </a>
            <a href="{{ route('doctor.profile.setting') }}"
                class="{{ request()->routeIs('doctor.profile*') ? 'text-blue-500' : 'text-gray-300 hover:text-blue-400 transition-colors' }}">
                <i class="fa-solid fa-gear text-xl"></i>
            </a>
        </nav>
    </div>
    @stack('scripts')
</body>

</html>
