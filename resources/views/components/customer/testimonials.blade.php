<section class="py-16 bg-gray-50">
    <div class="container mx-auto px-4">

        {{-- Heading --}}
        <div class="text-center mb-10">
            <h2 class="text-3xl font-bold text-gray-900">
                What Our Customers & Vendors Say
            </h2>
            <p class="text-gray-600 mt-2">
                Trusted by advertisers and media partners across India
            </p>
        </div>

        {{-- Tabs --}}
        <div class="flex justify-center mb-8">
            <button class="px-6 py-2 bg-primary text-white rounded-l-lg text-sm font-medium">
                Customers
            </button>
            <button class="px-6 py-2 bg-white border text-gray-700 rounded-r-lg text-sm font-medium">
                Vendors
            </button>
        </div>

        {{-- Testimonials Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            {{-- Card --}}
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <div class="flex items-center mb-4">
                    <div class="text-yellow-400 text-sm">★★★★★</div>
                </div>

                <p class="text-gray-700 text-sm mb-4">
                    “OOHAPP made hoarding booking extremely simple and transparent.
                    We closed our campaign in just one day.”
                </p>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Rahul Sharma</p>
                        <p class="text-xs text-gray-500">Customer · Delhi</p>
                    </div>
                </div>
            </div>

            {{-- Duplicate card (static for now) --}}
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <div class="text-yellow-400 text-sm mb-4">★★★★★</div>

                <p class="text-gray-700 text-sm mb-4">
                    “Very smooth process. Pricing and availability were clearly
                    mentioned. Highly recommended.”
                </p>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Ankit Verma</p>
                        <p class="text-xs text-gray-500">Customer · Mumbai</p>
                    </div>
                </div>
            </div>

            {{-- Vendor --}}
            <div class="bg-white p-6 rounded-xl shadow hover:shadow-lg transition">
                <div class="text-yellow-400 text-sm mb-4">★★★★★</div>

                <p class="text-gray-700 text-sm mb-4">
                    “As a vendor, OOHAPP helped us get consistent leads
                    and manage enquiries efficiently.”
                </p>

                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gray-200"></div>
                    <div>
                        <p class="text-sm font-semibold text-gray-900">Media House Pvt Ltd</p>
                        <p class="text-xs text-gray-500">Vendor · Jaipur</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
