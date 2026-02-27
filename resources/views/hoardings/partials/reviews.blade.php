<div class="max-w-7xl mx-auto px-4 py-8 border-t border-gray-300">

    <!-- <h3 class="text-lg font-semibold mb-6">Rating & Reviews</h3> -->

    {{-- SUMMARY --}}
    <!-- <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

        <div class="md:col-span-3">
            <div class="flex items-center gap-3">
                <div class="bg-green-600 text-white font-semibold text-lg px-3 py-2 rounded">
                    {{ $hoarding->avg_rating ?? '4.5' }}/5
                </div>
                <div>
                    <p class="font-semibold">Excellent</p>
                    <p class="text-sm text-gray-500">
                        From {{ $hoarding->reviews_count ?? 24 }} Reviews
                    </p>
                </div>
            </div>
        </div>

        <div class="md:col-span-6 space-y-3">
            @foreach([5=>76,4=>12,3=>10,2=>2,1=>0] as $star=>$percent)
                <div class="flex items-center gap-3 text-sm">
                    <span class="w-6">{{ $star }} ★</span>
                    <div class="flex-1 bg-gray-200 rounded-full h-3 overflow-hidden">
                        <div class="bg-yellow-400 h-3" style="width: {{ $percent }}%"></div>
                    </div>
                    <span class="w-10 text-right text-gray-600">{{ $percent }}%</span>
                </div>
            @endforeach
        </div>

        <div class="md:col-span-3 text-right">
            <a href="#" class="text-blue-600 text-sm font-medium hover:underline">
                Write a Review
            </a>
        </div>

    </div> -->

    {{-- SINGLE REVIEW --}}
    <div class="border-gray-300">
        <h2>Business Name: {{ $hoarding->vendor->vendorProfile->company_name ?? $hoarding->vendor->company_name ?? 'N/A' }}</h2>
        <a href="{{ route('vendors.show', $hoarding->vendor->id) }}">
            <h2 class="text-blue-600 hover:underline">
                Vendor Name: {{ $hoarding->vendor->name ?? 'N/A' }}
            </h2>
        </a>
        <p class="text-xs text-gray-500 mb-4">
            {{ $hoarding->created_at->format('d M, Y') }}
        </p>

        <!-- <div class="flex gap-3">
            @for($i=0;$i<3;$i++)
                <img src="https://via.placeholder.com/120x90"
                     class="w-32 h-24 object-cover rounded border">
            @endfor
        </div> -->
    </div>

    {{-- BRAND LOGOS --}}
    @php
        $brandLogos = collect();
        if ($hoarding->hoarding_type === 'dooh' && $hoarding->doohScreen) {
            $brandLogos = $hoarding->doohScreen->brandLogos;
        } elseif ($hoarding->hoarding_type === 'ooh' && $hoarding->ooh) {
            $brandLogos = $hoarding->ooh->brandLogos;
        } else {
            $brandLogos = $hoarding->brandLogos;
        }
    @endphp
    @if($brandLogos && $brandLogos->count())
        <div class="mt-8">
            <h4 class="text-sm font-semibold text-gray-700 mb-2">Recent Booked By</h4>
            <div class="flex flex-wrap gap-4">
                @foreach($brandLogos as $logo)
                    <div class="flex flex-col items-center">
                        <img src="{{ asset('storage/' . ltrim($logo->file_path, '/')) }}" alt="{{ $logo->brand_name ?? 'Brand Logo' }}" class="w-24 h-24 object-cover border border-gray-300 rounded bg-white shadow-sm" style="background-size:cover;">
                        @if(!empty($logo->brand_name))
                            <span class="text-xs mt-1 text-gray-600">{{ $logo->brand_name }}</span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>
