@extends('layouts.customer')

@section('title', 'Search Hoardings - OOHAPP')

@push('styles')
<style>
    .search-header {
        background: white;
        border-radius: 16px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
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
    }
    
    .filter-sidebar {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .filter-group {
        margin-bottom: 24px;
    }
    
    .filter-title {
        font-size: 15px;
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 12px;
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
    }
    
    .price-range-input {
        display: flex;
        gap: 12px;
        align-items: center;
    }
    
    .price-range-input input {
        width: 100%;
        padding: 8px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
    }
    
    .map-view {
        height: 600px;
        background: #f1f5f9;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Search Header -->
    <div class="search-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <form action="{{ route('search') }}" method="GET">
                    <div class="input-group input-group-lg">
                        <span class="input-group-text bg-white">
                            <i class="bi bi-search"></i>
                        </span>
                        <input 
                            type="text" 
                            name="search" 
                            class="form-control" 
                            placeholder="Search by city, location, or hoarding type..."
                            value="{{ request('search') }}"
                        >
                        <button type="submit" class="btn btn-primary px-4">Search</button>
                    </div>
                </form>
            </div>
            <div class="col-md-4 text-end mt-3 mt-md-0">
                <div class="view-toggle">
                    <button class="active" id="gridViewBtn">
                        <i class="bi bi-grid-3x3-gap"></i> Grid
                    </button>
                    <button id="listViewBtn">
                        <i class="bi bi-list-ul"></i> List
                    </button>
                    <button id="mapViewBtn">
                        <i class="bi bi-map"></i> Map
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Filters Sidebar -->
        <div class="col-lg-3 mb-4">
            <div class="filter-sidebar">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Filters</h5>
                    <a href="{{ route('search') }}" class="btn btn-sm btn-link">Clear All</a>
                </div>

                <form action="{{ route('search') }}" method="GET" id="filterForm">
                    <!-- City Filter -->
                    <div class="filter-group">
                        <div class="filter-title">City</div>
                        <select name="city" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Cities</option>
                            @foreach($cities ?? [] as $city)
                            <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                            @endforeach
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
