<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Vendor</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="antialiased bg-gray-50">
    <div id="app" class="flex h-screen overflow-hidden">
        <!-- Sidebar -->
        @include('layouts.partials.vendor.sidebar')

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">
            <!-- Top Navigation Bar -->
            @include('layouts.partials.vendor.navbar')

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
    <style>
    @media (max-width: 1023px) {
        #vendor-sidebar {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 9999;
        }
    }
</style>
    @stack('scripts')
<script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
<script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar   = document.getElementById('vendor-sidebar');
            const openBtn   = document.getElementById('vendor-mobile-menu-btn');
            const closeBtn  = document.getElementById('vendor-mobile-btn-close');
            if (openBtn) {
                openBtn.addEventListener('click', function () {
                    sidebar.classList.remove('hidden');
                });
            }
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    sidebar.classList.add('hidden');
                });
            }
        });
</script>
</body>
</html>
