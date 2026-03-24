<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<!-- ============================================================
     HEAD
     ============================================================ -->
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Vendor</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="/assets/images/favicon/Vector (1).png" />

    <!-- External CSS Libraries -->
    {{-- CSS only in head — no render-blocking JS --}}
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Vite CSS only -->
    @vite(['resources/css/app.css'])

    <!-- Page-specific styles -->
    @stack('styles')

    <!-- ============================================================
         CSS
         ============================================================ -->
    <style>
        /* ---------- Global Font ---------- */
        html, body, * {
            font-family: 'Poppins', sans-serif !important;
        }

        /* ---------- Alpine cloak ---------- */
        [x-cloak] {
            display: none !important;
        }

        /* =====================================================
           MOBILE  (max-width: 1023px)
           ===================================================== */
        @media (max-width: 1023px) {

            /* Hide sidebar arrow button on mobile */
            #sidebar-toggle-btn {
                display: none !important;
            }

            /* Overlay always on top */
            #sidebar-overlay {
                z-index: 999999999 !important;
            }

            /* Remove z-index from main content */
            #main-content-area {
                z-index: auto !important;
            }

            /* Sidebar — open state */
            #vendor-sidebar:not(.hidden) {
                position: fixed;
                top: 0;
                left: 0;
                height: 100vh;
                z-index: 2147483647 !important;
                display: flex;
                flex-direction: column;
                width: 80vw !important;
                max-width: 340px !important;
                min-width: 220px !important;
                box-shadow: 2px 0 16px 0 rgba(0, 0, 0, 0.08);
                background: #fff;
                transition: left 0.3s cubic-bezier(.4, 0, .2, 1);
            }

            /* Overlay */
            #sidebar-overlay {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.25);
                z-index: 2147483646;
                display: none;
            }

            #sidebar-overlay.active {
                display: block;
            }

            /* Prevent body scroll when sidebar is open */
            body.sidebar-open {
                overflow: hidden !important;
                touch-action: none;
            }
        }

        /* =====================================================
           DESKTOP  (min-width: 1024px)
           ===================================================== */
        @media (min-width: 1024px) {

            #vendor-sidebar {
                position: relative !important;
                display: flex !important;
                flex-direction: column;
            }

            /* Collapsed state */
            #vendor-sidebar.sidebar-collapsed {
                width: 64px !important;
                min-width: 64px !important;
                max-width: 64px !important;
            }

            #main-content-area.sidebar-collapsed-content {
                margin-left: 0 !important;
            }

            /* Expanded state */
            #vendor-sidebar.sidebar-expanded {
                width: 16rem !important;
                min-width: 16rem !important;
                max-width: 16rem !important;
            }

            #main-content-area.sidebar-expanded-content {
                margin-left: 0 !important;
            }

            /* --- Collapsed: hide text labels --- */
            #vendor-sidebar.sidebar-collapsed .sb-nav-text {
                display: none !important;
            }

            /* --- Collapsed: hide dropdown arrows --- */
            #vendor-sidebar.sidebar-collapsed .sb-nav-arrow {
                display: none !important;
            }

            /* --- Collapsed: hide open submenus / alpine dropdowns --- */
            #vendor-sidebar.sidebar-collapsed [x-show] {
                display: none !important;
            }

            /* --- Collapsed: center nav items (icon only) --- */
            #vendor-sidebar.sidebar-collapsed nav a,
            #vendor-sidebar.sidebar-collapsed nav button {
                justify-content: center !important;
                padding-left: 0 !important;
                padding-right: 0 !important;
                gap: 0 !important;
            }

            /* --- Collapsed: keep icon visible --- */
            #vendor-sidebar.sidebar-collapsed .sidebar-icon {
                display: flex !important;
                min-width: unset !important;
            }

            /* --- Collapsed: center avatar in profile section --- */
            #vendor-sidebar.sidebar-collapsed .bg-white.px-6.py-5 {
                padding-left: 10px !important;
                padding-right: 0 !important;
            }

            #vendor-sidebar.sidebar-collapsed .bg-white.px-6.py-5 > div {
                padding-left: 0 !important;
                padding-right: 0 !important;
                display: flex !important;
                justify-content: center !important;
            }

            #vendor-sidebar.sidebar-collapsed .bg-white.px-6.py-5 a.flex {
                display: flex !important;
                justify-content: center !important;
                align-items: center !important;
                padding: 4px 0 !important;
                gap: 0 !important;
            }

            /* --- Collapsed: hide name text and edit icon --- */
            #vendor-sidebar.sidebar-collapsed .bg-white.px-6.py-5 a > div:last-child {
                display: none !important;
            }

            /* --- Collapsed: hide bottom border in profile section --- */
            #vendor-sidebar.sidebar-collapsed .bg-white.px-6.py-5 .border-b {
                display: none !important;
            }

            /* Hide logo and profile image when sidebar is collapsed */
            #vendor-sidebar.sidebar-collapsed .sidebar-hide-when-collapsed {
                display: none !important;
            }
        }
                /* When sidebar is collapsed, add top margin to nav for icon alignment */
        #vendor-sidebar.sidebar-collapsed nav {
            margin-top: 180px !important;
        }


        /* =====================================================
           SMALL MOBILE  (max-width: 767px)
           ===================================================== */
        @media only screen and (max-width: 767px) {

            body.sidebar-open aside#vendor-sidebar {
                position: fixed !important;
                top: 0;
                left: 0;
                z-index: 999999999;
                display: block;
                height: 100%;
                overflow-x: hidden;
                overflow-y: auto;
                outline: 0;
                transform: translateX(0%);
                background: #fff;
                box-shadow: 2px 0 16px 0 rgba(0, 0, 0, 0.08);
                max-width: 340px !important;
                min-width: 220px !important;
                width: 80vw !important;
                display: flex;
                flex-direction: column;
                transition: transform .8s ease-out;
            }
        }

       
    </style>
