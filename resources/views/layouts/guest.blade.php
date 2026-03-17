<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', config('app.name', 'OOHAPP'))</title>
    <meta name="description" content="@yield('meta_description', 'Sign in or register on OOHAPP — India\'s outdoor advertising booking platform.')">
    <meta name="robots" content="noindex, nofollow">

    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="icon" type="image/png" href="{{ asset('assets/images/favicon/Vector (1).png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">

    <!-- CSS only in head -->
    @vite(['resources/css/app.css'])

    @stack('styles')
</head>
<body class="antialiased">
    <div class="min-h-screen bg-gray-50">
        @yield('content')
    </div>

    {{-- JS at end of body to avoid render-blocking --}}
    @vite(['resources/js/app.js'])
    @stack('scripts')
</body>
</html>
