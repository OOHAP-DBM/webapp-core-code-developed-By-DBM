@extends('layouts.app')


@section('content')
    <!-- Navigation -->
    @include('components.customer.navbar')

    <!-- Hero Banner -->
    <!-- Search Section -->
    {{-- @include('components.customer.search-bar') --}}

    <!-- Best Hoardings Section -->
    <section id="best-hoardings-section" class=" pb-6 pt-10 md:pt-25 scroll-mt-32 md:scroll-mt-48">
        <div class="w-full px-6 md:px-10 lg:px-20 px-4">
            <!-- Section Header -->
            <div class="mb-3">
                <h2 class="text-2xl font-bold text-gray-900">Best Hoardings</h2>
            </div>

            <!-- Hoardings Grid -->
            <div id="hoardingGrid" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                @foreach($bestHoardings as $hoarding)
                    @include('components.customer.hoarding-card', ['hoarding' => $hoarding])
                @endforeach

                <div class="col-span-full mt-8 pt-3 border-t border-gray-200">
                    <div class="flex flex-col items-center gap-3 sm:flex-row sm:items-center sm:justify-between">
                        <p class="text-sm text-gray-500 font-medium order-2 sm:order-1">
                            Showing {{ $bestHoardings->firstItem() ?? 0 }}–{{ $bestHoardings->lastItem() ?? 0 }} of {{ $bestHoardings->total() }} results
                        </p>
                        <div class="order-1 sm:order-2">
                            {{ $bestHoardings->links('pagination.vendor-compact') }}
                        </div>
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
      <div class="w-full px-6 md:px-10 lg:px-20 px-4">
          <hr class="border-gray-200">
      </div>
       <section class="py-6 bg-white">
            <div class="w-full px-6 md:px-10 lg:px-20 px-4 text-center">
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
       <div class="w-full px-6 md:px-10 lg:px-20 px-4">
            <hr class="border-gray-200">
       </div>
    @endguest
    @include('home.home_contact_enquiry')
    <!-- Featured Categories -->
    <section id="top-spots-section" class="py-6 bg-white scroll-mt-32 md:scroll-mt-48">
        <div class="w-full px-6 md:px-10 lg:px-20 px-4">
            <div class=" mb-6">
                <!-- <h2 class="text-lg md:text-4xl font-bold text-gray-900 mb-3">Top Spots</h2> -->
                <h2 class="text-2xl font-bold text-gray-900">Top Spots</h2>
                <!-- <p class="text-gray-600">Explore advertising opportunities in major cities</p> -->
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 2xl:grid-cols-6 gap-6">
                @foreach($topStates as $state)
                    @include('components.customer.category-card', ['state' => $state])
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
