{{-- 
    Reusable Hoarding Card Component (PROMPT 50 - Enhanced with Figma Design)
    Props: $hoarding, $showActions (default: false), $isWishlisted (default: false)
--}}
@props(['hoarding', 'showActions' => false, 'isWishlisted' => false])

<div class="hoarding-card" data-hoarding-id="{{ $hoarding->id }}">
    <div class="card shadow-sm h-100 position-relative">
        <!-- Wishlist Heart Button - Top Right (Figma Design) -->
        @auth
        <button 
            class="btn-wishlist position-absolute {{ $isWishlisted ? 'active' : '' }}" 
            data-hoarding-id="{{ $hoarding->id }}"
            title="{{ $isWishlisted ? 'Remove from shortlist' : 'Add to shortlist' }}"
            style="top: 12px; right: 12px; z-index: 10;"
        >
            <i class="bi {{ $isWishlisted ? 'bi-heart-fill' : 'bi-heart' }}"></i>
        </button>
        @endauth

        <!-- Image -->
        <div class="card-img-wrapper position-relative" style="height: 220px; overflow: hidden;">
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
            
            <!-- "Best Hoarding" Badge (Figma Design) -->
            @if($hoarding->is_featured ?? false)
            <span class="badge badge-featured position-absolute" style="top: 12px; left: 12px;">
                <i class="bi bi-star-fill"></i> Best Hoarding
            </span>
            @endif
            
            <!-- Status Badge -->
            <span class="badge badge-status position-absolute" style="bottom: 12px; left: 12px;">
                {{ ucfirst($hoarding->status ?? 'available') }}
            </span>
        </div>

        <div class="card-body d-flex flex-column p-3">
            <!-- Location with Icon -->
            <div class="d-flex align-items-center text-muted mb-2" style="font-size: 14px;">
                <i class="bi bi-geo-alt-fill me-1"></i>
                <span class="text-truncate">{{ $hoarding->location ?? ($hoarding->city . ', ' . $hoarding->state) }}</span>
            </div>

            <!-- Title -->
            <h5 class="card-title fw-semibold mb-2 text-truncate" style="font-size: 16px;">
                {{ $hoarding->title }}
            </h5>

            <!-- Rating (Figma Design) -->
            <div class="d-flex align-items-center mb-2">
                <div class="rating-stars me-2">
                    @for($i = 1; $i <= 5; $i++)
                        <i class="bi bi-star{{ $i <= ($hoarding->rating ?? 4.5) ? '-fill' : '' }} text-warning"></i>
                    @endfor
                </div>
                <span class="text-muted" style="font-size: 13px;">{{ number_format($hoarding->rating ?? 4.5, 1) }}</span>
            </div>

            <!-- Price per Impression (Figma Design) -->
            <div class="price-section mb-2">
                <div class="d-flex align-items-baseline">
                    <span class="h5 fw-bold text-primary mb-0">₹{{ number_format($hoarding->price_per_impression ?? 20) }}</span>
                    <span class="text-muted ms-1" style="font-size: 13px;">/impression</span>
                </div>
            </div>

            <!-- Minimum Spend (Figma Design) -->
            <div class="min-spend mb-3" style="font-size: 12px; color: #666;">
                <i class="bi bi-info-circle"></i>
                ₹{{ number_format($hoarding->min_spend ?? 30000) }} Min Spend
            </div>

            <!-- Action Buttons (Figma Design) -->
            <div class="mt-auto">
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-primary btn-sm flex-fill" onclick="addToCart({{ $hoarding->id }})">
                        <i class="bi bi-cart-plus"></i> Add to Cart
                    </button>
                    <a href="{{ route('customer.enquiries.create', ['hoarding_id' => $hoarding->id]) }}" 
                       class="btn btn-primary btn-sm flex-fill">
                        <i class="bi bi-calendar-check"></i> Book Now
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.hoarding-card .card {
    transition: all 0.3s ease;
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    overflow: hidden;
}

.hoarding-card .card:hover {
    transform: translateY(-5px);
    box-shadow: 0 10px 25px rgba(0,0,0,0.12) !important;
}

.btn-wishlist {
    background: rgba(255, 255, 255, 0.95);
    border: none;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    cursor: pointer;
}

.btn-wishlist i {
    font-size: 18px;
    color: #64748b;
    transition: all 0.3s ease;
}

.btn-wishlist:hover {
    background: white;
    transform: scale(1.1);
}

.btn-wishlist:hover i {
    color: #ef4444;
}

.btn-wishlist.active i {
    color: #ef4444;
}

.badge-featured {
    background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
    color: white;
    font-weight: 600;
    font-size: 11px;
    padding: 6px 12px;
    border-radius: 20px;
    box-shadow: 0 2px 8px rgba(251, 191, 36, 0.3);
}

.badge-status {
    background: rgba(16, 185, 129, 0.9);
    color: white;
    font-weight: 500;
    font-size: 11px;
    padding: 4px 12px;
    border-radius: 20px;
}

.rating-stars i {
    font-size: 14px;
}

.bg-gradient-primary {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
}

.card-img-wrapper {
    border-radius: 12px 12px 0 0;
}

/* Cart notification */
.cart-indicator {
    font-size: 12px;
    color: #10b981;
    font-weight: 500;
}
</style>
