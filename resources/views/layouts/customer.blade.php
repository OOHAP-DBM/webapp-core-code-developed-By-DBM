<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Customer</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link rel="icon" type="image/png" href="/assets/images/favicon/Vector (1).png" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
    <style>
        [x-cloak] { display: none !important; }

        @media (max-width: 1023px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 9999;
                transition: transform 0.3s ease;
            }
            #d-none { display: none; }
            #user { margin-left: 15px; }
        }

        /* Sidebar overlay backdrop */
        #sidebar-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.4);
            z-index: 9998;
        }
        #sidebar-overlay.active { display: block; }
    </style>
</head>
<body class="antialiased bg-gray-50">

    <!-- Toast Container -->
    <div id="toast-container" class="position-fixed top-0 end-0" style="z-index: 9999;"></div>

    <!-- Sidebar Backdrop Overlay (mobile) -->
    <div id="sidebar-overlay" onclick="closeSidebar()"></div>

    <div id="app" class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        @include('layouts.partials.customer.sidebar')

        <!-- Main Content Area -->
        <div class="flex flex-col flex-1 overflow-hidden">

            <!-- Top Navigation Bar -->
            @include('layouts.partials.customer.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50  md:p-6">
                @include('layouts.partials.breadcrumb')
                @include('layouts.partials.flash-messages')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('modals')

    <script src="{{ asset('js/shortlist.js') }}"></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        // ── Sidebar open/close ──────────────────────────────────────────
        function openSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebar-overlay');
            if (!sidebar) return;
            sidebar.classList.remove('hidden');
            overlay.classList.add('active');
            document.body.style.overflow = 'hidden';
        }

        function closeSidebar() {
            const sidebar  = document.getElementById('sidebar');
            const overlay  = document.getElementById('sidebar-overlay');
            if (!sidebar) return;
            sidebar.classList.add('hidden');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        }
        window.addEventListener('resize', function () {
            if (window.innerWidth >= 1024) {
                const overlay = document.getElementById('sidebar-overlay');
                if (overlay) overlay.classList.remove('active');
                document.body.style.overflow = '';
            }
        });

        document.addEventListener('DOMContentLoaded', function () {

            // Open button (hamburger in navbar)
            const openBtn  = document.getElementById('mobile-menu-btn');
            // Close button (X inside sidebar)
            const closeBtn = document.getElementById('mobile-btn-close');

            if (openBtn)  openBtn.addEventListener('click', openSidebar);
            if (closeBtn) closeBtn.addEventListener('click', closeSidebar);

            // Near Me button in navbar search
            document.getElementById('near-me-btn')?.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Geolocation not supported');
                    return;
                }
                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat  = position.coords.latitude;
                        const lng  = position.coords.longitude;
                        const form = document.getElementById('navbar-search-form');
                        if (!form) return;

                        let latInput   = document.createElement('input');
                        latInput.type  = 'hidden';
                        latInput.name  = 'lat';
                        latInput.value = lat;

                        let lngInput   = document.createElement('input');
                        lngInput.type  = 'hidden';
                        lngInput.name  = 'lng';
                        lngInput.value = lng;

                        form.appendChild(latInput);
                        form.appendChild(lngInput);
                        form.submit();
                    },
                    function () { alert('Location access denied'); }
                );
            });
        });
    </script>

    @stack('scripts')
    @include('layouts.partials.logout')

    <script>
        function openLogoutModal()  
        { 
            closeSidebar();
            document.getElementById('logoutModal').classList.remove('hidden'); 
        }
        function closeLogoutModal() { document.getElementById('logoutModal').classList.add('hidden'); }
    </script>

</body>
</html>