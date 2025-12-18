<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'OOHAPP') }} - @yield('title', 'Hoarding Marketplace')</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts & Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased">
    <div id="app" class="min-h-screen bg-gray-50">
        {{-- <!-- Header -->
        @include('layouts.partials.header') --}}

        <!-- Main Content -->
        <main class="">
            @yield('content')
        </main>

        <!-- Footer -->
        {{-- @include('layouts.partials.footer') --}}
    </div>

    @stack('modals')
    @stack('scripts')
</body>
</html>
