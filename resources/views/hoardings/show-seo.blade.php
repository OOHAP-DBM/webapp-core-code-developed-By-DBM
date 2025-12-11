@extends('layouts.app')

@section('title', $hoarding->meta_title ?? ($hoarding->location_name . ' - ' . $hoarding->city))

@section('meta')
<meta name="description" content="{{ $hoarding->meta_description ?? $metaDescription }}">
<meta name="keywords" content="{{ is_array($hoarding->meta_keywords) ? implode(', ', $hoarding->meta_keywords) : '' }}">
@if(!$hoarding->index_page)
<meta name="robots" content="noindex, nofollow">
@else
<meta name="robots" content="index, follow">
@endif

{{-- Open Graph Meta Tags --}}
@foreach($openGraphData as $property => $content)
<meta property="{{ $property }}" content="{{ $content }}">
@endforeach

{{-- Twitter Card Meta Tags --}}
@foreach($twitterCardData as $name => $content)
<meta name="{{ $name }}" content="{{ $content }}">
@endforeach

{{-- Canonical URL --}}
<link rel="canonical" href="{{ route('hoardings.show', $hoarding->slug ?? $hoarding->id) }}">

{{-- Structured Data (JSON-LD) --}}
<script type="application/ld+json">
{!! json_encode($structuredData, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>

{{-- Breadcrumb Structured Data --}}
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        @foreach($breadcrumbs as $index => $breadcrumb)
        {
            "@type": "ListItem",
            "position": {{ $index + 1 }},
            "name": "{{ $breadcrumb['label'] }}",
            @if($breadcrumb['url'])
            "item": "{{ $breadcrumb['url'] }}"
            @endif
        }{{ $loop->last ? '' : ',' }}
        @endforeach
    ]
}
</script>
@endsection

