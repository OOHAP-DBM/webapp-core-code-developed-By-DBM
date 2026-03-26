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
            <div id="hoardingGrid">
                @include('components.customer.hoarding-grid', ['bestHoardings' => $bestHoardings])

                <div class="mt-8 pt-6 border-t border-gray-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="text-sm text-gray-600 font-medium">
                        Showing {{ $bestHoardings->firstItem() ?? 0 }} - {{ $bestHoardings->lastItem() ?? 0 }} of {{ $bestHoardings->total() }}
                    </div>
                    <div>
                        {{ $bestHoardings->links('pagination.vendor-compact') }}

                    </div>
                </div>
            </div>

            <script>
            function loadHoardings(url) {
                const grid = document.getElementById('hoardingGrid');
                grid.classList.add('opacity-50');
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.json())
                    .then(data => {
                        grid.innerHTML = data.html + data.pagination;
                        grid.classList.remove('opacity-50');
                        window.scrollTo({ top: grid.offsetTop - 100, behavior: 'smooth' });
                    });
            }

            document.addEventListener('click', function(e) {
                if (e.target.closest('.pagination a')) {
                    e.preventDefault();
                    loadHoardings(e.target.closest('a').href);
                }
            });

            document.querySelectorAll('.filter-input').forEach(function(input) {
                input.addEventListener('change', function() {
                    const params = new URLSearchParams();
                    document.querySelectorAll('.filter-input').forEach(function(i) {
                        if (i.value) params.append(i.name, i.value);
                    });
                    loadHoardings('/ajax/hoardings?' + params.toString());
                });
            });
            </script>

            
         
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
