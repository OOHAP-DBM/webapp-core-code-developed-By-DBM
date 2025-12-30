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
                <button class="underline underline-offset-4" onclick="openFilterModal()" style="cursor:pointer;">
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
    
    @include('search.Map_View')
    
    @include('search.List_View')

    @include('search.filter_modal')


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