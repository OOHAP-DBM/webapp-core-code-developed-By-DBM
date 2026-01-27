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
        .flex.gap-2.mt-2 {
            flex-wrap: nowrap;
            overflow-x: auto;
            padding-bottom: 0.5rem;
        }
        .flex.gap-2.mt-2 img {
            flex-shrink: 0;
        }       
        .map-view-container > div:first-child {
            width: 100% !important;
            height: 300px !important;
        }       
        .map-view-container > div:last-child {
            width: 100% !important;
        }
    }
    @media (max-width: 640px) {
        .bg-white.border-b > div > div:first-child span {
            font-size: 0.875rem;
        }  
        .bg-\[\#f0f0f0\] {
            padding: 1rem !important;
        }       
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
    #mapView {
        display: none;
    }
    .leaflet-pane,
    .leaflet-top,
    .leaflet-bottom,
    .leaflet-control,
    .leaflet-control-container {
        z-index: 0 !important;
    }
    #priceMap {
        position: relative;
        z-index: 0 !important;
    }
    .price-badge {
        background: #1dbf73;
        color: #fff;
        padding: 4px 10px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        box-shadow: 0 2px 6px rgba(0,0,0,.35);
        white-space: nowrap;
        display: inline-block;
        width: auto;
    }
</style>
@extends('layouts.app')
@section('title', 'Home - Seamless Hoarding Booking')
@section('content')
@php
    $currentView = request('view', 'grid');
@endphp
@include('components.customer.navbar')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    {{-- ================= TOP FILTER BAR ================= --}}
    <div class="bg-white border-b border-[#d0d4db] sticky top-[140px] md:top-[64px] z-40">
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
                            <input
                                type="checkbox"
                                id="weeklyToggle"
                                class="sr-only peer"
                                {{ request('duration') === 'weekly' ? 'checked' : '' }}
                                onchange="toggleWeekly(this)"
                            >
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
                    <div class="flex items-center gap-2 hidden sm:flex hidden md:flex">
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
                <div class="flex items-center gap-2 hidden sm:flex md:flex">
                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'list', 'page' => null]) }}"
                        class="{{ $currentView === 'list'
                            ? 'font-medium text-gray-900'
                            : 'text-gray-400' }}"
                    >
                        List view
                    </a>

                    <span class="text-gray-400">|</span>

                    <a
                        href="{{ request()->fullUrlWithQuery(['view' => 'grid', 'page' => null]) }}"
                        class="{{ $currentView === 'grid'
                            ? 'font-medium text-gray-900'
                            : 'text-gray-400' }}"
                    >
                        Grid view
                    </a>
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
                <select
                    onchange="applySort(this.value)"
                    class="border border-gray-300 rounded-md px-2 py-1 text-sm"
                >
                    <option value="">Select</option>
                    <option value="rating" {{ request('sort')=='rating' ? 'selected' : '' }}>
                        Ratings
                    </option>
                    <option value="price_asc" {{ request('sort')=='price_asc' ? 'selected' : '' }}>
                        Price Low to High
                    </option>
                    <option value="price_desc" {{ request('sort')=='price_desc' ? 'selected' : '' }}>
                        Price High to Low
                    </option>
                    <option value="recommended" {{ request('sort')=='recommended' ? 'selected' : '' }}>
                        Recommended
                    </option>
                </select>
            </div>
        </div>
    </div>

@if($currentView === 'grid')
    @include('search.Grid_View')
@else
    @include('search.List_View')
@endif
@include('search.Map_View')
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
                price: {{ 
                    (int)(
                        ($item->price ?? 0) > 0 
                            ? $item->price 
                            : (
                                ($item->monthly_price ?? 0) > 0 
                                    ? $item->monthly_price 
                                    : ($item->base_monthly_price ?? 0)
                              )
                    )
                }}
            },
            @endif
        @endforeach
    ];
    let map = null;
    let mapReady = false;  
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
                <div class="price-badge">
                    ₹${item.price}
                </div>
            `;

            const icon = L.divIcon({
                html: priceHTML,
                className: '',    
                iconAnchor: [0, 0] 
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
    function toggleMapView() {
        const mapView   = document.getElementById('mapView');
        const listView  = document.getElementById('listView');
        const gridView  = document.getElementById('gridView');
        const toggle    = document.getElementById('mapViewToggle');

        if (toggle.checked) {
            // 🔒 Map ON → sirf map dikhe
            if (mapView)  mapView.style.display = 'block';
            if (listView) listView.style.display = 'none';
            if (gridView) gridView.style.display = 'none';

            setTimeout(initPriceMap, 300);
        } else {
            // 🔓 Map OFF → URL ke hisaab se view dikhe
            if (mapView) mapView.style.display = 'none';

            const currentView = "{{ $currentView }}"; // list | grid

            if (currentView === 'grid') {
                if (gridView) gridView.style.display = 'block';
                if (listView) listView.style.display = 'none';
            } else {
                if (listView) listView.style.display = 'block';
                if (gridView) gridView.style.display = 'none';
            }
        }
    }
    function openFilterModal() {
        document.getElementById('filterModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }
    function closeFilterModal() {
        document.getElementById('filterModal').classList.add('hidden');
        document.body.style.overflow = '';
    }
    function applySort(sort) {
        const url = new URL(window.location.href);

        if (sort) {
            url.searchParams.set('sort', sort);
        } else {
            url.searchParams.delete('sort');
        }

        window.location.href = url.toString();
    }
    function toggleWeekly(el) {
        const url = new URL(window.location.href);

        if (el.checked) {
            url.searchParams.set('duration', 'weekly');
        } else {
            url.searchParams.delete('duration');
        }

        // reset pagination when toggling
        url.searchParams.delete('page');

        window.location.href = url.toString();
    }
</script>
@endsection