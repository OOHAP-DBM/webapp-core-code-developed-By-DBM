@extends('layouts.customer')

@section('title', 'My Offers')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Offers</h1>
            <p class="text-muted">View and manage your special offers</p>
        </div>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Offers</h6>
                    <h3 class="mb-0">{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Active Offers</h6>
                    <h3 class="mb-0 text-success">{{ $summary['active'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Used Offers</h6>
                    <h3 class="mb-0 text-info">{{ $summary['used'] }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Offer code..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                        <option value="used" {{ request('status') == 'used' ? 'selected' : '' }}>Used</option>
                        <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Expired</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100"><i class="bi bi-search"></i> Filter</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offers Grid -->
    <div class="row g-3">
        @forelse($offers as $offer)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-tag text-primary fs-3"></i>
                        </div>
                        @php
                        $statusColors = ['active' => 'success', 'used' => 'info', 'expired' => 'secondary'];
                        @endphp
                        <span class="badge bg-{{ $statusColors[$offer->status] ?? 'secondary' }}">
                            {{ ucfirst($offer->status) }}
                        </span>
                    </div>
                    <h5 class="card-title">{{ $offer->offer_code }}</h5>
                    <p class="card-text text-muted">{{ $offer->description ?? 'Special offer' }}</p>
                    <hr>
                    <div class="d-flex justify-content-between text-sm">
                        <span class="text-muted">Discount:</span>
                        <strong>{{ $offer->discount_percentage ?? $offer->discount_amount }}%</strong>
                    </div>
                    <div class="d-flex justify-content-between text-sm mt-2">
                        <span class="text-muted">Valid Until:</span>
                        <strong>{{ \Carbon\Carbon::parse($offer->valid_until)->format('M d, Y') }}</strong>
                    </div>
                </div>
                <div class="card-footer bg-white">
                    @if($offer->status == 'active')
                    <button class="btn btn-primary btn-sm w-100">Apply Offer</button>
                    @else
                    <button class="btn btn-secondary btn-sm w-100" disabled>{{ ucfirst($offer->status) }}</button>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-5">
                    <i class="bi bi-tag fs-1 text-muted d-block mb-3"></i>
                    <p class="text-muted">No offers available</p>
                </div>
            </div>
        </div>
        @endforelse
    </div>

    @if($offers->hasPages())
    <div class="mt-4">
        {{ $offers->links() }}
    </div>
    @endif
</div>
@endsection
