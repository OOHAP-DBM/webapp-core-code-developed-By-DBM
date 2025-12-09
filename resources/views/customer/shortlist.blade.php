@extends('layouts.customer')

@section('title', 'My Shortlist - OOHAPP')

@push('styles')
<style>
    .page-header {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state {
        text-align: center;
        padding: 80px 20px;
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .empty-state i {
        font-size: 64px;
        color: #cbd5e1;
        margin-bottom: 24px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Page Header -->
    <div class="page-header">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h2 class="mb-2">My Shortlist</h2>
                <p class="text-muted mb-0">Hoardings you've saved for later</p>
            </div>
            <div class="col-md-4 text-end">
                @if(isset($wishlist) && $wishlist->count() > 0)
                <button class="btn btn-outline-danger" onclick="clearAllShortlist()">
                    <i class="bi bi-trash"></i> Clear All
                </button>
                @endif
            </div>
        </div>
    </div>

    @if(isset($wishlist) && $wishlist->count() > 0)
    <!-- Shortlisted Items -->
    <div class="row g-4">
        @foreach($wishlist as $item)
        <div class="col-12 col-md-6 col-lg-4" id="wishlist-item-{{ $item->hoarding_id }}">
            <x-hoarding-card 
                :hoarding="$item->hoarding" 
                :showActions="true" 
                :isWishlisted="true"
            />
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    @if($wishlist->hasPages())
    <div class="mt-4">
        {{ $wishlist->links() }}
    </div>
    @endif

    @else
    <!-- Empty State -->
    <div class="empty-state">
        <i class="bi bi-heart"></i>
        <h3>Your shortlist is empty</h3>
        <p class="text-muted mb-4">Start adding hoardings to your shortlist to compare and book later</p>
        <a href="{{ route('hoardings.index') }}" class="btn btn-primary">
            <i class="bi bi-search"></i> Browse Hoardings
        </a>
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function clearAllShortlist() {
    if (confirm('Are you sure you want to remove all items from your shortlist?')) {
        fetch('/api/v1/customer/wishlist/clear', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + localStorage.getItem('token'),
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        })
        .catch(error => console.error('Error:', error));
    }
}

// Handle wishlist button clicks
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.btn-wishlist').forEach(button => {
        button.addEventListener('click', function(e) {
            e.preventDefault();
            const hoardingId = this.dataset.hoardingId;
            const isWishlisted = this.dataset.wishlisted === 'true';
            
            if (isWishlisted) {
                removeFromWishlist(hoardingId);
            }
        });
    });
});

function removeFromWishlist(hoardingId) {
    fetch(`/api/v1/customer/wishlist/${hoardingId}`, {
        method: 'DELETE',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const item = document.getElementById(`wishlist-item-${hoardingId}`);
            if (item) {
                item.remove();
                // Reload if no items left
                if (document.querySelectorAll('[id^="wishlist-item-"]').length === 0) {
                    window.location.reload();
                }
            }
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endpush