@section('content')
<div class="container py-4">
    {{-- Breadcrumbs --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb">
            @foreach($breadcrumbs as $breadcrumb)
            @if($breadcrumb['url'])
            <li class="breadcrumb-item">
                <a href="{{ $breadcrumb['url'] }}">{{ $breadcrumb['label'] }}</a>
            </li>
            @else
            <li class="breadcrumb-item active" aria-current="page">{{ $breadcrumb['label'] }}</li>
            @endif
            @endforeach
        </ol>
    </nav>

    {{-- Main Content --}}
    <div class="row">
        {{-- Left Column - Images & Details --}}
        <div class="col-lg-8">
            {{-- Hero Image Gallery --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-0">
                    @if(!empty($images))
                    <div id="hoardingCarousel" class="carousel slide" data-bs-ride="carousel">
                        <div class="carousel-indicators">
                            @foreach($images as $index => $image)
                            <button type="button" data-bs-target="#hoardingCarousel" data-bs-slide-to="{{ $index }}" 
                                class="{{ $index === 0 ? 'active' : '' }}" aria-current="{{ $index === 0 ? 'true' : 'false' }}" 
                                aria-label="Slide {{ $index + 1 }}"></button>
                            @endforeach
                        </div>
                        <div class="carousel-inner">
                            @foreach($images as $index => $image)
                            <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                <img src="{{ asset('storage/' . $image) }}" 
                                     class="d-block w-100" 
                                     alt="{{ $hoarding->location_name }}"
                                     style="max-height: 500px; object-fit: cover;">
                            </div>
                            @endforeach
                        </div>
                        @if(count($images) > 1)
                        <button class="carousel-control-prev" type="button" data-bs-target="#hoardingCarousel" data-bs-slide="prev">
                            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Previous</span>
                        </button>
                        <button class="carousel-control-next" type="button" data-bs-target="#hoardingCarousel" data-bs-slide="next">
                            <span class="carousel-control-next-icon" aria-hidden="true"></span>
                            <span class="visually-hidden">Next</span>
                        </button>
                        @endif
                    </div>
                    @else
                    <img src="{{ asset('images/default-hoarding.jpg') }}" 
                         class="w-100" 
                         alt="{{ $hoarding->location_name }}"
                         style="max-height: 500px; object-fit: cover;">
                    @endif
                </div>
            </div>

            {{-- Title & Basic Info --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h1 class="h3 mb-3">{{ $hoarding->location_name }}</h1>
                    
                    <div class="d-flex align-items-center mb-3 text-muted">
                        <i class="bi bi-geo-alt me-2"></i>
                        <span>{{ $hoarding->address }}, {{ $hoarding->city }}, {{ $hoarding->state }} - {{ $hoarding->pincode }}</span>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-3">
                            <small class="text-muted d-block">Type</small>
                            <strong class="text-capitalize">{{ str_replace('_', ' ', $hoarding->board_type) }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Size</small>
                            <strong>{{ $hoarding->width }}m × {{ $hoarding->height }}m</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Illuminated</small>
                            <strong>{{ $hoarding->is_lit ? 'Yes' : 'No' }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">Traffic</small>
                            <strong class="text-capitalize">{{ $hoarding->traffic_density ?? 'N/A' }}</strong>
                        </div>
                    </div>

                    @if($hoarding->description)
                    <div class="mb-3">
                        <h5 class="mb-2">Description</h5>
                        <p class="text-muted">{{ $hoarding->description }}</p>
                    </div>
                    @endif

                    @if($hoarding->amenities)
                    <div class="mb-3">
                        <h5 class="mb-2">Features & Amenities</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(json_decode($hoarding->amenities, true) as $amenity)
                            <span class="badge bg-light text-dark border">
                                <i class="bi bi-check-circle me-1"></i>{{ $amenity }}
                            </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    @if($hoarding->target_audience)
                    <div class="mb-3">
                        <h5 class="mb-2">Target Audience</h5>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach(json_decode($hoarding->target_audience, true) as $audience)
                            <span class="badge bg-primary">{{ $audience }}</span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    {{-- Share Buttons --}}
                    <div class="mt-4">
                        <h6 class="mb-2">Share this hoarding:</h6>
                        <div class="d-flex gap-2">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(route('hoardings.show', $hoarding->slug ?? $hoarding->id)) }}" 
                               target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-facebook me-1"></i>Facebook
                            </a>
                            <a href="https://twitter.com/intent/tweet?url={{ urlencode(route('hoardings.show', $hoarding->slug ?? $hoarding->id)) }}&text={{ urlencode($hoarding->location_name) }}" 
                               target="_blank" class="btn btn-sm btn-outline-info">
                                <i class="bi bi-twitter me-1"></i>Twitter
                            </a>
                            <a href="https://api.whatsapp.com/send?text={{ urlencode($hoarding->location_name . ' - ' . route('hoardings.show', $hoarding->slug ?? $hoarding->id)) }}" 
                               target="_blank" class="btn btn-sm btn-outline-success">
                                <i class="bi bi-whatsapp me-1"></i>WhatsApp
                            </a>
                            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="copyToClipboard('{{ route('hoardings.show', $hoarding->slug ?? $hoarding->id) }}')">
                                <i class="bi bi-link-45deg me-1"></i>Copy Link
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Map Location --}}
            @if($hoarding->latitude && $hoarding->longitude)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h5 class="card-title mb-3">Location on Map</h5>
                    <div id="map" style="height: 300px; border-radius: 8px;"></div>
                    <small class="text-muted mt-2 d-block">
                        Coordinates: {{ $hoarding->latitude }}, {{ $hoarding->longitude }}
                    </small>
                </div>
            </div>
            @endif
        </div>

        {{-- Right Column - Booking & Vendor Info --}}
        <div class="col-lg-4">
            {{-- Price & Booking Card --}}
            <div class="card border-0 shadow-sm mb-4 sticky-top" style="top: 20px;">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h3 class="mb-0">₹{{ number_format($hoarding->price_per_month, 0) }}</h3>
                            <small class="text-muted">per month</small>
                        </div>
                        <span class="badge bg-{{ $hoarding->status === 'available' ? 'success' : 'secondary' }}">
                            {{ ucfirst($hoarding->status) }}
                        </span>
                    </div>

                    @if($hoarding->status === 'available')
                    <a href="{{ route('enquiries.create', ['hoarding_id' => $hoarding->id]) }}" 
                       class="btn btn-primary w-100 mb-2">
                        <i class="bi bi-envelope me-2"></i>Send Enquiry
                    </a>
                    <a href="{{ route('bookings.create', ['hoarding_id' => $hoarding->id]) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-calendar-check me-2"></i>Book Now
                    </a>
                    @else
                    <button class="btn btn-secondary w-100" disabled>
                        Currently Unavailable
                    </button>
                    @endif

                    <hr class="my-3">

                    <div class="d-flex justify-content-between text-muted small">
                        <span><i class="bi bi-eye me-1"></i>{{ number_format($hoarding->view_count) }} views</span>
                        @if($hoarding->last_viewed_at)
                        <span>Last viewed {{ \Carbon\Carbon::parse($hoarding->last_viewed_at)->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Vendor Info Card --}}
            @if($hoarding->vendor)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body">
                    <h6 class="card-title mb-3">Vendor Information</h6>
                    <div class="d-flex align-items-center mb-3">
                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                            <i class="bi bi-building fs-4 text-primary"></i>
                        </div>
                        <div>
                            <strong class="d-block">{{ $hoarding->vendor->name }}</strong>
                            <small class="text-muted">Member since {{ $hoarding->vendor->created_at->format('Y') }}</small>
                        </div>
                    </div>
                    @if($vendorStats)
                    <div class="row g-2 text-center">
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Total Hoardings</small>
                                <strong>{{ $vendorStats['total_hoardings'] }}</strong>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 bg-light rounded">
                                <small class="text-muted d-block">Avg Rating</small>
                                <strong>{{ number_format($vendorStats['avg_rating'], 1) }} ⭐</strong>
                            </div>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
            @endif

            {{-- Quick Contact Card --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="card-title mb-3">Need Help?</h6>
                    <p class="small text-muted">Contact our support team for assistance with this hoarding.</p>
                    <a href="{{ route('contact') }}" class="btn btn-outline-primary btn-sm w-100">
                        <i class="bi bi-headset me-2"></i>Contact Support
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Similar Hoardings --}}
    @if(count($similarHoardings) > 0)
    <div class="row mt-5">
        <div class="col-12">
            <h4 class="mb-4">Similar Hoardings in {{ $hoarding->city }}</h4>
            <div class="row g-4">
                @foreach($similarHoardings as $similar)
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        @php
                            $similarImages = is_array($similar->images) ? $similar->images : json_decode($similar->images ?? '[]', true);
                        @endphp
                        @if(!empty($similarImages))
                        <img src="{{ asset('storage/' . $similarImages[0]) }}" 
                             class="card-img-top" 
                             alt="{{ $similar->location_name }}"
                             style="height: 200px; object-fit: cover;">
                        @endif
                        <div class="card-body">
                            <h6 class="card-title">{{ $similar->location_name }}</h6>
                            <p class="small text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> {{ $similar->city }}
                            </p>
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="text-primary">₹{{ number_format($similar->price_per_month, 0) }}</strong>
                                <a href="{{ route('hoardings.show', $similar->slug ?? $similar->id) }}" 
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<style>
    .sticky-top {
        z-index: 1020;
    }
    
    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        background-color: rgba(0, 0, 0, 0.5);
        border-radius: 50%;
        padding: 10px;
    }
</style>
@endpush

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
    @if($hoarding->latitude && $hoarding->longitude)
    // Initialize map
    const map = L.map('map').setView([{{ $hoarding->latitude }}, {{ $hoarding->longitude }}], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);
    
    L.marker([{{ $hoarding->latitude }}, {{ $hoarding->longitude }}])
        .addTo(map)
        .bindPopup('<strong>{{ $hoarding->location_name }}</strong><br>{{ $hoarding->address }}')
        .openPopup();
    @endif

    // Copy to clipboard function
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Link copied to clipboard!');
        });
    }
</script>
@endpush
