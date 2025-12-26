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
      <div class="container mx-auto px-4">
          <hr class="border-gray-200">
      </div>
       <section class="py-12 bg-white">
            <div class="container mx-auto px-4 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    See Personalized Recommendations
                </h3>

                @guest
                    <div class="flex items-center justify-center space-x-4">
                        <a href="{{ route('login') }}"
                        class="px-8 py-3 bg-gray-900 text-white rounded font-semibold hover:bg-gray-800">
                            Login
                        </a>

                        <span class="text-gray-500">New on OOHAPP?</span>

                        <a href="{{route('register.role-selection')}}"
                        class="text-teal-600 font-semibold hover:text-teal-700">
                            Signup
                        </a>
                    </div>
                @endguest
            </div>
       </section>
       <div class="container mx-auto px-4">
            <hr class="border-gray-200">
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
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-3">Top Spots</h2>
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

     
   

    <!-- Footer -->
    @include('components.customer.footer')
@endsection
