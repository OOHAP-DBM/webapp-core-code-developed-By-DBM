<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Customer</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased bg-gray-50">
    <!-- Toast Container (PROMPT 50) -->
    <div id="toast-container" class="position-fixed top-0 end-0" style="z-index: 9999;"></div>

    <div id="app" class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.partials.customer.sidebar')

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top Navigation Bar -->
            @include('layouts.partials.customer.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-6">
                <!-- Breadcrumb -->
                @include('layouts.partials.breadcrumb')

                <!-- Flash Messages -->
                @include('layouts.partials.flash-messages')

                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')
    
    <!-- Shortlist JavaScript (PROMPT 50) -->
    <script src="{{ asset('js/shortlist.js') }}"></script>
    
    @stack('scripts')
</body>
</html>
