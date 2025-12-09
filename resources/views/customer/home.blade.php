@extends('layouts.customer')

@section('title', 'Home - OOHAPP')

@push('styles')
<style>
    .hero-section {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 60px 0;
        color: white;
        border-radius: 20px;
        margin-bottom: 40px;
    }
    
    .search-box-main {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
        max-width: 800px;
        margin: 0 auto;
    }
    
    .location-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 16px;
        background: rgba(255, 255, 255, 0.2);
        border-radius: 20px;
        font-size: 14px;
        margin-bottom: 16px;
    }
    
    .stats-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.08);
        text-align: center;
        transition: transform 0.3s;
    }
    
    .stats-card:hover {
        transform: translateY(-5px);
    }
    
    .stats-card .number {
        font-size: 36px;
        font-weight: 700;
        color: #667eea;
        margin-bottom: 8px;
    }
    
    .stats-card .label {
        font-size: 14px;
        color: #64748b;
    }
    
    .category-chip {
        display: inline-flex;
        align-items: center;
        padding: 12px 20px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-weight: 500;
        color: #334155;
        text-decoration: none;
        transition: all 0.3s;
    }
    
    .category-chip:hover {
        border-color: #667eea;
        background: #f8fafc;
        color: #667eea;
    }
    
    .section-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .section-title {
        font-size: 24px;
        font-weight: 700;
        color: #1e293b;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="text-center">
                <!-- Geolocation Badge -->
                @if(isset($userLocation))
                <div class="location-badge">
                    <i class="bi bi-geo-alt-fill"></i>
                    <span>{{ $userLocation['city'] ?? 'Your Location' }}</span>
                </div>
                @endif
                
                <h1 class="display-4 fw-bold mb-3">Find Perfect Advertising Spaces</h1>
                <p class="lead mb-4">Discover hoardings and digital screens across India</p>
                
                <!-- Main Search Box -->
                <div class="search-box-main">
                    <form action="{{ route('search') }}" method="GET">
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="bi bi-search"></i>
                            </span>
                            <input 
                                type="text" 
                                name="search" 
                                class="form-control border-start-0 border-end-0" 
                                placeholder="Search by city, location, or hoarding type..."
                                value="{{ request('search') }}"
                            >
                            @if(!isset($userLocation))
                            <button 
                                type="button" 
                                class="btn btn-outline-secondary border-start-0" 
                                id="useLocationBtn"
                                title="Use my location"
                            >
                                <i class="bi bi-crosshair"></i>
                            </button>
                            @endif
                            <button type="submit" class="btn btn-primary px-4">
                                Search
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="row g-4 mb-5">
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="number">{{ $stats['total_hoardings'] ?? '500+' }}</div>
                <div class="label">Total Hoardings</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="number">{{ $stats['cities'] ?? '50+' }}</div>
                <div class="label">Cities Covered</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="number">{{ $stats['active_vendors'] ?? '100+' }}</div>
                <div class="label">Active Vendors</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="stats-card">
                <div class="number">{{ $stats['bookings'] ?? '1000+' }}</div>
                <div class="label">Successful Campaigns</div>
            </div>
        </div>
    </div>

    <!-- Categories -->
    <div class="mb-5">
        <div class="section-header">
            <h2 class="section-title">Browse by Category</h2>
        </div>
        <div class="d-flex gap-3 flex-wrap">
            <a href="{{ route('search', ['type' => 'billboard']) }}" class="category-chip">
                <i class="bi bi-badge-ad me-2"></i> Billboards
            </a>
            <a href="{{ route('search', ['type' => 'hoarding']) }}" class="category-chip">
                <i class="bi bi-sign-stop me-2"></i> Hoardings
            </a>
            <a href="{{ route('search', ['type' => 'unipole']) }}" class="category-chip">
                <i class="bi bi-signpost me-2"></i> Unipoles
            </a>
            <a href="{{ route('search', ['type' => 'digital']) }}" class="category-chip">
                <i class="bi bi-tv me-2"></i> Digital Screens
            </a>
            <a href="{{ route('dooh.index') }}" class="category-chip">
                <i class="bi bi-display me-2"></i> DOOH
            </a>
        </div>
    </div>

    <!-- Featured Hoardings -->
    @if(isset($featuredHoardings) && $featuredHoardings->count() > 0)
    <div class="mb-5">
        <div class="section-header">
            <h2 class="section-title">Featured Hoardings</h2>
            <a href="{{ route('hoardings.index') }}" class="btn btn-outline-primary">View All</a>
        </div>
        <div class="row g-4">
            @foreach($featuredHoardings as $hoarding)
            <div class="col-12 col-md-6 col-lg-4">
                <x-hoarding-card :hoarding="$hoarding" :showActions="true" :isWishlisted="auth()->user()->hasWishlisted($hoarding->id)" />
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Nearby Hoardings (if geolocation enabled) -->
    @if(isset($nearbyHoardings) && $nearbyHoardings->count() > 0)
    <div class="mb-5">
        <div class="section-header">
            <h2 class="section-title">
                <i class="bi bi-geo-alt-fill text-primary"></i> Near You
            </h2>
            <a href="{{ route('search', ['nearby' => 'true']) }}" class="btn btn-outline-primary">View All</a>
        </div>
        <div class="row g-4">
            @foreach($nearbyHoardings as $hoarding)
            <div class="col-12 col-md-6 col-lg-4">
                <x-hoarding-card :hoarding="$hoarding" :showActions="true" :isWishlisted="auth()->user()->hasWishlisted($hoarding->id)" />
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Popular Cities -->
    <div class="mb-5">
        <div class="section-header">
            <h2 class="section-title">Popular Cities</h2>
        </div>
        <div class="row g-4">
            @php
            $popularCities = [
                ['name' => 'Mumbai', 'count' => 150, 'image' => 'mumbai.jpg'],
                ['name' => 'Delhi', 'count' => 120, 'image' => 'delhi.jpg'],
                ['name' => 'Bangalore', 'count' => 100, 'image' => 'bangalore.jpg'],
                ['name' => 'Pune', 'count' => 80, 'image' => 'pune.jpg'],
                ['name' => 'Hyderabad', 'count' => 75, 'image' => 'hyderabad.jpg'],
                ['name' => 'Chennai', 'count' => 70, 'image' => 'chennai.jpg'],
            ];
            @endphp
            
            @foreach($popularCities as $city)
            <div class="col-6 col-md-4 col-lg-2">
                <a href="{{ route('search', ['city' => $city['name']]) }}" class="text-decoration-none">
                    <div class="stats-card">
                        <div class="mb-3">
                            <i class="bi bi-building" style="font-size: 32px; color: #667eea;"></i>
                        </div>
                        <h5 class="mb-1">{{ $city['name'] }}</h5>
                        <p class="text-muted small mb-0">{{ $city['count'] }} Hoardings</p>
                    </div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const useLocationBtn = document.getElementById('useLocationBtn');
    
    if (useLocationBtn) {
        useLocationBtn.addEventListener('click', function() {
            if (navigator.geolocation) {
                useLocationBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
                useLocationBtn.disabled = true;
                
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        const lat = position.coords.latitude;
                        const lng = position.coords.longitude;
                        
                        // Redirect to search with coordinates
                        window.location.href = `/search?lat=${lat}&lng=${lng}&nearby=true`;
                    },
                    function(error) {
                        alert('Could not get your location. Please enable location services.');
                        useLocationBtn.innerHTML = '<i class="bi bi-crosshair"></i>';
                        useLocationBtn.disabled = false;
                    }
                );
            } else {
                alert('Geolocation is not supported by your browser');
            }
        });
    }
});
</script>
@endpush
