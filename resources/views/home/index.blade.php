@extends('layouts.app')

@section('title', 'Home - Seamless Hoarding Booking')

@section('content')
    <!-- Navigation -->
    @include('components.customer.navbar')

    <!-- Hero Banner -->
    @include('components.customer.hero-banner')
    @include('home.home_contact_enquiry')
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

            @if($bestHoardings->hasPages())
                <div class="mt-8 pt-6 border-t border-gray-200 flex items-center justify-between">
                    
                    {{-- LEFT : Showing text --}}
                    <div class="text-sm text-gray-600">
                        Showing
                        <span class="font-semibold">
                            {{ $bestHoardings->firstItem() }}
                        </span>
                        to
                        <span class="font-semibold">
                            {{ $bestHoardings->lastItem() }}
                        </span>
                        of
                        <span class="font-semibold">
                            {{ $bestHoardings->total() }}
                        </span>
                    </div>

                    {{-- RIGHT : Pagination --}}
                    <div>
                        {{ $bestHoardings->links() }}
                    </div>

                </div>
            @endif

            
         
        </div>
        
    </section>
    @guest
     <!-- Personalized Recommendations CTA -->
      <div class="container mx-auto px-4">
          <hr class="border-gray-200">
      </div>
       <section class="py-12 bg-white">
            <div class="container mx-auto px-4 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    See Personalized Recommendations
                </h3>

                    <div class="flex flex-col items-center justify-center space-y-4">
                        <a href="{{ route('login') }}"
                        class="px-24 py-3 bg-gray-900 text-white rounded font-semibold hover:bg-gray-800">
                            Login
                        </a>
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-500">New on OOHAPP?</span>

                            <a href="{{ route('register.role-selection') }}"
                            class="text-btn-color font-semibold transition-colors">
                                Signup
                            </a>
                        </div>
                    </div>
            </div>
       </section>
       <div class="container mx-auto px-4">
            <hr class="border-gray-200">
       </div>
    @endguest

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
    @if(isset($testimonials) && $testimonials->count())
     @include('components.customer.testimonials')
    @endif
    <!-- Footer -->
    @include('components.customer.footer')
@endsection
