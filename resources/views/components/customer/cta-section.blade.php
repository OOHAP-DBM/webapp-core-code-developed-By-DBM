<section class="py-20 bg-gradient-to-br from-blue-50 to-purple-50">
    <div class="container mx-auto px-4">
        <div class="max-w-6xl mx-auto">
            <!-- Section Header -->
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Why Choose OOHAPP?
                </h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">
                    Your trusted partner for outdoor advertising success
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 mb-16">
                <!-- Feature 1 -->
                <div class="text-center group">
                    <div class="w-20 h-20 mx-auto mb-6 theme-gradient rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Instant Booking</h3>
                    <p class="text-gray-600">
                        Book your advertising space in minutes with our streamlined process
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="text-center group">
                    <div class="w-20 h-20 mx-auto mb-6 theme-gradient rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Verified Vendors</h3>
                    <p class="text-gray-600">
                        All vendors are verified and quality-checked for your peace of mind
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="text-center group">
                    <div class="w-20 h-20 mx-auto mb-6 theme-gradient rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
                        <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 mb-3">Best Prices</h3>
                    <p class="text-gray-600">
                        Competitive pricing with transparent billing and no hidden charges
                    </p>
                </div>
            </div>

            <!-- Stats Section -->
            @php
                $formatCount = function ($count) {
                    return $count > 100 ? 100 : $count;
                };

                // $totalHoardings = $formatCount($stats['total_hoardings'] ?? 0);
                // $totalVendors   = $formatCount($stats['total_vendors'] ?? 0);
                // $totalBookings  = $formatCount($stats['total_bookings'] ?? 0);
                // $totalCities    = 50;
                $totalHoardings = 50000;
                $hoardingsSuffix = '+';
                $totalVendors   = 500;
                $vendorsSuffix = '+';
                $totalBookings  = 2000;
                $bookingsSuffix = '+';
                $totalCities    = 500;
                $citiesSuffix = '+';
            @endphp

            <div id="stats-section" class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <div class="text-4xl font-bold theme-gradient-text mb-2">
                        <span class="stat-number" data-target="{{ $totalHoardings }}" data-suffix="{{ $hoardingsSuffix }}">0</span>{{ $hoardingsSuffix }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Hoardings</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-btn-color mb-2">
                        <span class="stat-number" data-target="{{ $totalVendors }}" data-suffix="{{ $vendorsSuffix }}">0</span>{{ $vendorsSuffix }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Vendors</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-btn-color mb-2">
                        <span class="stat-number" data-target="{{ $totalBookings }}" data-suffix="{{ $bookingsSuffix }}">0</span>{{ $bookingsSuffix }}
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Bookings</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-btn-color mb-2">
                        <span class="stat-number" data-target="{{ $totalCities }}" data-suffix="">0</span>
                    </div>
                    <div class="text-sm text-gray-600 font-medium">Cities</div>
                </div>
            </div>

            <!-- CTA Button (only for guests) -->
            @guest
            <div class="text-center mt-12">
                <a href="{{ route('register.role-selection') }}"
                    class="inline-flex items-center px-8 py-4
                            theme-gradient
                            text-white rounded-xl font-bold text-lg
                            transform hover:scale-105 transition-all
                            shadow-lg hover:shadow-xl">
                        <span>Get Started Now</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                </a>
            </div>
            @endguest
        </div>
    </div>
</section>

<script>
    (function () {
        /**
         * Animates a single element from 0 up to its data-target value.
         * @param {HTMLElement} el
         */
        function animateStat(el) {
            const target   = parseInt(el.dataset.target, 10);
            const duration = 1800; // ms
            const start    = performance.now();

            function step(now) {
                const elapsed  = now - start;
                const progress = Math.min(elapsed / duration, 1);

                // Ease-out cubic: starts fast, slows near the end
                const eased  = 1 - Math.pow(1 - progress, 3);
                const current = Math.floor(eased * target);

                el.textContent = current;

                if (progress < 1) {
                    requestAnimationFrame(step);
                } else {
                    el.textContent = target; // Ensure exact final value
                }
            }

            requestAnimationFrame(step);
        }

        // Trigger animation when the stats section enters the viewport
        const statsSection = document.getElementById('stats-section');
        const statNumbers  = statsSection ? statsSection.querySelectorAll('.stat-number') : [];

        if (!statsSection || !statNumbers.length) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    statNumbers.forEach(animateStat);
                    observer.unobserve(entry.target); // Only animate once
                }
            });
        }, { threshold: 0.3 });

        observer.observe(statsSection);
    })();
</script>