{{-- Public Header (Landing page / Guest users) --}}
<header class="bg-white shadow-sm border-b border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
            <!-- Logo -->
            <div class="flex items-center">
                <a href="{{ route('home') }}" class="flex items-center">
                    <img src="{{ asset('images/logo.svg') }}" alt="OOHAPP" class="h-8 w-auto">
                    <span class="ml-2 text-xl font-bold text-gray-900">OOHAPP</span>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="hidden md:flex space-x-8">
                <a href="{{ route('hoardings.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                    Hoardings
                </a>
                <a href="{{ route('dooh.index') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                    DOOH
                </a>
                <a href="{{ route('search') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                    Search
                </a>
            </nav>

            <!-- Auth Links -->
            <div class="flex items-center space-x-4">
                @guest
                    <a href="{{ route('login') }}" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                        Login
                    </a>
                    <a href="{{ route('register') }}" class="bg-blue-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-blue-700">
                        Sign Up
                    </a>
                @else
                    <!-- Role Switcher (PROMPT 96) -->
                    @include('components.role-switcher')
                    
                    <a href="{{ auth()->user()->getDashboardRoute() }}" 
                       class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="text-gray-700 hover:text-gray-900 px-3 py-2 text-sm font-medium">
                            Logout
                        </button>
                    </form>
                @endguest
            </div>
        </div>
    </div>
</header>
