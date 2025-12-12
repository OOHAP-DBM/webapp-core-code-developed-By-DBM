<section class="relative bg-gray-900 text-white overflow-hidden" style="min-height: 400px;">
    <!-- Background Image with Overlay -->
    <div class="absolute inset-0">
        <img 
            src="https://images.unsplash.com/photo-1449824913935-59a10b8d2000?w=1920&q=80" 
            alt="City hoarding background" 
            class="w-full h-full object-cover"
        >
        <!-- Dark Overlay -->
        <div class="absolute inset-0 bg-black/60"></div>
    </div>

    <div class="container mx-auto px-4 py-16 md:py-24 relative z-10">
        <div class="max-w-3xl">
            <h1 class="text-3xl md:text-4xl lg:text-5xl font-bold mb-4 leading-tight">
                "Seamless Hoarding Booking For Maximum Visibility"
            </h1>
            <p class="text-base md:text-lg mb-8 text-white/90">
                Search deals on hoardings anywhere in <span class="font-semibold">India</span>
            </p>

            @guest
                <div class="inline-block">
                    <a href="{{ route('login') }}" class="inline-flex items-center justify-center px-8 py-3 bg-teal-500 text-white rounded-md font-semibold hover:bg-teal-600 transition-colors shadow-lg">
                        Login / Signup
                    </a>
                </div>
            @else
                <div class="flex flex-col sm:flex-row gap-4">
                    <a href="{{ route('hoardings.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-teal-500 text-white rounded-md font-semibold hover:bg-teal-600 transition-colors">
                        Explore Hoardings
                    </a>
                    <a href="{{ route('dooh.index') }}" class="inline-flex items-center justify-center px-6 py-3 bg-white/10 backdrop-blur-sm border border-white/30 text-white rounded-md font-semibold hover:bg-white/20 transition-colors">
                        Browse DOOH
                    </a>
                </div>
            @endguest
        </div>
    </div>
</section>
