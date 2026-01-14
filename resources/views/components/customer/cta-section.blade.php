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
                    <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-[#00baa8] to-[#009e8e] rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
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
                    <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-[#00baa8] to-[#009e8e] rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
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
                    <div class="w-20 h-20 mx-auto mb-6 bg-gradient-to-br from-[#00baa8] to-[#009e8e] rounded-2xl flex items-center justify-center transform group-hover:scale-110 transition-transform duration-300 shadow-lg">
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
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 bg-white rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <div class="text-4xl font-bold bg-gradient-to-r from-[#00baa8] to-[#009e8e] bg-clip-text text-transparent">{{ $stats['total_hoardings'] ?? 0 }}+</div>
                    <div class="text-sm text-gray-600 font-medium">Hoardings</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-[#00baa8] mb-2">{{ $stats['total_vendors'] ?? 0 }}+</div>
                    <div class="text-sm text-gray-600 font-medium">Vendors</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-[#00baa8] mb-2">{{ $stats['total_bookings'] ?? 0 }}+</div>
                    <div class="text-sm text-gray-600 font-medium">Bookings</div>
                </div>
                <div class="text-center">
                    <div class="text-4xl font-bold text-[#00baa8]">50+</div>
                    <div class="text-sm text-gray-600 font-medium">Cities</div>
                </div>
            </div>

            <!-- CTA Button -->
            <div class="text-center mt-12">
                <a href="{{ route('register.role-selection') }}"
                    class="inline-flex items-center px-8 py-4
                            bg-gradient-to-br from-[#00baa8] to-[#009e8e]
                            text-white rounded-xl font-bold text-lg
                            hover:from-[#009e8e] hover:to-[#007f73]
                            transform hover:scale-105 transition-all
                            shadow-lg hover:shadow-xl">
                        <span>Get Started Now</span>
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path>
                        </svg>
                </a>
            </div>
        </div>
    </div>
</section>
