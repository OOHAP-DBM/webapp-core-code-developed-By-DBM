@extends('layouts.customer')

@section('title', 'Smart Search - OOHAPP')

@push('styles')
<style>
    .search-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 24px;
        box-shadow: 0 4px 20px rgba(102, 126, 234, 0.3);
    }
    
    .location-detect-btn {
        background: rgba(255, 255, 255, 0.2);
        border: 2px solid rgba(255, 255, 255, 0.3);
        color: white;
        padding: 10px 20px;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.3s;
    }
    
    .location-detect-btn:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: translateY(-2px);
    }
    
    .view-toggle {
        display: flex;
        gap: 8px;
    }
    
    .view-toggle button {
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        background: white;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    
    .view-toggle button.active {
        background: #667eea;
        color: white;
        border-color: #667eea;
    }
    
    .results-info {
        font-size: 14px;
        color: #64748b;
        background: white;
        padding: 16px;
        border-radius: 12px;
        margin-bottom: 16px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
    }
    
    .results-info .relevance-badge {
        display: inline-block;
        background: #10b981;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
        margin-left: 8px;
    }
    
    .filter-sidebar {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        position: sticky;
        top: 20px;
    }
    
    .filter-group {
        margin-bottom: 24px;
        padding-bottom: 20px;
        border-bottom: 1px solid #f1f5f9;
    }
    
    .filter-group:last-child {
        border-bottom: none;
    }
    
    .filter-title {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .filter-title i {
        color: #667eea;
    }
    
    .filter-option {
        display: flex;
        align-items: center;
        padding: 8px 0;
    }
    
    .filter-option input[type="checkbox"],
    .filter-option input[type="radio"] {
        width: 18px;
        height: 18px;
        margin-right: 10px;
        cursor: pointer;
    }
    
    .filter-option label {
        cursor: pointer;
        margin-bottom: 0;
    }
    
    .price-range-input {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .price-range-input input {
        width: 100%;
        padding: 10px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 14px;
    }
    
    .price-range-input input:focus {
        outline: none;
        border-color: #667eea;
    }
    
    .slider-container {
        margin-top: 12px;
    }
    
    .radius-slider {
        width: 100%;
        height: 8px;
        border-radius: 4px;
        background: #e2e8f0;
        outline: none;
        opacity: 0.9;
        transition: opacity 0.2s;
    }
    
    .radius-slider:hover {
        opacity: 1;
    }
    
    .radius-value {
        display: inline-block;
        background: #667eea;
        color: white;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 13px;
        font-weight: 600;
        margin-top: 8px;
    }
    
    .rating-filter {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
    }
    
    .rating-option {
        padding: 8px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 20px;
        cursor: pointer;
        transition: all 0.2s;
        font-size: 14px;
    }
    
    .rating-option:hover {
        border-color: #667eea;
        background: #f8fafc;
    }
    
    .rating-option input[type="radio"] {
        display: none;
    }
    
    .rating-option input[type="radio"]:checked + label {
        background: #667eea;
        color: white;
    }
    
    .map-view {
        height: 600px;
        background: #f1f5f9;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .score-badges {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 8px;
    }
    
    .score-badge {
        padding: 4px 10px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        background: #f1f5f9;
        color: #64748b;
    }
    
    .score-badge.high {
        background: #d1fae5;
        color: #065f46;
    }
    
    .score-badge.medium {
        background: #fef3c7;
        color: #92400e;
    }
    
    .score-badge.low {
        background: #fee2e2;
        color: #991b1b;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Search Header with Location Detection (PROMPT 54) -->
    <div class="search-header">
        <div class="row align-items-center mb-3">
            <div class="col-md-8">
                <h2 class="mb-2">Smart Hoarding Search</h2>
                <p class="mb-0 opacity-90">Find the perfect advertising space with intelligent ranking</p>
            </div>
            <div class="col-md-4 text-end">
                <button type="button" class="location-detect-btn" id="detectLocationBtn">
                    <i class="bi bi-crosshair"></i> Use My Location
                </button>
            </div>
        </div>
        
        <form action="{{ route('customer.search') }}" method="GET" id="searchForm">
            <input type="hidden" name="latitude" id="latitude" value="{{ request('latitude') }}">
            <input type="hidden" name="longitude" id="longitude" value="{{ request('longitude') }}">
            
            <div class="input-group input-group-lg">
                <span class="input-group-text bg-white">
                    <i class="bi bi-search"></i>
                </span>
                <input 
                    type="text" 
                    name="search" 
                    class="form-control" 
                    placeholder="Search by location, city, or hoarding name..."
                    value="{{ request('search') }}"
                >
                <button type="submit" class="btn btn-light px-4 fw-bold">
                    Search
                </button>
            </div>
            
            @if(request('latitude') && request('longitude'))
            <div class="mt-2 text-white-50">
                <small>
                    <i class="bi bi-geo-alt-fill"></i> 
                    Searching near your location ({{ number_format(request('latitude'), 4) }}, {{ number_format(request('longitude'), 4) }})
                </small>
            </div>
            @endif
        </form>
    </div>

    <div class="row">
        <!-- Smart Filters Sidebar (PROMPT 54) -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-sliders"></i> Filters</h5>
                    <a href="{{ route('customer.search') }}" class="btn btn-sm btn-link">Clear All</a>
                </div>

                <form action="{{ route('customer.search') }}" method="GET" id="filterForm">
                    <input type="hidden" name="search" value="{{ request('search') }}">
                    <input type="hidden" name="latitude" value="{{ request('latitude') }}">
                    <input type="hidden" name="longitude" value="{{ request('longitude') }}">
                    
                    <!-- Radius Filter (PROMPT 54) -->
                    @if(request('latitude') && request('longitude'))
                    <div class="filter-group">
                        <div class="filter-title">
                            <i class="bi bi-bullseye"></i>
                            Search Radius
                        </div>
                        <div class="slider-container">
                            <input 
                                type="range" 
                                name="radius" 
                                id="radiusSlider" 
                                class="radius-slider" 
                                min="1" 
                                max="100" 
                                value="{{ request('radius', 10) }}"
                                step="1"
                            >
                            <div class="radius-value">
                                <span id="radiusValue">{{ request('radius', 10) }}</span> km
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Hoarding Type Filter (PROMPT 54) -->
                    <div class="filter-group">
                        <div class="filter-title">
                            <i class="bi bi-badge-ad"></i>
                            Hoarding Type
                        </div>
                        @foreach($availableTypes ?? [] as $type)
                        <div class="filter-option">
                            <input 
                                type="checkbox" 
                                name="types[]" 
                                value="{{ $type }}" 
                                id="type-{{ $type }}" 
                                {{ in_array($type, (array) request('types', [])) ? 'checked' : '' }}
                            >
                            <label for="type-{{ $type }}">{{ ucfirst(str_replace('_', ' ', $type)) }}</label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Price Range Filter (PROMPT 54) -->
                    <div class="filter-group">
                        <div class="filter-title">
                            <i class="bi bi-currency-rupee"></i>
                            Price Range (₹/week)
                        </div>
                        @if(isset($priceRange))
                        <small class="text-muted d-block mb-2">
                            Available: ₹{{ number_format($priceRange['min']) }} - ₹{{ number_format($priceRange['max']) }}
                        </small>
                        @endif
                        <div class="price-range-input">
                            <input 
                                type="number" 
                                name="min_price" 
                                placeholder="Min" 
                                value="{{ request('min_price') }}"
                                min="0"
                            >
                            <span>-</span>
                            <input 
                                type="number" 
                                name="max_price" 
                                placeholder="Max" 
                                value="{{ request('max_price') }}"
                                min="0"
                            >
                        </div>
                    </div>

                    <!-- Vendor Rating Filter (PROMPT 54) -->
                    <div class="filter-group">
                        <div class="filter-title">
                            <i class="bi bi-star-fill"></i>
                            Minimum Vendor Rating
                        </div>
                        <div class="rating-filter">
                            @foreach([0, 3, 3.5, 4, 4.5] as $rating)
                            <div class="rating-option {{ request('min_rating') == $rating ? 'active' : '' }}">
                                <input 
                                    type="radio" 
                                    name="min_rating" 
                                    value="{{ $rating }}" 
                                    id="rating-{{ $rating }}"
                                    {{ request('min_rating') == $rating ? 'checked' : '' }}
                                >
                                <label for="rating-{{ $rating }}">
                                    @if($rating == 0)
                                        Any
                                    @else
                                        {{ $rating }}+ ⭐
                                    @endif
                                </label>
                            </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Availability Filter (PROMPT 54) -->
                    <div class="filter-group">
                        <div class="filter-title">
                            <i class="bi bi-calendar-check"></i>
                            Availability
                        </div>
                        <div class="filter-option">
                            <input 
                                type="radio" 
                                name="availability" 
                                value="" 
                                id="avail-all" 
                                {{ !request('availability') ? 'checked' : '' }}
                            >
                            <label for="avail-all">All</label>
                        </div>
                        <div class="filter-option">
                            <input 
                                type="radio" 
                                name="availability" 
                                value="available" 
                                id="avail-available" 
                                {{ request('availability') === 'available' ? 'checked' : '' }}
                            >
                            <label for="avail-available">
                                <span class="badge bg-success">Available Now</span>
                            </label>
                        </div>
                        <div class="filter-option">
                            <input 
                                type="radio" 
                                name="availability" 
                                value="available_soon" 
                                id="avail-soon" 
                                {{ request('availability') === 'available_soon' ? 'checked' : '' }}
                            >
                            <label for="avail-soon">
                                <span class="badge bg-warning">Available Soon</span>
                            </label>
                        </div>
                        <div class="filter-option">
                            <input 
                                type="radio" 
                                name="availability" 
                                value="booked" 
                                id="avail-booked" 
                                {{ request('availability') === 'booked' ? 'checked' : '' }}
                            >
                            <label for="avail-booked">
                                <span class="badge bg-danger">Currently Booked</span>
                            </label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-funnel"></i> Apply Filters
                    </button>
                    <button type="button" class="btn btn-outline-secondary w-100" onclick="window.location.href='{{ route('customer.search') }}'">
                        <i class="bi bi-x-circle"></i> Clear Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Results Area -->
        <div class="col-lg-9">
            <!-- Results Info with Relevance Scoring (PROMPT 54) -->
            <div class="results-info">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <strong class="text-dark">{{ $hoardings->total() ?? 0 }}</strong> hoardings found
                        @if(request('search'))
                            for "<strong class="text-primary">{{ request('search') }}</strong>"
                        @endif
                        @if(isset($searchResults) && !empty($searchResults['filters_applied']))
                            <span class="relevance-badge">
                                <i class="bi bi-lightning-charge-fill"></i> Smart Ranked
                            </span>
                        @endif
                    </div>
                    
                    <div class="view-toggle">
                        <button class="active" id="gridViewBtn" title="Grid View">
                            <i class="bi bi-grid-3x3-gap"></i>
                        </button>
                        <button id="listViewBtn" title="List View">
                            <i class="bi bi-list-ul"></i>
                        </button>
                    </div>
                </div>
                
                @if(isset($searchResults['filters_applied']))
                <div class="mt-2 pt-2 border-top">
                    <small class="text-muted">
                        <i class="bi bi-info-circle"></i> 
                        Results sorted by: <strong>Relevance</strong> → <strong>Price Match</strong> → <strong>Visibility</strong>
                    </small>
                </div>
                @endif
            </div>

            <!-- Grid View -->
            <div id="gridView">
                <div class="row g-4">
                    @forelse($hoardings ?? [] as $hoarding)
                    <div class="col-12 col-md-6 col-xl-4">
                        <x-hoarding-card 
                            :hoarding="$hoarding" 
                            :showActions="true" 
                            :isWishlisted="auth()->user()->hasWishlisted($hoarding->id)"
                        />
                        
                        {{-- Show relevance scores if available (PROMPT 54) --}}
                        @if(isset($hoarding->relevance_score))
                        <div class="score-badges mt-2">
                            <span class="score-badge {{ $hoarding->relevance_score >= 80 ? 'high' : ($hoarding->relevance_score >= 60 ? 'medium' : 'low') }}">
                                Relevance: {{ $hoarding->relevance_score }}%
                            </span>
                            @if(isset($hoarding->distance_km))
                            <span class="score-badge">
                                <i class="bi bi-geo-alt"></i> {{ number_format($hoarding->distance_km, 1) }} km
                            </span>
                            @endif
                            @if(isset($hoarding->vendor_avg_rating))
                            <span class="score-badge">
                                <i class="bi bi-star-fill"></i> {{ number_format($hoarding->vendor_avg_rating, 1) }}
                            </span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-search" style="font-size: 64px; color: #cbd5e1;"></i>
                            <h4 class="mt-3">No hoardings found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms</p>
                            <a href="{{ route('customer.search') }}" class="btn btn-outline-primary">Clear Filters</a>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(isset($hoardings) && $hoardings->hasPages())
                <div class="mt-4">
                    {{ $hoardings->links() }}
                </div>
                @endif
            </div>

            <!-- List View (Hidden by default) -->
            <div id="listView" style="display: none;">
                @foreach($hoardings ?? [] as $hoarding)
                <div class="card mb-3 shadow-sm">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                @if($hoarding->primary_image)
                                <img src="{{ asset('storage/' . $hoarding->primary_image) }}" class="img-fluid rounded" alt="{{ $hoarding->title }}">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="bi bi-image" style="font-size: 32px; color: #cbd5e1;"></i>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h5 class="fw-bold">{{ $hoarding->title }}</h5>
                                <p class="text-muted mb-2">
                                    <i class="bi bi-geo-alt-fill"></i> {{ $hoarding->address }}
                                    @if(isset($hoarding->distance_km))
                                        <span class="badge bg-info ms-2">{{ number_format($hoarding->distance_km, 1) }} km away</span>
                                    @endif
                                </p>
                                <p class="mb-2">{{ Str::limit($hoarding->description ?? '', 150) }}</p>
                                <div class="d-flex gap-2 flex-wrap">
                                    <span class="badge bg-light text-dark">{{ ucfirst($hoarding->type) }}</span>
                                    @if(isset($hoarding->vendor_avg_rating))
                                    <span class="badge bg-warning text-dark">
                                        <i class="bi bi-star-fill"></i> {{ number_format($hoarding->vendor_avg_rating, 1) }}
                                    </span>
                                    @endif
                                    @if(isset($hoarding->relevance_score))
                                    <span class="badge bg-success">
                                        {{ $hoarding->relevance_score }}% Match
                                    </span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <h4 class="text-primary fw-bold">₹{{ number_format($hoarding->weekly_price ?? ($hoarding->monthly_price / 4)) }}</h4>
                                <small class="text-muted">/week</small>
                                <div class="mt-3">
                                    <a href="{{ route('hoardings.show', $hoarding->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">
                                        <i class="bi bi-eye"></i> View Details
                                    </a>
                                    <a href="{{ route('customer.enquiries.create', ['hoarding_id' => $hoarding->id]) }}" class="btn btn-primary btn-sm w-100">
                                        <i class="bi bi-envelope"></i> Enquire Now
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle functionality
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    
    gridViewBtn.addEventListener('click', function() {
        switchView('grid');
    });
    
    listViewBtn.addEventListener('click', function() {
        switchView('list');
    });
    
    function switchView(view) {
        [gridViewBtn, listViewBtn].forEach(btn => btn.classList.remove('active'));
        gridView.style.display = 'none';
        listView.style.display = 'none';
        
        if (view === 'grid') {
            gridViewBtn.classList.add('active');
            gridView.style.display = 'block';
        } else if (view === 'list') {
            listViewBtn.classList.add('active');
            listView.style.display = 'block';
        }
    }
    
    // Location detection (PROMPT 54)
    const detectBtn = document.getElementById('detectLocationBtn');
    const latInput = document.getElementById('latitude');
    const lonInput = document.getElementById('longitude');
    
    if (detectBtn) {
        detectBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                detectBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Detecting...';
                detectBtn.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        latInput.value = position.coords.latitude;
                        lonInput.value = position.coords.longitude;
                        
                        // Auto-submit form
                        document.getElementById('searchForm').submit();
                    },
                    function(error) {
                        alert('Could not detect location. Please check your browser permissions.');
                        detectBtn.innerHTML = '<i class="bi bi-crosshair"></i> Use My Location';
                        detectBtn.disabled = false;
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser');
            }
        });
    }
    
    // Radius slider update (PROMPT 54)
    const radiusSlider = document.getElementById('radiusSlider');
    const radiusValue = document.getElementById('radiusValue');
    
    if (radiusSlider && radiusValue) {
        radiusSlider.addEventListener('input', function() {
            radiusValue.textContent = this.value;
        });
    }
    
    // Auto-submit on filter change (optional)
    const autoSubmitFilters = document.querySelectorAll('input[type="radio"][name="availability"], input[type="radio"][name="min_rating"]');
    autoSubmitFilters.forEach(filter => {
        filter.addEventListener('change', function() {
            // Uncomment to enable auto-submit
            // document.getElementById('filterForm').submit();
        });
    });
});
</script>
@endpush

                        </select>
                    </div>

                    <!-- Type Filter -->
                    <div class="filter-group">
                        <div class="filter-title">Hoarding Type</div>
                        @foreach(['billboard' => 'Billboard', 'hoarding' => 'Hoarding', 'unipole' => 'Unipole', 'digital' => 'Digital Screen'] as $value => $label)
                        <div class="filter-option">
                            <input type="checkbox" name="type[]" value="{{ $value }}" id="type-{{ $value }}" {{ in_array($value, (array) request('type', [])) ? 'checked' : '' }}>
                            <label for="type-{{ $value }}">{{ $label }}</label>
                        </div>
                        @endforeach
                    </div>

                    <!-- Illumination -->
                    <div class="filter-group">
                        <div class="filter-title">Illumination</div>
                        <div class="filter-option">
                            <input type="radio" name="illumination" value="" id="illum-all" {{ !request('illumination') ? 'checked' : '' }}>
                            <label for="illum-all">All</label>
                        </div>
                        <div class="filter-option">
                            <input type="radio" name="illumination" value="lit" id="illum-lit" {{ request('illumination') === 'lit' ? 'checked' : '' }}>
                            <label for="illum-lit">Lit</label>
                        </div>
                        <div class="filter-option">
                            <input type="radio" name="illumination" value="non-lit" id="illum-nonlit" {{ request('illumination') === 'non-lit' ? 'checked' : '' }}>
                            <label for="illum-nonlit">Non-Lit</label>
                        </div>
                    </div>

                    <!-- Price Range -->
                    <div class="filter-group">
                        <div class="filter-title">Price Range (₹/month)</div>
                        <div class="price-range-input">
                            <input type="number" name="min_price" placeholder="Min" value="{{ request('min_price') }}">
                            <span>-</span>
                            <input type="number" name="max_price" placeholder="Max" value="{{ request('max_price') }}">
                        </div>
                    </div>

                    <!-- Size -->
                    <div class="filter-group">
                        <div class="filter-title">Minimum Size (ft)</div>
                        <input type="number" name="min_width" class="form-control mb-2" placeholder="Width" value="{{ request('min_width') }}">
                        <input type="number" name="min_height" class="form-control" placeholder="Height" value="{{ request('min_height') }}">
                    </div>

                    <!-- Sort -->
                    <div class="filter-group">
                        <div class="filter-title">Sort By</div>
                        <select name="sort" class="form-select">
                            <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>Latest</option>
                            <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>Price: Low to High</option>
                            <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>Price: High to Low</option>
                            <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>Most Popular</option>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary w-100">Apply Filters</button>
                </form>
            </div>
        </div>

        <!-- Results Area -->
        <div class="col-lg-9">
            <!-- Results Info -->
            <div class="results-info mb-3">
                <strong>{{ $hoardings->total() ?? 0 }}</strong> hoardings found
                @if(request('search'))
                    for "<strong>{{ request('search') }}</strong>"
                @endif
            </div>

            <!-- Grid View -->
            <div id="gridView">
                <div class="row g-4">
                    @forelse($hoardings ?? [] as $hoarding)
                    <div class="col-12 col-md-6 col-xl-4">
                        <x-hoarding-card 
                            :hoarding="$hoarding" 
                            :showActions="true" 
                            :isWishlisted="auth()->user()->hasWishlisted($hoarding->id)"
                        />
                    </div>
                    @empty
                    <div class="col-12">
                        <div class="text-center py-5">
                            <i class="bi bi-search" style="font-size: 48px; color: #cbd5e1;"></i>
                            <h4 class="mt-3">No hoardings found</h4>
                            <p class="text-muted">Try adjusting your filters or search terms</p>
                            <a href="{{ route('search') }}" class="btn btn-outline-primary">Clear Filters</a>
                        </div>
                    </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if(isset($hoardings) && $hoardings->hasPages())
                <div class="mt-4">
                    {{ $hoardings->links() }}
                </div>
                @endif
            </div>

            <!-- List View (Hidden by default) -->
            <div id="listView" style="display: none;">
                @foreach($hoardings ?? [] as $hoarding)
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3">
                                @if($hoarding->primary_image)
                                <img src="{{ asset('storage/' . $hoarding->primary_image) }}" class="img-fluid rounded" alt="{{ $hoarding->title }}">
                                @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height: 150px;">
                                    <i class="bi bi-image"></i>
                                </div>
                                @endif
                            </div>
                            <div class="col-md-6">
                                <h5>{{ $hoarding->title }}</h5>
                                <p class="text-muted"><i class="bi bi-geo-alt"></i> {{ $hoarding->city }}, {{ $hoarding->state }}</p>
                                <p>{{ Str::limit($hoarding->description ?? '', 150) }}</p>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-light text-dark">{{ $hoarding->width }}x{{ $hoarding->height }} ft</span>
                                    <span class="badge bg-light text-dark">{{ ucfirst($hoarding->illumination_type) }}</span>
                                </div>
                            </div>
                            <div class="col-md-3 text-end">
                                <h4 class="text-primary">₹{{ number_format($hoarding->price_per_month ?? 0) }}</h4>
                                <small class="text-muted">/month</small>
                                <div class="mt-3">
                                    <a href="{{ route('hoardings.show', $hoarding->id) }}" class="btn btn-outline-primary btn-sm w-100 mb-2">View Details</a>
                                    <a href="{{ route('customer.enquiries.create', ['hoarding_id' => $hoarding->id]) }}" class="btn btn-primary btn-sm w-100">Enquire</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Map View (Hidden by default) -->
            <div id="mapView" class="map-view" style="display: none;">
                <div class="text-center">
                    <i class="bi bi-map" style="font-size: 64px; color: #cbd5e1;"></i>
                    <p class="mt-3 text-muted">Map integration coming soon</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const gridViewBtn = document.getElementById('gridViewBtn');
    const listViewBtn = document.getElementById('listViewBtn');
    const mapViewBtn = document.getElementById('mapViewBtn');
    
    const gridView = document.getElementById('gridView');
    const listView = document.getElementById('listView');
    const mapView = document.getElementById('mapView');
    
    gridViewBtn.addEventListener('click', function() {
        switchView('grid');
    });
    
    listViewBtn.addEventListener('click', function() {
        switchView('list');
    });
    
    mapViewBtn.addEventListener('click', function() {
        switchView('map');
    });
    
    function switchView(view) {
        // Update buttons
        [gridViewBtn, listViewBtn, mapViewBtn].forEach(btn => btn.classList.remove('active'));
        
        // Hide all views
        gridView.style.display = 'none';
        listView.style.display = 'none';
        mapView.style.display = 'none';
        
        // Show selected view
        if (view === 'grid') {
            gridViewBtn.classList.add('active');
            gridView.style.display = 'block';
        } else if (view === 'list') {
            listViewBtn.classList.add('active');
            listView.style.display = 'block';
        } else if (view === 'map') {
            mapViewBtn.classList.add('active');
            mapView.style.display = 'block';
        }
    }
});
</script>
@endpush
