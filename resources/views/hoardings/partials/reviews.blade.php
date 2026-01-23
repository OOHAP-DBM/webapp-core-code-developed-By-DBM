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
        <h2>Business Name: {{ $hoarding->vendor->company_name ?? 'N/A' }}</h2>
        <h2>Vendor Name: {{ $hoarding->vendor->name ?? 'N/A' }}</h2>
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

</div>
