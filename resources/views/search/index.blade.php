<style>
    @media (max-width: 1024px) {
        .max-w-6xl {
            padding-left: 1rem;
            padding-right: 1rem;
        }
        
        .max-w-\[1460px\] {
            padding-left: 1rem;
            padding-right: 1rem;
        }
    }

    @media (max-width: 768px) {
        /* Top filter bar */
        .bg-white.border-b > div {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
            padding: 0.75rem 1rem;
        }
        
        .bg-white.border-b > div > div:first-child {
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .bg-white.border-b > div > div:first-child > div:first-child {
            flex-wrap: wrap;
            gap: 0.75rem;
        }
        
        .h-4.w-px.bg-gray-300 {
            display: none;
        }
        
        /* Listing cards */
        .bg-\[\#f0f0f0\] > div {
            flex-direction: column;
            gap: 1rem;
        }
        
        .bg-\[\#f0f0f0\] > div > div:first-child {
            width: 100% !important;
        }
        
        .bg-\[\#f0f0f0\] > div > div:last-child {
            min-width: 100% !important;
            position: static !important;
            margin-top: 1rem;
        }
        
        .bg-\[\#f0f0f0\] > div > div:last-child > div {
            position: static !important;
            width: 100%;
            justify-content: space-between;
        }
        
        /* Action buttons */
        .absolute.bottom-0.right-0 {
            position: static !important;
            width: 100%;
            justify-content: space-between;
        }
        
        .absolute.bottom-0.right-0 > div {
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        
        /* Thumbnail images */
        .flex.gap-2.mt-2 {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        
        .flex.gap-2.mt-2 img {
            flex-shrink: 0;
        }
        
        /* Map view responsive */
        .map-view-container > div:first-child {
            width: 100% !important;
            height: 300px !important;
        }
        
        .map-view-container > div:last-child {
            width: 100% !important;
        }
    }

    @media (max-width: 640px) {
        /* Adjust text sizes for very small screens */
        .bg-white.border-b > div > div:first-child span {
            font-size: 0.875rem;
        }
        
        /* Adjust card padding */
        .bg-\[\#f0f0f0\] {
            padding: 1rem !important;
        }
        
        /* Make buttons full width on very small screens */
        .absolute.bottom-0.right-0 {
            flex-direction: column;
            gap: 0.75rem;
        }
        
        .absolute.bottom-0.right-0 > div {
            width: 100%;
        }
        
        .absolute.bottom-0.right-0 button {
            width: 100%;
        }
    }
    
    /* Hide elements initially */
    #mapView {
        display: none;
    }
    /* ===============================
    FIX LEAFLET Z-INDEX ISSUE
    ================================ */

    /* All leaflet layers lower than modal */
    .leaflet-pane,
    .leaflet-top,
    .leaflet-bottom,
    .leaflet-control,
    .leaflet-control-container {
        z-index: 0 !important;
    }

    /* Map container itself */
    #priceMap {
        position: relative;
        z-index: 0 !important;
    }

    /* Filter modal MUST stay on top */
    #filterModal {
        z-index: 9999 !important;
    }

