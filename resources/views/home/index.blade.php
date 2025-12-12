@extends('layouts.app')

@section('title', 'Home - Seamless Hoarding Booking')

@section('content')
    <!-- Navigation -->
    @include('components.customer.navbar')

    <!-- Hero Banner -->
    @include('components.customer.hero-banner')

    <!-- Search Section -->
    {{-- @include('components.customer.search-bar') --}}

    <!-- Best Hoardings Section -->
    <section class="py-12 bg-gray-50">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Best Hoardings</h2>
            </div>

            <!-- Hoardings Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @forelse($bestHoardings as $hoarding)
                    @include('components.customer.hoarding-card', ['hoarding' => $hoarding])
                @empty
                    <div class="col-span-full text-center py-12">
                        <p class="text-gray-500">No hoardings available at the moment.</p>
                    </div>
                @endforelse
            </div>

            <!-- Pagination Info -->
            @if($bestHoardings->count() > 0)
                <div class="flex items-center justify-between mt-8 pt-6 border-t border-gray-200">
                    <div class="text-sm text-gray-600">
                        Showing <span class="font-semibold">1</span> of <span class="font-semibold">300</span>
                    </div>
                    <div class="flex items-center space-x-1">
                        <button class="w-8 h-8 flex items-center justify-center rounded bg-teal-500 text-white font-semibold text-sm hover:bg-teal-600">
                            1
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            2
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            3
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            4
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            5
                        </button>
                        <span class="px-2 text-gray-500">...</span>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            35
                        </button>
                        <button class="w-8 h-8 flex items-center justify-center rounded border border-gray-300 text-gray-700 text-sm hover:bg-gray-50">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>
                </div>
            @endif

            
         
        </div>
        
    </section>
     <!-- Personalized Recommendations CTA -->
            <div class="mt-12 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-4">See Personalized Recommendations</h3>
                @guest
                    <div class="flex items-center justify-center space-x-4">
                        <a href="{{ route('login') }}" class="px-8 py-3 bg-gray-900 text-white rounded font-semibold hover:bg-gray-800 transition-colors">
                            Login
                        </a>
                        <span class="text-gray-500">New on OOHAPP?</span>
                        <a href="{{ route('register') }}" class="text-teal-600 font-semibold hover:text-teal-700">
                            Signup
                        </a>
                    </div>
                @endguest
            </div>
     <!-- Top DOOH Section -->
    {{-- <section class="py-16 bg-gray-50">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Top DOOHs</h2>
                <p class="text-gray-600">Digital out-of-home advertising screens</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                @foreach($topDOOHs as $dooh)
                    @include('components.customer.dooh-card', ['dooh' => $dooh])
                @endforeach
            </div>
        </div>
    </section> --}}

  

   

    <!-- Featured Categories -->
    <section class="py-16 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-12">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Top DOAs</h2>
                <p class="text-gray-600">Explore advertising opportunities in major cities</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                @foreach($topCities as $city)
                    @include('components.customer.category-card', ['city' => $city])
                @endforeach
            </div>
        </div>
    </section>
      

     <!-- Why Choose OOHAPP -->
    @include('components.customer.cta-section')

      <!-- Newsletter Subscription -->
    <section class="py-12 bg-gray-900">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto">
                <!-- Flash Messages -->
                @if(session('newsletter_success'))
                    <div class="mb-6 p-4 bg-green-500 text-white rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('newsletter_success') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('newsletter_error'))
                    <div class="mb-6 p-4 bg-red-500 text-white rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('newsletter_error') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                @if(session('newsletter_info'))
                    <div class="mb-6 p-4 bg-blue-500 text-white rounded-lg flex items-center justify-between">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                            </svg>
                            <span>{{ session('newsletter_info') }}</span>
                        </div>
                        <button onclick="this.parentElement.remove()" class="text-white hover:text-gray-200">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>
                @endif

                <div class="flex flex-col md:flex-row items-center justify-between gap-6">
                    <!-- Left Content -->
                    <div class="text-white md:w-1/2">
                        <h2 class="text-2xl md:text-3xl font-bold mb-2">Unlock special offers just for you</h2>
                        <p class="text-gray-300 text-sm md:text-base">The finest offers are the ones that arrive in your inbox.</p>
                    </div>

                    <!-- Right - Email Form -->
                    <div class="md:w-1/2 w-full max-w-md">
                        <form action="{{ route('newsletter.subscribe') }}" method="POST" class="flex gap-2">
                            @csrf
                            <input 
                                type="email" 
                                name="email" 
                                placeholder="Your email" 
                                value="{{ old('email') }}"
                                required
                                class="flex-1 px-4 py-3 rounded-md border-none focus:outline-none focus:ring-2 focus:ring-teal-500 text-gray-900 @error('email') ring-2 ring-red-500 @enderror"
                            >
                            <button 
                                type="submit" 
                                class="px-8 py-3 bg-teal-500 text-white font-semibold rounded-md hover:bg-teal-600 transition-colors whitespace-nowrap"
                            >
                                Subscribe
                            </button>
                        </form>
                        @error('email')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </section>
   

    <!-- Footer -->
    @include('components.customer.footer')
@endsection
