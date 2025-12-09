@extends('layouts.customer')

@section('title', 'My Orders - OOHAPP')

@push('styles')
<style>
    .orders-header {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .filter-pills {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
    }
    
    .filter-pill {
        padding: 8px 20px;
        border-radius: 20px;
        border: 2px solid #e2e8f0;
        background: white;
        color: #64748b;
        text-decoration: none;
        font-size: 14px;
        font-weight: 500;
        transition: all 0.2s;
    }
    
    .filter-pill:hover,
    .filter-pill.active {
        border-color: #667eea;
        background: #667eea;
        color: white;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Orders Header -->
    <div class="orders-header">
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="mb-2">My Orders</h2>
                <p class="text-muted mb-0">Track and manage your bookings</p>
            </div>
            <div class="col-md-6 text-end">
                <a href="{{ route('search') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle me-2"></i>New Booking
                </a>
            </div>
        </div>

        <!-- Status Filters -->
        <div class="filter-pills">
            <a href="{{ route('customer.orders.index') }}" class="filter-pill {{ !request('status') ? 'active' : '' }}">
                All Orders
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'pending']) }}" class="filter-pill {{ request('status') === 'pending' ? 'active' : '' }}">
                Pending
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'confirmed']) }}" class="filter-pill {{ request('status') === 'confirmed' ? 'active' : '' }}">
                Confirmed
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'active']) }}" class="filter-pill {{ request('status') === 'active' ? 'active' : '' }}">
                Active
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'completed']) }}" class="filter-pill {{ request('status') === 'completed' ? 'active' : '' }}">
                Completed
            </a>
            <a href="{{ route('customer.orders.index', ['status' => 'cancelled']) }}" class="filter-pill {{ request('status') === 'cancelled' ? 'active' : '' }}">
                Cancelled
            </a>
        </div>
    </div>

    <!-- Orders List -->
    @if(isset($bookings) && $bookings->count() > 0)
    <div class="row">
        <div class="col-12">
            @foreach($bookings as $booking)
            <x-order-card :booking="$booking" />
            @endforeach

            <!-- Pagination -->
            @if($bookings->hasPages())
            <div class="mt-4">
                {{ $bookings->links() }}
            </div>
            @endif
        </div>
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-5">
        <div class="mb-4">
            <i class="bi bi-inbox" style="font-size: 64px; color: #cbd5e1;"></i>
        </div>
        <h4>No orders found</h4>
        <p class="text-muted mb-4">
            @if(request('status'))
                No {{ request('status') }} orders at the moment
            @else
                You haven't made any bookings yet
            @endif
        </p>
        <a href="{{ route('search') }}" class="btn btn-primary">
            <i class="bi bi-search me-2"></i>Browse Hoardings
        </a>
    </div>
    @endif
</div>
@endsection