</style>
@extends('layouts.app')
@section('title', 'Home - Seamless Hoarding Booking')
@section('content')
    @include('components.customer.navbar')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    {{-- ================= TOP FILTER BAR ================= --}}
    <div class="bg-white border-b border-[#d0d4db] sticky top-16 z-30">
        <div class="max-w-6xl mx-auto px-6 py-3 flex items-center justify-between text-sm text-gray-700">
            {{-- LEFT CONTROLS --}}
            <div class="flex items-center gap-6">
                <div class="flex items-center gap-6">
                    <!-- Before Tax -->
                    <div class="flex items-center gap-2">
                        <span>Before Tax</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" checked class="sr-only peer">
                            <div
                                class="w-9 h-5 rounded-full bg-gray-300
                                    peer-checked:bg-green-400
                                    after:content-['']
                                    after:absolute after:top-[2px] after:left-[2px]
                                    after:h-4 after:w-4 after:bg-white after:rounded-full
                                    after:transition-all
                                    peer-checked:after:translate-x-4">
                            </div>
                        </label>
                    </div>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <!-- Weekly -->
                    <div class="flex items-center gap-2">
                        <span>Weekly</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" class="sr-only peer">
                            <div
                                class="w-9 h-5 rounded-full bg-gray-300
                                    peer-checked:bg-green-400
                                    after:content-['']
                                    after:absolute after:top-[2px] after:left-[2px]
                                    after:h-4 after:w-4 after:bg-white after:rounded-full
                                    after:transition-all
                                    peer-checked:after:translate-x-4">
                            </div>
                        </label>
                    </div>
                    <div class="h-4 w-px bg-gray-300"></div>
                    <!-- Map View -->
                    <div class="flex items-center gap-2">
                        <span>Map View</span>
                        <label class="relative inline-flex items-center cursor-pointer">
                            <input type="checkbox" id="mapViewToggle" class="sr-only peer" onchange="toggleMapView()">
                            <div
                                class="w-9 h-5 rounded-full bg-gray-300
                                    peer-checked:bg-green-400
                                    after:content-['']
                                    after:absolute after:top-[2px] after:left-[2px]
                                    after:h-4 after:w-4 after:bg-white after:rounded-full
                                    after:transition-all
                                    peer-checked:after:translate-x-4">
                            </div>
                        </label>
                    </div>
                </div>
                <div class="h-4 w-px bg-gray-300"></div>
                {{-- Grid / List --}}
                <div class="flex items-center gap-2">
                    <span class="font-medium text-gray-900">Grid view</span>
                    <span class="text-gray-400">|</span>
                    <span class="text-gray-400">List view</span>
                </div>
                <div class="h-4 w-px bg-gray-300"></div>
                {{-- Filters --}}
                <button class="underline underline-offset-4" onclick="openFilterModal()">
                    Filters
                </button>
            </div>
            {{-- SORT --}}
            <div class="flex items-center gap-2">
                <span>Sort by</span>
                <select class="border border-gray-300 rounded-md px-2 py-1 text-sm">
                    <option>Ratings</option>
                    <option>Price Low to High</option>
                    <option>Price High to Low</option>
                    <option>Recommended</option>
                </select>
            </div>
        </div>
    </div>
    @php
        $dummyImage = asset('images/image.png');
        $dummyThumbs = [$dummyImage,$dummyImage,$dummyImage,$dummyImage];
    @endphp
    
    {{-- ================= MAP VIEW ================= --}}
    <div id="mapView" class="bg-white  min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">
            <h2 class="text-sm text-gray-700 mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>
            
            <div class="map-view-container flex gap-6">
                 {{-- RIGHT: LISTINGS --}}
                <div class="w-1/2 overflow-y-auto" style="max-height: 700px;">
                    @forelse($results as $item)
                        <div class="rounded-lg p-4 mb-4 shadow bg-[#F8F8F8]">
                            <div class="flex gap-3">
                                {{-- THUMBNAIL --}}
                                <div class="w-50 flex-shrink-0">
                                    <img src="{{$dummyImage }}" 
                                         class="w-full h-20 object-cover rounded">
                                         <div class="flex gap-2 mt-2">
                                            @foreach($dummyThumbs as $thumb)
                                                <img src="{{ $thumb }}"
                                                    class="w-[44px] h-[48px] object-cover rounded ">
                                            @endforeach
                                        </div>
                                </div>
                                
                                
                                {{-- DETAILS --}}
                                <div class="flex-1">
                                    <h4 class="font-semibold text-sm">{{ $item->title ?? 'Unipole Hazaratganj Lucknow' }}</h4>
                                    <p class="text-xs text-gray-500 mt-1">{{ $item->address ?? 'Vipul khand gomti nagar' }}</p>
                                    
                                    <div class="flex items-center gap-2 mt-2">
                                        <span class="text-xs">OOH | 300*250Sq.ft</span>
                                        <span class="flex items-center gap-1 text-xs">
                                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#ffc83d" class="w-3 h-3">
                                                <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                                            </svg>
                                            <span>{{ $item->rating ?? 4.5 }}</span>
                                        </span>
                                    </div>
                                    
                                    <div class="mt-2">
                                        <span class="font-bold">₹{{ number_format($item->price ?? 10999) }}</span>
                                        <span class="text-xs text-gray-500">/Month</span>
                                                        {{-- OLD PRICE + DISCOUNT --}}
                                        <div class="flex items-center gap-2 mt-1">
                                            <!-- Old price -->
                                            <span class="text-xs text-red-400 line-through">
                                                ₹{{ number_format(($item->price ?? 10999) + 4200) }}
                                            </span>

                                            <!-- Discount badge -->
                                            <span class="text-[11px] text-green-600 font-medium">
                                                ₹3000 OFF!
                                            </span>
                                            <span class="text-xs text-gray-500 mt-1">Taxes excluded</span>

                                        </div>
                                        <p class="text-xs text-gray-500">Hoarding Available From: {{ $item->available_from ?? 'December 25' }}</p>
                                        <p class="text-xs text-blue-600">{{ $item->packages_count ?? 3 }} Packages Available</p>
                                    </div>
                                    
                                    <button class="mt-2 bg-green-600 text-white text-xs px-3 py-1.5 rounded w-full">
                                        View Details
                                    </button>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="bg-white p-4 text-center text-gray-500 rounded border">
                            No hoardings found.
                        </div>
                    @endforelse
                </div>
                {{-- LEFT: MAP --}}
                <div class="w-2/3">
                    <div class="bg-gray-300 rounded-xl h-[700px] flex items-center justify-center">
                        <div class="rounded-xl overflow-hidden h-[700px] w-full">
                            <div id="priceMap" class="h-full w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- ================= LIST VIEW ================= --}}
    <div id="listView" class="bg-gray-100 min-h-screen">
        <div class="max-w-[1460px] mx-auto px-6 py-6">

            <h2 class="text-sm text-gray-700 mb-4">
                {{ $results->total() }} Hoardings in {{ request('location') ?? 'India' }}
            </h2>

            @forelse($results as $item)
                <div class="bg-[#f0f0f0] rounded-xl p-5 mb-5 flex flex-col">
                    <div class="flex gap-6 items-stretch flex-1">

                        {{-- IMAGE --}}
                        <div class="w-[305px] flex-shrink-0">
                            <div class="relative group">
                                <img src="{{ $dummyImage }}"
                                    class="w-full h-[190px] object-cover rounded-lg">
                                <!-- RECOMMENDED TAG -->
                                <span class="absolute top-2 left-2 bg-red-500 text-white text-[10px] px-2 py-0.5 rounded z-10">
                                    RECOMMENDED
                                </span>
                                <!-- SAVE (BOOKMARK) ICON -->
                                <button
                                    class="absolute top-2 right-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow">
                                    <!-- bookmark svg -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="w-4 h-4 text-gray-700">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M5 5v14l7-5 7 5V5a2 2 0 00-2-2H7a2 2 0 00-2 2z"/>
                                    </svg>
                                </button>
                                <!-- VIEW (EYE) ICON -->
                                <button
                                    class="absolute bottom-2 left-2 z-10
                                        bg-white/90 hover:bg-white
                                        border border-gray-200
                                        rounded-full p-1.5 shadow">
                                    <!-- eye svg -->
                                    <svg xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="none"
                                        stroke="currentColor"
                                        stroke-width="2"
                                        class="w-4 h-4 text-gray-700">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.458 12C3.732 7.943 7.523 5 12 5
                                                c4.477 0 8.268 2.943 9.542 7
                                                -1.274 4.057-5.065 7-9.542 7
                                                -4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </button>
                            </div>
                            <div class="flex gap-2 mt-2">
                                @foreach($dummyThumbs as $thumb)
                                    <img src="{{ $thumb }}"
                                        class="w-[70px] h-[48px] object-cover rounded ">
                                @endforeach
                            </div>
                        </div>

                        {{-- DETAILS --}}
                        <div class="flex-1">
                            <h3 class="text-lg font-semibold">{{ $item->title ?? 'Unipole Hazaratganj Lucknow' }}</h3>
                            <p class="text-sm text-gray-500">{{ $item->address ?? 'Vipul khand gomti nagar' }}</p>

                            <div class="flex items-center gap-3 text-sm mt-1">
                                <span>OOH | 300*250Sq.ft</span>
                                <span class="flex items-center gap-1 text-sm text-gray-600">
                                    <svg
                                        xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 24 24"
                                        fill="#ffc83d"
                                        class="w-4 h-4">
                                        <polygon points="12 2 15 8.5 22 9.3 17 14.1 18.2 21 12 17.8 5.8 21 7 14.1 2 9.3 9 8.5 12 2" />
                                    </svg>

                                    <span>{{ $item->rating ?? 4.5 }}</span>
                                    <span class="text-xs text-gray-400">
                                        ({{ $item->reviews_count ?? 16 }} Ratings)
                                    </span>
                                </span>
                            </div>

                            <div class="flex gap-2 mt-2">
                                <span class="bg-[#ffb854] text-xs px-2 py-0.5 rounded">Limited time offer</span>
                                <span class="bg-blue-100 text-blue-700 text-xs px-2 py-0.5 rounded">OOH</span>
                            </div>

                            <div class="mt-3">
                                <span class="text-xl font-bold">₹{{ number_format($item->price ?? 10999) }}</span>
                                <span class="text-sm text-gray-500">/Month</span>
                            </div>
                                 {{-- OLD PRICE + DISCOUNT --}}
                            <div class="flex items-center gap-2 mt-1">
                                <!-- Old price -->
                                <span class="text-xs text-red-400 line-through">
                                    ₹{{ number_format(($item->price ?? 10999) + 4200) }}
                                </span>

                                <!-- Discount badge -->
                                <span class="text-[11px] text-green-600 font-medium">
                                    ₹3000 OFF!
                                </span>
                            </div>


                            <p class="text-xs text-gray-500 mt-1">Taxes excluded</p>
                            <p class="text-xs text-gray-500">Hoarding Available From: {{ $item->available_from ?? 'December 25' }}</p>
                            <p class="text-xs text-blue-600">{{ $item->packages_count ?? 3 }} Packages Available</p>
                        </div>

                        {{-- ACTIONS --}}
                        <div class="min-w-[200px] relative">
                            <!-- FORCE BOTTOM -->
                            <div class="absolute bottom-0 right-0 flex gap-6 items-start">
                                <!-- Short List + Enquire -->
                                <div class="flex flex-col">
                                    <button class="border border-[#c7c7c7] px-4 py-1.5 rounded text-sm whitespace-nowrap min-w-[96px]">Short List</button>
                                    <a class="block text-xs text-yellow-600 mt-1 italic underline whitespace-nowrap text-left">Enquire Now</a>
                                </div>
                                <!-- Book Now -->
                                <div>
                                    <button class="bg-green-600 text-white px-5 py-2 rounded-md text-sm whitespace-nowrap min-w-[110px]">
                                        Book Now
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
             @empty
                <div class="bg-white p-8 text-center text-gray-500 rounded border">
                    No hoardings found.
                </div>
            @endforelse

            {{ $results->links() }}
        </div>
    </div>
    {{-- ================= FILTER MODAL ================= --}}
    <div id="filterModal"
        class="fixed inset-0 z-50 hidden">

        <!-- Overlay -->
        <div class="absolute inset-0 bg-black/40"
            onclick="closeFilterModal()"></div>

        <!-- Modal Box -->
        <div class="
            relative
            bg-white
            w-full max-w-3xl
            mx-auto
            mt-20
            rounded-xl
            shadow-xl
            max-h-[80vh]
            overflow-hidden
        ">

            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b">
                <h2 class="text-lg font-semibold">Filters</h2>
                <button onclick="closeFilterModal()" class="text-xl">&times;</button>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto max-h-[65vh]">

                <!-- TYPES -->
                <h3 class="font-medium mb-3">Types of Hoarding</h3>
                <div class="flex gap-3 mb-6">
                    <button class="px-4 py-2 rounded-md bg-black text-white">Any Type</button>
                    <button class="px-4 py-2 rounded-md border">DOOH</button>
                    <button class="px-4 py-2 rounded-md border">OOH</button>
                </div>

                <!-- CATEGORIES -->
                <h3 class="font-medium mb-3">Categories</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm mb-6">
                    @php
                        $categories = [
                            'Flag Sign','Traffic Both','Standee','LED Screens',
                            'Digital Standee','Metro Panels','Taxi Branding','Unipole',
                            'Canopy','Sandwich Board','Glow Sign Board','Gantry',
                            'BQS','Flag Pole','Slides Scrolling','Flagpole',
                            'Bus Shelter','Ballon Display','Metro Pillars'
                        ];
                    @endphp

                    @foreach($categories as $cat)
                        <label class="flex items-center gap-2">
                            <input type="checkbox">
                            <span>{{ $cat }}</span>
                        </label>
                    @endforeach
                </div>

                <!-- DURATION -->
                <h3 class="font-medium mb-2">Campaign Duration</h3>
                <p class="text-xs text-gray-500 mb-3">
                    Select campaign duration. like how long you want to book the hoarding.
                </p>

                <div class="flex gap-3">
                    <button class="px-6 py-2 rounded-md bg-black text-white">Weekly</button>
                    <button class="px-6 py-2 rounded-md border">Monthly</button>
                </div>

            </div>

            <!-- Footer -->
            <div class="flex items-center justify-between px-6 py-4 border-t">
                <button class="text-sm text-gray-500 underline">
                    Clear all
                </button>
                <button
                    class="bg-green-600 text-white px-6 py-2 rounded-md">
                    Apply
                </button>
            </div>

        </div>
    </div>


    <script>
        window.mapData = [
        @foreach($results as $item)
            @if(
                is_numeric($item->lat) &&
                is_numeric($item->lng) &&
                $item->lat >= -90 && $item->lat <= 100 &&
                $item->lng >= -180 && $item->lng <= 180
            )
            {
                lat: {{ (float)$item->lat }},
                lng: {{ (float)$item->lng }},
                price: {{ (int)($item->price ?? 0) }}
            },
            @endif
        @endforeach
        ];
    </script>
    <script>
        /* =====================================================
        GLOBALS
        ===================================================== */
        let map = null;
        let mapReady = false;

        /* =====================================================
        INIT MAP
        ===================================================== */
        function initPriceMap() {

            if (mapReady) {
                setTimeout(() => map.invalidateSize(), 300);
                return;
            }

            if (!Array.isArray(window.mapData) || window.mapData.length === 0) {
                console.warn('No valid map data');
                return;
            }

            // Create map
            map = L.map('priceMap', {
                zoomControl: true,
                attributionControl: false
            });

            // Tiles (FREE)
            L.tileLayer(
            'https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',
            {
                maxZoom: 19
            }
            ).addTo(map);


            const bounds = [];

            // Add markers
            window.mapData.forEach(item => {

                if (
                    typeof item.lat !== 'number' ||
                    typeof item.lng !== 'number'
                ) return;

                const priceHTML = `
                    <div style="
                        background:#1dbf73;
                        color:#fff;
                        padding:4px 10px;
                        border-radius:999px;
                        font-size:12px;
                        font-weight:600;
                        box-shadow:0 2px 6px rgba(0,0,0,.35);
                        white-space:nowrap;
                    ">
                        ${item.price > 0 
                            ? `₹${Math.round(item.price / 1000)}k`
                            : 'Price on Request'
                        }
                    </div>
                    `;


                const icon = L.divIcon({
                    html: priceHTML,
                    className: '',
                    iconSize: [60, 26]
                });

                L.marker([item.lat, item.lng], { icon }).addTo(map);
                bounds.push([item.lat, item.lng]);
            });

            // Fit to actual hoarding locations ONLY
            if (bounds.length > 0) {
                map.fitBounds(bounds, { padding: [80, 80] });
            } else {
                // fallback (Lucknow)
                map.setView([26.8467, 80.9462], 12);
            }

            setTimeout(() => map.invalidateSize(), 300);
            mapReady = true;
        }

        /* =====================================================
        TOGGLE MAP VIEW
        ===================================================== */
        function toggleMapView() {
            const mapView  = document.getElementById('mapView');
            const listView = document.getElementById('listView');
            const toggle   = document.getElementById('mapViewToggle');

            if (toggle.checked) {
                mapView.style.display = 'block';
                listView.style.display = 'none';
                setTimeout(initPriceMap, 300);
            } else {
                mapView.style.display = 'none';
                listView.style.display = 'block';
            }
        }
    </script>
    <script>
        function openFilterModal() {
            document.getElementById('filterModal').classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        }
        function closeFilterModal() {
            document.getElementById('filterModal').classList.add('hidden');
            document.body.style.overflow = '';
        }
    </script>

    @include('components.customer.footer')
@endsection