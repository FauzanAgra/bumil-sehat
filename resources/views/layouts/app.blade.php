<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'BumilSehat')</title>

    {{-- Vite — Tailwind CSS v4 + JS --}}
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    {{-- Font Awesome Kit --}}
    <script src="https://kit.fontawesome.com/5bd8635687.js" crossorigin="anonymous" defer></script>

    @stack('head')
</head>

<body class="@yield('body-class', 'bg-rose-50 text-gray-800')">

    @yield('content')

    @stack('scripts')

</body>

</html>
