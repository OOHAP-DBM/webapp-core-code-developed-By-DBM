{{-- 
    Reusable Hoarding Card Component
    Props: $hoarding, $showActions (default: false), $isWishlisted (default: false)
--}}
@props(['hoarding', 'showActions' => false, 'isWishlisted' => false])

<div class="hoarding-card" data-hoarding-id="{{ $hoarding->id }}">
    <div class="card shadow-sm h-100 position-relative">
        <!-- Wishlist Button -->
        @if($showActions)
        <button 
            class="btn btn-wishlist position-absolute top-0 end-0 m-2 z-3" 
            data-hoarding-id="{{ $hoarding->id }}"
            data-wishlisted="{{ $isWishlisted ? 'true' : 'false' }}"
        >
            <i class="bi {{ $isWishlisted ? 'bi-heart-fill text-danger' : 'bi-heart' }}"></i>
        </button>
        @endif

        <!-- Image -->
        <div class="card-img-wrapper position-relative" style="height: 200px; overflow: hidden;">
            @if($hoarding->primary_image)
                <img 
                    src="{{ asset('storage/' . $hoarding->primary_image) }}" 
                    class="card-img-top h-100 w-100 object-fit-cover" 
                    alt="{{ $hoarding->title }}"
                    loading="lazy"
                >
            @else
                <div class="h-100 w-100 bg-gradient-primary d-flex align-items-center justify-content-center">
                    <i class="bi bi-image text-white" style="font-size: 3rem; opacity: 0.5;"></i>
                </div>
            @endif
            
            <!-- Status Badge -->
            <span class="badge position-absolute top-0 start-0 m-2 
                {{ $hoarding->status === 'available' ? 'bg-success' : 'bg-warning' }}">
                {{ ucfirst($hoarding->status) }}
            </span>
        </div>

        <div class="card-body d-flex flex-column">
            <!-- Title -->
            <h5 class="card-title fw-bold mb-2 text-truncate">{{ $hoarding->title }}</h5>

            <!-- Location -->
            <p class="text-muted mb-2 small">
                <i class="bi bi-geo-alt-fill"></i> 
                {{ $hoarding->city }}, {{ $hoarding->state }}
            </p>

            <!-- Specs -->
            <div class="d-flex gap-2 mb-3 flex-wrap">
                <span class="badge bg-light text-dark">
                    <i class="bi bi-rulers"></i> {{ $hoarding->width }}x{{ $hoarding->height }} ft
                </span>
                <span class="badge bg-light text-dark">
                    <i class="bi bi-eye-fill"></i> {{ number_format($hoarding->illumination_type === 'lit' ? 150 : 100) }}+ views/day
                </span>
            </div>

            <!-- Price -->
            <div class="mt-auto">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small">Starting from</span>
                    <div class="text-end">
                        <span class="h5 fw-bold text-primary mb-0">₹{{ number_format($hoarding->price_per_month ?? 0) }}</span>
                        <small class="text-muted d-block">/month</small>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="d-flex gap-2">
                    <a href="{{ route('hoardings.show', $hoarding->id) }}" class="btn btn-outline-primary btn-sm flex-fill">
                        <i class="bi bi-eye"></i> View Details
                    </a>
                    @if($showActions)
                    <a href="{{ route('customer.enquiries.create', ['hoarding_id' => $hoarding->id]) }}" class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-envelope"></i> Enquire
                    </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hoarding-card .card {
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    border: none;
}

.hoarding-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
}

.btn-wishlist {
    background: rgba(255, 255, 255, 0.9);
    border: none;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.btn-wishlist:hover {
    background: white;
    transform: scale(1.1);
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}
</style>
