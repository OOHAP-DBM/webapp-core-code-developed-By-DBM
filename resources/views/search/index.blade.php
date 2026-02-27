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
        color: #000000;
        display:flex;
        padding:5px;
        border-radius:20px;
    }
    .map-marker {
        display: flex;
        align-items: center;
        gap: 6px;
    }

    /* Black location pin */
    .map-marker .pin {
        width: 26px;
        height: 34px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* OOHAPP logo circle */
    .map-marker .pill .logo {
        width: 22px;
        height: 22px;
        background: #16a34a; /* darker green */
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* Price text */
    .map-marker .pill .price {
        color: #000;
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
                <button
                    onclick="openFilterModal()"
                    class="flex items-center gap-1 underline underline-offset-4 text-sm cursor-pointer"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        viewBox="0 0 24 24"
                        fill="none"
                        stroke="currentColor"
                        stroke-width="1.8"
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        class="w-4 h-4"
                    >
                        <path d="M3 5h18" />
                        <path d="M6 12h12" />
                        <path d="M10 19h4" />
                    </svg>
                    <span>Filters</span>
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
                <div class="map-marker">
                    <div class="pin">
                        <svg width="22" height="30" viewBox="0 0 26 34" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12.7735 16.7652C13.6517 16.7652 14.4037 16.4366 15.0296 15.7794C15.6544 15.1233 15.9669 14.3342 15.9669 13.4122C15.9669 12.4901 15.6544 11.7004 15.0296 11.0432C14.4037 10.3872 13.6517 10.0591 12.7735 10.0591C11.8953 10.0591 11.1438 10.3872 10.519 11.0432C9.89306 11.7004 9.58011 12.4901 9.58011 13.4122C9.58011 14.3342 9.89306 15.1233 10.519 15.7794C11.1438 16.4366 11.8953 16.7652 12.7735 16.7652ZM12.7735 33.5304C8.48904 29.7023 5.28929 26.1464 3.17421 22.8627C1.05807 19.5801 0 16.5417 0 13.7475C0 9.55616 1.28427 6.21709 3.8528 3.73026C6.42027 1.24342 9.39383 0 12.7735 0C16.1531 0 19.1267 1.24342 21.6942 3.73026C24.2627 6.21709 25.547 9.55616 25.547 13.7475C25.547 16.5417 24.4894 19.5801 22.3743 22.8627C20.2582 26.1464 17.0579 29.7023 12.7735 33.5304Z" fill="#222222"/>
                        </svg>
                    </div>

                    <div class="price-badge">
                    <div class="logo">
                        <svg width="16" height="16" viewBox="0 0 24 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path d="M1.54039 8.76063C1.40235 9.45417 1.32996 10.1714 1.32996 10.9057C1.32996 16.9287 6.20208 21.8113 12.2121 21.8113C18.2222 21.8113 23.0943 16.9287 23.0943 10.9057C23.0943 4.88263 18.2222 0 12.2121 0C11.2271 0 10.2726 0.13116 9.36518 0.377003V5.95482C10.2029 5.46996 11.1752 5.19251 12.2121 5.19251C15.3607 5.19251 17.913 7.75037 17.913 10.9057C17.913 14.0609 15.3607 16.6188 12.2121 16.6188C9.06365 16.6188 6.51129 14.0609 6.51129 10.9057C6.51129 10.147 6.65884 9.42294 6.92674 8.76063H1.54039Z" fill="#222222"/>
                        <path d="M0 0.666389L7.85513 1.97146V7.39999L0 6.09492L0 0.666389Z" fill="#00B711"/>
                        </svg>
                    </div>

                    <div class="price font-semibold">
                        ₹${item.price}
                    </div>
                    </div>
                </div>
            `;


            const icon = L.divIcon({
                html: priceHTML,
                className: '',
                iconAnchor: [30, 15] // center-ish
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
        const currentView = "{{ $currentView }}"; // list | grid

        if (toggle.checked) {
            // Save state
            try {
                localStorage.setItem('view', 'map');
            } catch (e) {}
            // Update URL
            const url = new URL(window.location.href);
            url.searchParams.set('view', 'map');
            window.history.replaceState({}, '', url);

            if (mapView)  mapView.style.display = 'block';
            if (listView) listView.style.display = 'none';
            if (gridView) gridView.style.display = 'none';

            setTimeout(() => {
                if (!window.mapReady) initPriceMap();
            }, 300);
        } else {
            // Remove state
            try {
                localStorage.removeItem('view');
            } catch (e) {}
            // Remove ?view=map from URL
            const url = new URL(window.location.href);
            if (url.searchParams.get('view') === 'map') {
                url.searchParams.delete('view');
                window.history.replaceState({}, '', url);
            }
            if (mapView) mapView.style.display = 'none';
            if (currentView === 'grid') {
                if (gridView) gridView.style.display = 'block';
                if (listView) listView.style.display = 'none';
            } else {
                if (listView) listView.style.display = 'block';
                if (gridView) gridView.style.display = 'none';
            }
        }
    }
    // On page load, persist Map View state
    document.addEventListener('DOMContentLoaded', function () {
        const mapView   = document.getElementById('mapView');
        const listView  = document.getElementById('listView');
        const gridView  = document.getElementById('gridView');
        const toggle    = document.getElementById('mapViewToggle');
        const url = new URL(window.location.href);
        let shouldShowMap = false;
        if (url.searchParams.get('view') === 'map') {
            shouldShowMap = true;
        } else {
            try {
                if (localStorage.getItem('view') === 'map') {
                    shouldShowMap = true;
                }
            } catch (e) {}
        }
        if (shouldShowMap && toggle) {
            toggle.checked = true;
            if (mapView)  mapView.style.display = 'block';
            if (listView) listView.style.display = 'none';
            if (gridView) gridView.style.display = 'none';
            setTimeout(() => {
                if (!window.mapReady) initPriceMap();
            }, 300);
        }
    });

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