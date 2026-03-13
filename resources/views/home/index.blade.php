@extends('layouts.app')


@section('content')
    <!-- Navigation -->
    @include('components.customer.navbar')

    <!-- Hero Banner -->
    @include('components.customer.hero-banner')
    <!-- Search Section -->
    {{-- @include('components.customer.search-bar') --}}

    <!-- Best Hoardings Section -->
    <section class="py-6 bg-gray-50">
        <div class="container mx-auto px-4">
            <!-- Section Header -->
            <div class="mb-3">
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
                @php
                    $compactPaginator = $bestHoardings->appends(request()->except('page'));
                    $currentPage = $compactPaginator->currentPage();
                    $lastPage = $compactPaginator->lastPage();
                    $startPage = max(1, $currentPage - 1);
                    $endPage = min($lastPage, $startPage + 2);
                    $startPage = max(1, $endPage - 2);
                @endphp

                <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    
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
                    <nav aria-label="Best Hoardings pagination" class="overflow-x-auto">
                        <div class="flex items-center gap-2 whitespace-nowrap">
                            @if ($compactPaginator->onFirstPage())
                                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">Previous</span>
                            @else
                                <a href="{{ $compactPaginator->previousPageUrl() }}" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Previous</a>
                            @endif

                            @for ($page = $startPage; $page <= $endPage; $page++)
                                @if ($page === $currentPage)
                                    <span class="min-w-[40px] text-center px-3 py-2 rounded-md border border-[#00A86B] bg-[#00A86B] text-white font-semibold text-sm">{{ $page }}</span>
                                @else
                                    <a href="{{ $compactPaginator->url($page) }}" class="min-w-[40px] text-center px-3 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">{{ $page }}</a>
                                @endif
                            @endfor

                            @if ($compactPaginator->hasMorePages())
                                <a href="{{ $compactPaginator->nextPageUrl() }}" class="px-4 py-2 rounded-md border border-gray-300 bg-white text-gray-700 hover:bg-gray-50 text-sm">Next</a>
                            @else
                                <span class="px-4 py-2 rounded-md border border-gray-300 bg-gray-100 text-gray-400 cursor-not-allowed text-sm">Next</span>
                            @endif
                        </div>
                    </nav>

                </div>
            @endif

            
         
        </div>
        
    </section>
    @guest
     <!-- Personalized Recommendations CTA -->
      <div class="container mx-auto px-4">
          <hr class="border-gray-200">
      </div>
       <section class="py-6 bg-white">
            <div class="container mx-auto px-4 text-center">
                <h3 class="text-xl font-bold text-gray-900 mb-4">
                    See Personalized Recommendations
                </h3>

                    <div class="flex flex-col items-center justify-center space-y-4">
                        <a href="{{ route('login') }}"
                        class="px-20 py-2 bg-gray-900 text-white rounded font-semibold hover:bg-gray-800">
                            Login
                        </a>
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-500">New on OOHAPP?</span>

                            <a href="{{ route('register.role-selection') }}"
                                class="text-[#008ae0] font-semibold border-b-1 border-[#008ae0] hover:border-[#006bb3] transition">
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
    @include('home.home_contact_enquiry')
    <!-- Featured Categories -->
    <section class="py-6 bg-white">
        <div class="container mx-auto px-4">
            <div class=" mb-6">
                <!-- <h2 class="text-lg md:text-4xl font-bold text-gray-900 mb-3">Top Spots</h2> -->
                <h2 class="text-2xl font-bold text-gray-900">Top Spots</h2>
                <!-- <p class="text-gray-600">Explore advertising opportunities in major cities</p> -->
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
@endsection
