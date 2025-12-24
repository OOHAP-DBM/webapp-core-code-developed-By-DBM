<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Dashboard') - OOHAPP Admin</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script src="https://cdn.tailwindcss.com"></script>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        [x-cloak] { display: none !important; }
        
        .sidebar-link {
            @apply flex items-center px-4 py-3 text-gray-400 hover:bg-gray-800 hover:text-white transition-colors duration-200 rounded-lg mx-2;
        }
        .sidebar-link.active {
            @apply bg-blue-600 text-white;
        }
        .sidebar-submenu-title {
            @apply px-6 mt-4 mb-1 text-xs font-bold text-gray-500 uppercase tracking-widest;
        }
    </style>
    @stack('styles')
</head>
<body class="antialiased bg-gray-50 font-inter" x-data="{ sidebarOpen: false }">
    
    <div x-show="sidebarOpen" 
         @click="sidebarOpen = false" 
         class="fixed inset-0 z-40 bg-gray-900/50 backdrop-blur-sm lg:hidden transition-opacity"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0">
    </div>

    <div class="flex h-screen overflow-hidden">
        
        @include('layouts.partials.admin.sidebar')

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden lg:ml-64">
            
            @include('layouts.partials.admin.navbar')

            <main class="flex-1 overflow-y-auto bg-gray-50 p-4 md:p-8">
                <div class="max-w-7xl mx-auto">
                    @include('layouts.partials.flash-messages')
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('modals')
    @stack('scripts')
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</body>
</html>