</head>


<!-- ============================================================
     BODY / HTML
     ============================================================ -->
<body class="antialiased bg-gray-50">

    <script>
        (function() {
            try {
                if (window.innerWidth <= 1023) {
                    var style = document.createElement('style');
                    style.id = 'sidebar-init-hide';
                    style.textContent = '#vendor-sidebar { display: none !important; }';
                    document.head.appendChild(style);
                }
            } catch(e) {}
        })();
        </script>
    <div id="sidebar-overlay" onclick="closeSidebarMobile()"></div>

    <div id="app" class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        @include('layouts.partials.vendor.sidebar')

        <!-- Main Content Area -->
        <div id="main-content-area" class="flex flex-col flex-1 overflow-hidden transition-all duration-300">

            <!-- Top Navigation Bar -->
            @include('layouts.partials.vendor.navbar')

            <!-- Page Content -->
            <main class="flex-1 overflow-y-auto bg-gray-50 p-3">

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
    {{-- @include('vendor.pos.components.pos-timer-notification') --}}

    <!-- Logout Modal -->
    @include('layouts.partials.logout')

    <!-- Page-specific scripts -->
    @stack('scripts')


    <!-- ============================================================
         JS
         ============================================================ -->

    <!-- SweetAlert2 -->
    {{-- JS deferred at end of body to avoid render-blocking --}}
    @vite(['resources/js/app.js'])
    <script defer src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/@alpinejs/collapse@3.x.x/dist/cdn.min.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    <script>
        /* -------------------------------------------------------
           Sidebar — Desktop toggle (collapse / expand)
           ------------------------------------------------------- */
        function toggleSidebar() {
            const sidebar     = document.getElementById('vendor-sidebar');
            const mainContent = document.getElementById('main-content-area');
            const arrowIcon   = document.getElementById('sidebar-arrow-icon');
            const isCollapsed = sidebar.classList.contains('sidebar-collapsed');

            if (isCollapsed) {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('sidebar-collapsed-content');
                mainContent.classList.add('sidebar-expanded-content');
                arrowIcon.style.transform = 'rotate(0deg)';
            } else {
                sidebar.classList.add('sidebar-collapsed');
                sidebar.classList.remove('sidebar-expanded');
                mainContent.classList.add('sidebar-collapsed-content');
                mainContent.classList.remove('sidebar-expanded-content');
                arrowIcon.style.transform = 'rotate(180deg)';
            }
        }

        /* -------------------------------------------------------
           Sidebar — Mobile close
           ------------------------------------------------------- */
        function closeSidebarMobile() {
            const sidebar = document.getElementById('vendor-sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            sidebar.classList.add('hidden');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        }

        /* -------------------------------------------------------
           DOMContentLoaded — initial state + open/close buttons
           ------------------------------------------------------- */
        document.addEventListener('DOMContentLoaded', function () {
            // Remove the CSS hide rule, JS takes over from here
            var initStyle = document.getElementById('sidebar-init-hide');
            if (initStyle) initStyle.remove();

            const sidebar     = document.getElementById('vendor-sidebar');
            const mainContent = document.getElementById('main-content-area');
            const arrowIcon   = document.getElementById('sidebar-arrow-icon');
            const openBtn     = document.getElementById('vendor-mobile-menu-btn');
            const closeBtn    = document.getElementById('vendor-mobile-btn-close');
            const overlay     = document.getElementById('sidebar-overlay');

            // Initial state: open on desktop, hidden on mobile
            if (window.innerWidth <= 1023) {
                sidebar.classList.add('hidden');
            } else {
                sidebar.classList.remove('sidebar-collapsed');
                sidebar.classList.add('sidebar-expanded');
                mainContent.classList.remove('sidebar-collapsed-content');
                mainContent.classList.add('sidebar-expanded-content');
                arrowIcon.style.transform = 'rotate(0deg)';
            }

            // Mobile open button
            if (openBtn) {
                openBtn.addEventListener('click', function () {
                    sidebar.classList.remove('hidden');
                    if (window.innerWidth <= 1023) {
                        overlay.classList.add('active');
                        document.body.classList.add('sidebar-open');
                    }
                });
            }

            // Mobile close button
            if (closeBtn) {
                closeBtn.addEventListener('click', function () {
                    sidebar.classList.add('hidden');
                    if (window.innerWidth <= 1023) {
                        overlay.classList.remove('active');
                        document.body.classList.remove('sidebar-open');
                    }
                });
            }
        });

        /* -------------------------------------------------------
           Logout Modal
           ------------------------------------------------------- */
        function openLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (!modal) return;

            // Keep modal at document root so page-specific stacking contexts can't trap it.
            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            // Hard fallback for environments where arbitrary z-index classes are not generated.
            modal.style.zIndex = '2147483647';
            const dialog = modal.querySelector(':scope > div');
            if (dialog) {
                dialog.style.zIndex = '2147483647';
            }

            const sidebar = document.getElementById('vendor-sidebar');
            if (sidebar && window.matchMedia('(max-width: 1023px)').matches) {
                sidebar.classList.add('hidden');
            }

            modal.classList.remove('hidden');
            document.body.classList.add('overflow-hidden');
        }

        function closeLogoutModal() {
            const modal = document.getElementById('logoutModal');
            if (!modal) return;

            modal.classList.add('hidden');
            document.body.classList.remove('overflow-hidden');
        }
    </script>

    <!-- Add 'hidden' class to #vendor-sidebar.sidebar-expanded on screens <= 767px -->
    <script>
        (function() {
            function hideSidebarOnSmallScreen() {
                var sb = document.getElementById('vendor-sidebar');
                if (window.innerWidth <= 767 && sb && sb.classList.contains('sidebar-expanded')) {
                    sb.classList.add('hidden');
                } else if (window.innerWidth > 767 && sb) {
                    sb.classList.remove('hidden');
                }
            }
            window.addEventListener('resize', hideSidebarOnSmallScreen);
            hideSidebarOnSmallScreen();
        })();
    </script>

</body>
</html>