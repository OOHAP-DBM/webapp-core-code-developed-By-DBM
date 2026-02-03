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
    <style>
    [x-cloak] {
            display: none !important;
        }
    </style>
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
    <style>
        @media (max-width: 1023px) {
            #sidebar {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 9999;
            }
            #d-none{
                display:none;
            }
            #user{
                margin-left:15px;
            }
        }
</style>
    <!-- Shortlist JavaScript (PROMPT 50) -->
    <script src="{{ asset('js/shortlist.js') }}"></script>
    <script src="//unpkg.com/alpinejs" defer></script>
    <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar   = document.getElementById('sidebar');
            const openBtn   = document.getElementById('mobile-menu-btn');
            const closeBtn  = document.getElementById('mobile-btn-close');

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
            document.getElementById('near-me-btn')?.addEventListener('click', function () {
                if (!navigator.geolocation) {
                    alert('Geolocation not supported');
                    return;
                }

                navigator.geolocation.getCurrentPosition(
                    function (position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;

                        const form = document.getElementById('navbar-search-form');

                        let latInput = document.createElement('input');
                        latInput.type = 'hidden';
                        latInput.name = 'lat';
                        latInput.value = lat;

                        let lngInput = document.createElement('input');
                        lngInput.type = 'hidden';
                        lngInput.name = 'lng';
                        lngInput.value = lng;

                        form.appendChild(latInput);
                        form.appendChild(lngInput);

                        form.submit();
                    },
                    function () {
                        alert('Location access denied');
                    }
                );
            });
        });
    </script>


    @stack('scripts')
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
