@extends('layouts.vendor')

@section('title', 'My Hoardings')

@section('content')
<style>
.hover-primary:hover {
    color: #0d6efd !important;
    text-decoration: underline !important;
}
</style>
<div class="container-fluid">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0">My Hoardings</h1>
        <a href="{{ route('vendor.hoardings.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Add New Hoarding
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-2">
            <div class="card border-primary">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $statistics['total'] }}</h3>
                    <p class="text-muted mb-0 small">Total</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-success">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-success">{{ $statistics['active'] }}</h3>
                    <p class="text-muted mb-0 small">Active</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-secondary">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-secondary">{{ $statistics['draft'] }}</h3>
                    <p class="text-muted mb-0 small">Draft</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-warning">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-warning">{{ $statistics['pending'] }}</h3>
                    <p class="text-muted mb-0 small">Pending</p>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-info">
                <div class="card-body text-center">
                    <h3 class="mb-0 text-info">{{ $statistics['inactive'] }}</h3>
                    <p class="text-muted mb-0 small">Inactive</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('vendor.hoardings.index') }}" class="row g-3">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control" placeholder="Search by title or address..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="billboard" {{ request('type') === 'billboard' ? 'selected' : '' }}>Billboard</option>
                        <option value="digital" {{ request('type') === 'digital' ? 'selected' : '' }}>Digital Screen</option>
                        <option value="transit" {{ request('type') === 'transit' ? 'selected' : '' }}>Transit</option>
                        <option value="street_furniture" {{ request('type') === 'street_furniture' ? 'selected' : '' }}>Street Furniture</option>
                        <option value="wallscape" {{ request('type') === 'wallscape' ? 'selected' : '' }}>Wallscape</option>
                        <option value="mobile" {{ request('type') === 'mobile' ? 'selected' : '' }}>Mobile</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                        <option value="pending_approval" {{ request('status') === 'pending_approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                        <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspended</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Hoardings Grid -->
    @if($hoardings->count() > 0)
        <div class="row">
            @foreach($hoardings as $hoarding)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    <a href="{{ route('hoardings.show', $hoarding->id) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $hoarding->title }}
                                    </a>
                                </h5>
                                <span class="badge 
                                    @if($hoarding->status === 'active') bg-success
                                    @elseif($hoarding->status === 'draft') bg-secondary
                                    @elseif($hoarding->status === 'pending_approval') bg-warning
                                    @elseif($hoarding->status === 'inactive') bg-info
                                    @else bg-danger
                                    @endif">
                                    {{ $hoarding->status_label }}
                                </span>
                            </div>
                            
                            <p class="text-muted small mb-2">
                                <i class="bi bi-tag"></i> {{ $hoarding->type_label }}
                            </p>
                            
                            <p class="card-text text-truncate mb-2" style="max-height: 3em;">
                                {{ $hoarding->description ?? 'No description' }}
                            </p>
                            
                            <p class="text-muted small mb-2">
                                <i class="bi bi-geo-alt"></i> {{ Str::limit($hoarding->address, 50) }}
                            </p>
                            
                            <div class="mb-3">
                                <strong class="text-primary">₹{{ number_format($hoarding->monthly_price, 2) }}</strong> <span class="text-muted small">/ month</span>
                                @if($hoarding->supports_weekly_booking)
                                    <br>
                                    <strong class="text-success">₹{{ number_format($hoarding->weekly_price, 2) }}</strong> <span class="text-muted small">/ week</span>
                                @endif
                            </div>
                            
                            <div class="d-flex gap-2 mb-2">
                                <a href="{{ route('vendor.hoardings.edit', $hoarding->id) }}" class="btn btn-sm btn-outline-primary flex-fill">
                                    <i class="bi bi-pencil"></i> Edit
                                </a>
                                <a href="{{ route('vendor.hoarding.calendar', $hoarding->id) }}" class="btn btn-sm btn-outline-success flex-fill" title="View Availability Calendar">
                                    <i class="bi bi-calendar3"></i> Calendar
                                </a>
                            </div>
                            <div class="d-flex gap-2">
                                <form action="{{ route('vendor.hoardings.destroy', $hoarding->id) }}" method="POST" class="flex-fill" onsubmit="return confirm('Are you sure you want to delete this hoarding?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger w-100">
                                        <i class="bi bi-trash"></i> Delete
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center mt-4">
            {{ $hoardings->links() }}
        </div>
    @else
        <div class="card">
            <div class="card-body text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #ccc;"></i>
                <h5 class="mt-3">No hoardings found</h5>
                <p class="text-muted">Start by creating your first hoarding listing.</p>
                <a href="{{ route('vendor.hoardings.create') }}" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Add New Hoarding
                </a>
            </div>
        </div>
    @endif
</div>
@endsection
