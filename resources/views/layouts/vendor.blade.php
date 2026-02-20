<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Vendor</title>
    <link rel="icon" type="image/png" href="/assets/images/favicon/Vector (1).png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
<style>
    [x-cloak]{
        display:none !important;
    }
</style>
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
            @stack('vendor-modals')
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
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @include('layouts.partials.logout')
    <script>
    function openLogoutModal() {
        document.getElementById('logoutModal').classList.remove('hidden');
    }

    function closeLogoutModal() {
        document.getElementById('logoutModal').classList.add('hidden');
    }
</script>
</body>
</html>
