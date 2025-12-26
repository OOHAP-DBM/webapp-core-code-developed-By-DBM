@extends('layouts.app')
@section('title', $hoarding->title)

@section('content')
@include('components.customer.navbar')

<style>
/* ===== GLOBAL ===== */
body { background:#fff; }

/* ===== WRAPPER ===== */
.hoarding-wrapper{
    max-width:1500px;
    margin:auto;
    padding:24px 16px;
}

/* ===== GALLERY ===== */
.main-image{
    height:420px;
    object-fit:cover;
    border-radius:12px;
}
.thumb-image{
    height:200px;
    object-fit:cover;
    border-radius:10px;
    cursor:pointer;
    border:2px solid transparent;
}
.thumb-image.active{
    border-color:#20c997;
}

/* ===== INFO ===== */
.offer-badge{
    background:#ff4d4f;
    color:#fff;
    font-size:12px;
    padding:6px 12px;
    border-radius:6px;
    font-weight:600;
}
.rating-badge{
    background:#f1f3f5;
    padding:4px 10px;
    border-radius:6px;
    font-size:13px;
}

/* ===== PRICING ===== */
.price-box{
    border:1px solid #e9ecef;
    border-radius:14px;
    padding:16px;
}
.offer-row{
    border:1px solid #dee2e6;
    border-radius:10px;
    padding:14px;
    margin-bottom:12px;
    cursor:pointer;
}
.offer-row.active{
    border-color:#20c997;
    background:#f3fffb;
}
.save{
    background:#e6fcf5;
    color:#0ca678;
    font-size:11px;
    padding:4px 10px;
    border-radius:20px;
    font-weight:600;
}
.old-price{
    text-decoration:line-through;
    color:#adb5bd;
    font-size:13px;
}
.book-btn{
    background:#20c997;
    color:#fff;
    border:none;
    padding:12px;
    border-radius:8px;
    font-weight:600;
}
.book-btn:hover{ background:#12b886; }

/* ===== MOBILE ===== */
@media(max-width:768px){
    .main-image{ height:260px; }
    .thumb-image{ height:140px; }
}
</style>

<div class="hoarding-wrapper">

    {{-- ================= GALLERY ================= --}}
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <img id="mainImage"
                 src="{{ asset($hoarding->media[0]->path ?? 'assets/images/placeholder.jpg') }}"
                 class="img-fluid w-100 main-image">
        </div>

        <div class="col-lg-6">
            <div class="row g-3">
                @foreach($hoarding->media->take(4) as $media)
                    <div class="col-6">
                        <img src="{{ asset($media->path) }}"
                             class="img-fluid w-100 thumb-image">
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ================= CONTENT ================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">

        {{-- LEFT --}}
        <div class="lg:col-span-8">
                  {{-- ================= HOARDING BASIC INFO ================= --}}
                    <div class="space-y-4">

                        {{-- OFFER TIMER --}}
                        <div class="inline-flex items-center gap-2 bg-red-500 text-white text-xs font-semibold px-3 py-1 rounded-md">
                            🔥 Hurry, offers ends in
                            <span class="bg-red-600 px-2 py-0.5 rounded text-white">
                                07 : 44 : 33
                            </span>
                        </div>

                        {{-- TITLE --}}
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ $hoarding->title }}
                        </h2>

                        {{-- LOCATION --}}
                        <p class="text-sm text-gray-500 flex items-center gap-1">
                            📍 {{ $hoarding->address }}
                        </p>

                        {{-- META --}}
                        <p class="text-sm text-gray-600">
                            OOH | 200×300 Sq.ft | Unipole
                        </p>

                        {{-- RATING --}}
                        <div class="flex items-center gap-2 text-sm">
                            <span class="px-2 py-0.5 bg-yellow-100 text-yellow-700 rounded font-medium">
                                ⭐ 4.5
                            </span>
                            <span class="text-gray-500">/ 335 Reviews</span>
                        </div>

                        {{-- CANCELLATION --}}
                        <div class="text-xs text-red-500 flex items-center gap-1 cursor-pointer">
                            Cancellation Policy
                            <span class="text-gray-400">ⓘ</span>
                        </div>

                        <p class="text-xs text-gray-400">
                            By Proceeding, you agree to
                            <a href="#" class="text-green-600 underline">OOHAPP Policies</a>
                        </p>

                        {{-- CHAT BUTTON --}}
                        <button class="border border-gray-300 text-sm px-4 py-2 rounded-md text-gray-600 hover:bg-gray-50">
                            Chat with Hoarding Owner
                        </button>

                    </div>

                    <hr class="my-6 border-gray-300">

                    {{-- ================= HOARDING DETAILS ================= --}}
                    <div class="space-y-5">

                        <h3 class="text-sm font-semibold text-gray-900 flex justify-between">
                            Hoarding Details
                            <span class="text-gray-400">—</span>
                        </h3>

                        <div class="grid grid-cols-2 md:grid-cols-5 gap-y-4 text-sm">

                            <div>
                                <p class="text-gray-400">Type</p>
                                <p class="font-medium">OOH</p>
                            </div>

                            <div>
                                <p class="text-gray-400">Category</p>
                                <p class="font-medium">Unipole</p>
                            </div>

                            <div>
                                <p class="text-gray-400">Size</p>
                                <p class="font-medium">250×300 Sqft</p>
                            </div>

                            <div>
                                <p class="text-gray-400">Validity</p>
                                <p class="font-medium">
                                    Nov 18, 2025 to Jan 01, 2025
                                </p>
                            </div>

                            <div>
                                <p class="text-gray-400">Lightening</p>
                                <p class="font-medium">Backlight</p>
                            </div>

                        </div>

                        <p class="text-sm text-gray-600">
                            Approved From Nagar Nigam
                            <span class="font-medium ml-1">Yes</span>
                        </p>

                    </div>

                    <hr class="my-6 border-gray-300">

                    {{-- ================= GAZEFLOW ================= --}}
                    <div>
                        <h3 class="text-sm font-semibold mb-3">Gazeflow</h3>

                        <div class="flex gap-12 text-sm">
                            <div>
                                <p class="text-gray-400">Expected Eyeball</p>
                                <p class="font-medium">1K</p>
                            </div>

                            <div>
                                <p class="text-gray-400">Expected Footfall</p>
                                <p class="font-medium">1K</p>
                            </div>
                        </div>
                    </div>

                    <hr class="my-6 border-gray-300">

                    {{-- ================= AUDIENCE TYPE ================= --}}
                    <div>
                        <h3 class="text-sm font-semibold mb-3">Audience Type</h3>
                        <div class="flex flex-wrap gap-3 text-sm text-gray-700">
                            @php
                                $audiences = [
                                    'Students','Foodies','Political Activists','Culture Seekers',
                                    'Environmentalists','Luxury Consumers','Average Class',
                                    'Fitness Freaks','Travel'
                                ];
                            @endphp

                            @foreach($audiences as $audience)
                                <span class="flex items-center gap-1">
                                  <svg width="15" height="10" viewBox="0 0 15 10" fill="none" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M0.707031 4.70703L5.20703 9.20703L13.707 0.707031" stroke="#1E1B18" stroke-linecap="square"/>
                                    </svg>
                                    {{ $audience }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <hr class="my-6 border-gray-300">

                    {{-- ================= RECENTLY BOOKED ================= --}}
                    <div>
                        <h3 class="text-sm font-semibold mb-3">Recently Booked By</h3>

                        <div class="flex gap-6">
                            @for($i=0; $i<5; $i++)
                                <div class="text-center">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/7/7f/Audi_logo_detail.svg"
                                        class="h-8 mx-auto mb-1">
                                    <p class="text-xs text-gray-500">AUDI</p>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <hr class="my-6 border-gray-300">

                    {{-- ================= LOCATION ================= --}}
                    <div class="space-y-3 pb-5">
                        <h3 class="text-sm font-semibold">Location</h3>

                        <div class="text-sm">
                            <p class="font-medium">Near By Landmark</p>
                            <ul class="list-decimal list-inside text-gray-600">
                                <li>Kamta Chauraha</li>
                                <li>Hazratganj Chauraha</li>
                            </ul>
                        </div>

                        <div class="text-sm">
                            <p class="font-medium">Google Map Address</p>
                            <p class="text-gray-600">
                                Opposite Ram Dharam Kanta B43 Sector 7
                            </p>
                        </div>

                        <div class="flex gap-6 text-sm text-gray-600">
                            <p>Latitude: {{ $hoarding->lat }}</p>
                            <p>Longitude: {{ $hoarding->lng }}</p>
                        </div>
                    </div>


                    <iframe
                        src="https://www.google.com/maps?q={{ $hoarding->lat }},{{ $hoarding->lng }}&z=15&output=embed"
                        width="100%" height="280"
                        style="border-radius:12px;border:0;">
                    </iframe>

                    <h3 class="pt-5">Hoarding View For Visitors</h3>
                    <h4 class="py-3">One Way View</h4>

                    {{-- ================= HOARDING ATTRIBUTES ================= --}}
                    <div class="max-w-7xl mx-auto px-4 py-6 border-t border-gray-300">

                        <h3 class="text-base font-semibold mb-4">Hoarding Attributes</h3>

                        {{-- Visible From --}}
                        <div class="mb-5">
                            <p class="text-sm font-medium mb-2">Visible from</p>
                            <div class="flex flex-wrap gap-2">
                                @php
                                    $visibleFrom = ['Metro Ride', 'From Flyover', 'Roof Top'];
                                @endphp

                                @foreach($visibleFrom as $item)
                                    <span class="px-3 py-1 text-sm border border-gray-300 rounded text-gray-700 bg-white">
                                        {{ $item }}
                                    </span>
                                @endforeach
                            </div>
                        </div>

                        {{-- Located At --}}
                        <div class="mb-5">
                            <p class="text-sm font-medium mb-1">Located At</p>
                            <p class="text-sm text-gray-700">Highway Hoarding</p>
                            <p class="text-sm text-gray-500">NH-27</p>
                        </div>

                        {{-- Hoarding Visibility --}}
                        <div>
                            <p class="text-sm font-medium mb-1 flex items-center gap-1">
                                Hoarding Visibility
                                <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-8-4a1 1 0 100 2 1 1 0 000-2zm1 3H9v5h2V9z"/>
                                </svg>
                            </p>

                            <p class="text-sm text-gray-700 mb-1">One Way Visibility</p>
                            <p class="text-sm text-gray-500">
                                To: Central Bank of India &nbsp;/&nbsp; From: India Bank
                            </p>
                        </div>

                    </div>

                    {{-- ================= RATING & REVIEWS ================= --}}
                    <div class="max-w-7xl mx-auto px-4 py-8 border-t border-gray-300">

                        <h3 class="text-lg font-semibold mb-6">Rating & Reviews</h3>

                        <div class="grid grid-cols-1 md:grid-cols-12 gap-6">

                            {{-- LEFT : AVG RATING --}}
                            <div class="md:col-span-3">
                                <div class="flex items-center gap-3">
                                    <div class="bg-green-600 text-white font-semibold text-lg px-3 py-2 rounded">
                                        4.5/5
                                    </div>
                                    <div>
                                        <p class="font-semibold">Excellent</p>
                                        <p class="text-sm text-gray-500">From 24 Reviews</p>
                                    </div>
                                </div>
                            </div>

                            {{-- MIDDLE : RATING BARS --}}
                            <div class="md:col-span-6 space-y-3">
                                @php
                                    $ratings = [
                                        ['star'=>5,'percent'=>76],
                                        ['star'=>4,'percent'=>12],
                                        ['star'=>3,'percent'=>10],
                                        ['star'=>2,'percent'=>2],
                                        ['star'=>1,'percent'=>0],
                                    ];
                                @endphp

                                @foreach($ratings as $rate)
                                    <div class="flex items-center gap-3 text-sm">
                                        <span class="w-6">{{ $rate['star'] }} ★</span>
                                        <div class="flex-1 bg-gray-200 rounded-full h-3 overflow-hidden">
                                            <div class="bg-yellow-400 h-3"
                                                style="width: {{ $rate['percent'] }}%"></div>
                                        </div>
                                        <span class="w-10 text-right text-gray-600">
                                            {{ $rate['percent'] }}%
                                        </span>
                                    </div>
                                @endforeach
                            </div>

                            {{-- RIGHT : WRITE REVIEW --}}
                            <div class="md:col-span-3 text-right">
                                <p class="text-sm text-gray-500 mb-2">Total 24 Reviews</p>
                                <a href="#" class="text-blue-600 text-sm font-medium hover:underline">
                                    Write a Review
                                </a>
                            </div>

                    </div>
                    
                        {{-- ================= SINGLE REVIEW ================= --}}
                    <div class="mt-8 border-t pt-6 border-gray-300">

                        <div class="flex items-center gap-2 mb-2">
                            {{-- Stars --}}
                            @for($i=1;$i<=5;$i++)
                                <svg class="w-5 h-5 {{ $i<=4 ? 'text-yellow-400' : 'text-gray-300' }}"
                                    fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.286 3.965a1 1 0 00.95.69h4.173c.969 0 1.371 1.24.588 1.81l-3.377 2.455a1 1 0 00-.364 1.118l1.287 3.966c.3.921-.755 1.688-1.54 1.118l-3.378-2.455a1 1 0 00-1.175 0l-3.378 2.455c-.784.57-1.838-.197-1.539-1.118l1.287-3.966a1 1 0 00-.364-1.118L2.049 9.392c-.783-.57-.38-1.81.588-1.81h4.173a1 1 0 00.95-.69l1.286-3.965z"/>
                                </svg>
                            @endfor
                        </div>

                        <p class="font-medium">Business Name</p>
                        <p class="text-xs text-gray-500 mb-4">22 Oct, 2025</p>

                        {{-- REVIEW IMAGES --}}
                        <div class="flex flex-wrap gap-3">
                            @for($i=1;$i<=3;$i++)
                                <img
                                    src="https://via.placeholder.com/120x90"
                                    class="w-full sm:w-32 h-24 object-cover rounded border"
                                >
                            @endfor
                        </div>


                    </div>

                    </div>

        </div>

        {{-- RIGHT --}}
        <div class="lg:col-span-4">
           {{-- ================= PRICE / OFFER SECTION ================= --}}
                @php
                    $basePrice = 102000;
                    $offers = [
                        [
                            'id' => 1,
                            'label' => 'For a Month',
                            'months' => 1,
                            'price' => 50000,
                            'save' => 52000,
                            'active' => true,
                        ],
                        [
                            'id' => 2,
                            'label' => 'For 2 Months',
                            'months' => 2,
                            'price' => 50000,
                            'save' => 25000,
                            'active' => false,
                        ],
                        [
                            'id' => 3,
                            'label' => 'For 3 Months',
                            'months' => 3,
                            'price' => 50000,
                            'save' => 63000,
                            'active' => false,
                        ],
                        [
                            'id' => 4,
                            'label' => 'For 6 Months',
                            'months' => 6,
                            'price' => 50000,
                            'save' => 58000,
                            'active' => false,
                        ],
                    ];
                @endphp

                <div class="w-full max-w-sm">

                {{-- ACTIVE / DEFAULT OFFER --}}
                @foreach($offers as $offer)
                    @if($offer['active'])
                        <div class="border border-green-200 bg-green-50 rounded-xl p-4 mb-5 offer-card active"
                            data-price="{{ $offer['price'] }}">
                            <div class="flex justify-between items-start">
                                <div class="flex gap-3">
                                  <span class="radio-dot w-4 h-4 mt-1 rounded-full bg-green-600"></span>
                                    <div>
                                        <p class="font-medium text-sm">{{ $offer['label'] }}</p>
                                        <span class="inline-block mt-1 px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                            SAVE ₹{{ number_format($offer['save']) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-red-400 line-through">
                                        ₹{{ number_format($basePrice) }}
                                    </p>
                                    <p class="text-lg font-semibold">
                                        ₹{{ number_format($offer['price']) }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                Designing + Printing + Mounting Included
                            </p>
                        </div>
                    @endif
                @endforeach

                {{-- AVAILABLE OFFERS --}}
                <p class="text-sm font-semibold mb-3">Available Offers</p>

                @foreach($offers as $offer)
                    @if(!$offer['active'])
                        <div class="border border-gray-200 rounded-xl p-4 mb-4 cursor-pointer offer-card" data-price="{{ $offer['price'] }}">
                            <div class="flex justify-between items-start">
                                <div class="flex gap-3">
                                  <span class="radio-dot w-4 h-4 mt-1 rounded-full border border-gray-300"></span>
                                    <div>
                                        <p class="font-medium text-sm">{{ $offer['label'] }}</p>
                                        <span class="inline-block mt-1 px-3 py-1 text-xs font-semibold bg-green-100 text-green-700 rounded-full">
                                            SAVE ₹{{ number_format($offer['save']) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="text-right">
                                    <p class="text-xs text-red-400 line-through">
                                        ₹{{ number_format($basePrice) }}
                                    </p>
                                    <p class="text-lg font-semibold">
                                        ₹{{ number_format($offer['price']) }}
                                    </p>
                                </div>
                            </div>

                            <p class="text-xs text-gray-500 mt-2">
                                Designing + Printing + Mounting Included
                            </p>
                        </div>
                    @endif
                @endforeach

                {{-- FINAL PRICE --}}
                <div class="bg-gray-50 rounded-xl p-4 mt-4">
                    <p class="text-lg font-semibold">
                        ₹<span id="finalPrice">50,000</span>/Month
                    </p>
                    <p class="text-xs text-gray-500 mb-4">
                        Designing + Printing + Mounting Included
                    </p>

                    <button class="w-full bg-black text-white py-3 rounded-md text-sm font-semibold">
                        Book Now
                    </button>

                    <button class="w-full mt-3 bg-green-500 text-white py-3 rounded-md text-sm font-semibold">
                        Sort List
                    </button>

                    <p class="text-center text-xs text-orange-500 mt-3 cursor-pointer">
                        Enquire Now
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@include('components.customer.footer')
{{-- ================= SCRIPT ================= --}}
@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const cards = document.querySelectorAll('.offer-card');
        const finalPrice = document.getElementById('finalPrice');

        cards.forEach(card => {
            card.addEventListener('click', function () {

                // RESET ALL
                cards.forEach(c => {
                    c.classList.remove('bg-green-50','border-green-200');
                    c.classList.add('border-gray-200');

                    const dot = c.querySelector('.radio-dot');
                    if(dot){
                        dot.classList.remove('bg-green-600');
                        dot.classList.add('border','border-gray-300');
                    }
                });

                // ACTIVATE CURRENT
                this.classList.remove('border-gray-200');
                this.classList.add('border-green-200','bg-green-50');

                const activeDot = this.querySelector('.radio-dot');
                if(activeDot){
                    activeDot.classList.remove('border','border-gray-300');
                    activeDot.classList.add('bg-green-600');
                }

                finalPrice.innerText = Number(this.dataset.price).toLocaleString();
            });
        });

    });
</script>
@endpush
@endsection
