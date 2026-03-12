<div class="max-w-7xl mx-auto px-4 py-8 border-t border-gray-300">

    {{-- RATING SUMMARY --}}
    @php
        $ratings = $hoarding->ratings ?? collect();
        $totalReviews = $ratings->count();
        $avgRating = $totalReviews ? round($ratings->avg('rating'),1) : 0;

        $starCounts = [
            5 => $ratings->where('rating',5)->count(),
            4 => $ratings->where('rating',4)->count(),
            3 => $ratings->where('rating',3)->count(),
            2 => $ratings->where('rating',2)->count(),
            1 => $ratings->where('rating',1)->count(),
        ];

        $starPercents = [];
        foreach($starCounts as $star=>$count){
            $starPercents[$star] = $totalReviews ? round(($count/$totalReviews)*100) : 0;
        }
    @endphp

    <div class="mb-8">
        <h3 class="text-lg font-semibold mb-4">Rating & Reviews</h3>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

            {{-- LEFT SUMMARY --}}
            <div class="md:col-span-3">
                <div class="flex items-center gap-3">
                    <div class="bg-green-600 text-white font-semibold text-lg px-3 py-2 rounded">
                        {{ $avgRating }}/5
                    </div>
                    <div>
                        <p class="font-semibold">Excellent</p>
                        <p class="text-sm text-gray-500">
                            From {{ $totalReviews }} Reviews
                        </p>
                    </div>
                </div>
            </div>

            {{-- RATING BARS --}}
            <div class="md:col-span-6 space-y-3">

                @foreach([5,4,3,2,1] as $star)
                    <div class="flex items-center gap-3 text-sm">
                        <span class="w-6">{{ $star }} ★</span>

                        <div class="flex-1 bg-gray-200 rounded-full h-3 overflow-hidden">
                            <div class="bg-yellow-400 h-3"
                                 style="width: {{ $starPercents[$star] ?? 0 }}%">
                            </div>
                        </div>

                        <span class="w-10 text-right text-gray-600">
                            {{ $starPercents[$star] ?? 0 }}%
                        </span>
                    </div>
                @endforeach

            </div>

            {{-- WRITE REVIEW --}}
            <div class="md:col-span-3 text-right">
                @auth
                    <a href="javascript:void(0)"
                        onclick="openRatingModal()"
                        class="text-blue-600 text-sm font-medium hover:underline">
                        Write a Review
                    </a>
                @else
                    <a href="{{ route('login') }}"
                       class="text-blue-600 text-sm font-medium hover:underline">
                        Write a Review
                    </a>
                @endauth
            </div>

        </div>

        <p class="text-sm text-gray-500 mt-3">
            Total {{ $totalReviews }} Reviews
        </p>

        {{-- REVIEWS LIST --}}
        @php
            $publicReviews = $hoarding->ratings()->with(['user', 'vendorReply'])->latest()->take(3)->get();
            $extraReviews = $hoarding->ratings()->with(['user', 'vendorReply'])->latest()->get()->skip(3)->values();
        @endphp

        @if($publicReviews->count())
        <div class="mt-6 space-y-5">

            {{-- First 3 reviews --}}
            @foreach($publicReviews as $rev)
            <div class="border-b border-gray-100 pb-5">
                <div class="flex items-start gap-3">
                    <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-semibold text-sm flex-shrink-0">
                        {{ strtoupper(substr($rev->user->name ?? 'C', 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex items-center gap-3">
                            <p class="text-sm font-semibold text-gray-800">{{ $rev->user->name ?? 'Customer' }}</p>
                            <div class="flex text-yellow-400 text-sm mt-0.5">
                                @for($s = 1; $s <= 5; $s++)
                                    <span>{{ $s <= $rev->rating ? '★' : '☆' }}</span>
                                @endfor
                            </div>
                            <span class="text-xs text-gray-400">{{ $rev->created_at->format('M d, Y') }}</span>
                        </div>
                        @if($rev->review)
                        <p class="text-sm text-gray-600 mt-1">{{ $rev->review }}</p>
                        @endif
                        @if($rev->vendorReply)
                        <div class="mt-3 ml-4 bg-gray-50 border border-gray-200 rounded-lg px-4 pb-1 pt-2">
                            <div class="flex items-center gap-2 mb-1">
                                <div class="w-6 h-6 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                                    <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                    </svg>
                                </div>
                                <p class="text-xs font-semibold text-green-700">Vendor Reply</p>
                                <p class="text-sm text-gray-600">{{ $rev->vendorReply->reply }}</p>
                                <span class="text-xs text-gray-400 ml-auto">{{ $rev->vendorReply->created_at->format('M d, Y') }}</span>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
            @endforeach

            {{-- Extra reviews (hidden by default) --}}
            @if($extraReviews->count())
            <div id="extraReviews" class="hidden space-y-5">
                @foreach($extraReviews as $rev)
                <div class="border-b border-gray-100 pb-5">
                    <div class="flex items-start gap-3">
                        <div class="w-9 h-9 rounded-full bg-green-100 flex items-center justify-center text-green-700 font-semibold text-sm flex-shrink-0">
                            {{ strtoupper(substr($rev->user->name ?? 'C', 0, 1)) }}
                        </div>
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <p class="text-sm font-semibold text-gray-800">{{ $rev->user->name ?? 'Customer' }}</p>
                                <div class="flex text-yellow-400 text-sm mt-0.5">
                                    @for($s = 1; $s <= 5; $s++)
                                        <span>{{ $s <= $rev->rating ? '★' : '☆' }}</span>
                                    @endfor
                                </div>
                                <span class="text-xs text-gray-400">{{ $rev->created_at->format('M d, Y') }}</span>
                            </div>
                            @if($rev->review)
                            <p class="text-sm text-gray-600 mt-1">{{ $rev->review }}</p>
                            @endif
                            @if($rev->vendorReply)
                            <div class="mt-3 ml-4 bg-gray-50 border border-gray-200 rounded-lg px-4 pb-1 pt-2">
                                <div class="flex items-center gap-2 mb-1">
                                    <div class="w-6 h-6 rounded-full bg-green-600 flex items-center justify-center flex-shrink-0">
                                        <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                        </svg>
                                    </div>
                                    <p class="text-xs font-semibold text-green-700">Vendor Reply</p>
                                    <p class="text-sm text-gray-600">{{ $rev->vendorReply->reply }}</p>
                                    <span class="text-xs text-gray-400 ml-auto">{{ $rev->vendorReply->created_at->format('M d, Y') }}</span>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            {{-- View More Button --}}
            <div class="flex justify-end">
                <button id="viewMoreBtn" onclick="showAllReviews()"
                    class="text-sm text-green-600 hover:underline font-medium cursor-pointer">
                    View all {{ $totalReviews }} reviews ↓
                </button>
            </div>
            @endif

        </div>
        @endif
    </div>


    {{-- EXISTING CODE START (UNCHANGED) --}}
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
                        <img src="{{ asset('storage/' . ltrim($logo->file_path, '/')) }}"
                             alt="{{ $logo->brand_name ?? 'Brand Logo' }}"
                             class="w-24 h-24 object-cover border border-gray-300 rounded bg-white shadow-sm"
                             style="background-size:cover;">

                        @if(!empty($logo->brand_name))
                            <span class="text-xs mt-1 text-gray-600">
                                {{ $logo->brand_name }}
                            </span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<div id="ratingModal"
    class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50 px-2 sm:px-0">

    <div class="bg-white w-full max-w-lg rounded-md shadow-lg relative overflow-hidden mx-2 sm:mx-0">

        <!-- Green Header Bar -->
        <div class="bg-[#D9F2E6] h-10 w-full"></div>

        <!-- Close -->
        <button onclick="closeRatingModal()"
                class="absolute top-2 right-3 text-gray-500 text-xl hover:text-black cursor-pointer">
            ✕
        </button>

        <div class="p-6">
            <h2 class="text-xl font-semibold mb-1">
                Your opinion matters to us!
            </h2>

            <p class="text-gray-500 text-sm mb-8">
                We will  use your opinion to improve your experience
            </p>

            <!-- Stars (outlined style) -->
            <div class="flex justify-center gap-3 mb-8">
                @for($i=1;$i<=5;$i++)
                    <span class="star cursor-pointer text-5xl leading-none"
                          data-value="{{ $i }}"
                          style="color: transparent; -webkit-text-stroke: 2px #aaaaaa;">★</span>
                @endfor
            </div>

            <!-- Review -->
            <p class="text-sm text-gray-700 mb-2">
                Let us know what we can do better to improve your experience.
            </p>

            <form method="POST" action="{{ route('ratings.store') }}">
                @csrf
                <input type="hidden" name="hoarding_id" value="{{ $hoarding->id }}">
                <input type="hidden" name="rating" id="ratingValue">
                <input type="hidden" name="review" id="reviewHidden">

                <div class="border border-gray-300 rounded">
                    <textarea id="reviewText"
                              maxlength="250"
                              rows="5"
                              class="w-full p-3 text-sm outline-none resize-none rounded-t"
                              placeholder="Write here..."></textarea>

                    <div class="text-xs text-gray-400 text-right px-3 pb-2">
                        <span id="charCount">0</span>/250
                    </div>
                </div>

                <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-3 rounded mt-4 text-base font-medium cursor-pointer">
                    Submit
                </button>

            </form>
        </div>
    </div>
</div>

<script>

function showAllReviews() {
    document.getElementById('extraReviews').classList.remove('hidden');
    document.getElementById('viewMoreBtn').style.display = 'none';
}

function openRatingModal(){
    document.getElementById('ratingModal').classList.remove('hidden');
    document.getElementById('ratingModal').classList.add('flex');
}

function closeRatingModal(){
    document.getElementById('ratingModal').classList.add('hidden');
}

let stars = document.querySelectorAll('.star');
let ratingValue = document.getElementById('ratingValue');

stars.forEach((star,index)=>{
    star.addEventListener('click',function(){
        ratingValue.value = this.dataset.value;
        stars.forEach((s,i)=>{
            s.classList.remove('text-yellow-400');
            s.classList.add('text-gray-300');
            if(i <= index){
                s.classList.remove('text-gray-300');
                s.classList.add('text-yellow-400');
            }
        });
    });
});

let review = document.getElementById('reviewText');
let counter = document.getElementById('charCount');
let reviewHidden = document.getElementById('reviewHidden');

review.addEventListener('input',function(){
    counter.innerText = this.value.length;
    reviewHidden.value = this.value;
});

document.querySelectorAll('.star').forEach((star, idx, stars) => {
    star.addEventListener('mouseover', () => {
        stars.forEach((s, i) => {
            if (i <= idx) {
                s.style.color = '#f59e0b';
                s.style.webkitTextStroke = '0px';
            } else {
                s.style.color = 'transparent';
                s.style.webkitTextStroke = '2px #aaaaaa';
            }
        });
    });
    star.addEventListener('mouseout', () => {
        const selected = document.getElementById('ratingValue').value;
        stars.forEach((s, i) => {
            if (selected && i < selected) {
                s.style.color = '#f59e0b';
                s.style.webkitTextStroke = '0px';
            } else {
                s.style.color = 'transparent';
                s.style.webkitTextStroke = '2px #aaaaaa';
            }
        });
    });
    star.addEventListener('click', () => {
        document.getElementById('ratingValue').value = idx + 1;
        stars.forEach((s, i) => {
            if (i <= idx) {
                s.style.color = '#f59e0b';
                s.style.webkitTextStroke = '0px';
            } else {
                s.style.color = 'transparent';
                s.style.webkitTextStroke = '2px #aaaaaa';
            }
        });
    });
});

</script>