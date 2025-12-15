@extends('layouts.customer')

@section('title', 'All Campaigns')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">All Campaigns</h1>
            <p class="text-muted">Browse and filter your campaigns</p>
        </div>
        <div>
            <a href="{{ route('customer.campaigns.export', request()->query()) }}" class="btn btn-outline-primary">
                <i class="fas fa-download"></i> Export
            </a>
            <a href="{{ route('customer.campaigns.dashboard') }}" class="btn btn-outline-secondary">
                <i class="fas fa-th"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('customer.campaigns.index') }}" id="filterForm">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label small">Status</label>
                        <select name="status" class="form-select" onchange="document.getElementById('filterForm').submit()">
                            <option value="">All Statuses</option>
                            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                            <option value="upcoming" {{ request('status') === 'upcoming' ? 'selected' : '' }}>Upcoming</option>
                            <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                            <option value="mounted" {{ request('status') === 'mounted' ? 'selected' : '' }}>Mounted</option>
                            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">City</label>
                        <input type="text" name="city" class="form-control" placeholder="Enter city" value="{{ request('city') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Type</label>
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="billboard" {{ request('type') === 'billboard' ? 'selected' : '' }}>Billboard</option>
                            <option value="hoarding" {{ request('type') === 'hoarding' ? 'selected' : '' }}>Hoarding</option>
                            <option value="digital" {{ request('type') === 'digital' ? 'selected' : '' }}>Digital/DOOH</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Search</label>
                        <input type="text" name="search" class="form-control" placeholder="Booking ID, location..." value="{{ request('search') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Start Date From</label>
                        <input type="date" name="start_date" class="form-control" value="{{ request('start_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">End Date To</label>
                        <input type="date" name="end_date" class="form-control" value="{{ request('end_date') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label small">Sort By</label>
                        <select name="sort_by" class="form-select">
                            <option value="start_date" {{ request('sort_by') === 'start_date' ? 'selected' : '' }}>Start Date</option>
                            <option value="created_at" {{ request('sort_by') === 'created_at' ? 'selected' : '' }}>Created Date</option>
                            <option value="total_amount" {{ request('sort_by') === 'total_amount' ? 'selected' : '' }}>Amount</option>
                        </select>
                    </div>

                    <div class="col-md-3 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary me-2">
                            <i class="fas fa-filter"></i> Apply
                        </button>
                        <a href="{{ route('customer.campaigns.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-redo"></i> Reset
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Campaigns List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            @if($campaigns->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Campaign Details</th>
                                <th>Location</th>
                                <th>Duration</th>
                                <th>Status</th>
                                <th>Amount</th>
                                <th>PO</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($campaigns as $campaign)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        @if($campaign['hoarding']['image_url'])
                                            <img src="{{ $campaign['hoarding']['image_url'] }}" 
                                                 alt="{{ $campaign['hoarding']['title'] }}"
                                                 class="rounded me-2"
                                                 style="width: 60px; height: 60px; object-fit: cover;">
                                        @else
                                            <div class="bg-secondary rounded me-2 d-flex align-items-center justify-content-center"
                                                 style="width: 60px; height: 60px;">
                                                <i class="fas fa-image text-white"></i>
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $campaign['hoarding']['title'] }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $campaign['booking_id'] }}</small>
                                            <br>
                                            <span class="badge bg-secondary">{{ ucfirst($campaign['hoarding']['type']) }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <strong>{{ $campaign['hoarding']['location'] }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        {{ $campaign['hoarding']['city'] }}, {{ $campaign['hoarding']['state'] }}
                                    </small>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ \Carbon\Carbon::parse($campaign['dates']['start'])->format('M d, Y') }}</strong>
                                        <small class="text-muted">to</small>
                                        <strong>{{ \Carbon\Carbon::parse($campaign['dates']['end'])->format('M d, Y') }}</strong>
                                    </div>
                                    <small class="text-muted">{{ $campaign['dates']['duration_days'] }} days</small>
                                    @if($campaign['dates']['is_active'])
                                        <br><span class="badge bg-success">Live Now</span>
                                    @elseif($campaign['dates']['days_until_start'] > 0)
                                        <br><small class="text-info">Starts in {{ abs($campaign['dates']['days_until_start']) }} days</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-{{ $campaign['status_color'] }}">
                                        {{ $campaign['status_label'] }}
                                    </span>
                                    <br>
                                    <small class="text-muted">{{ $campaign['current_stage'] }}</small>
                                </td>
                                <td>
                                    <strong>₹{{ number_format($campaign['financials']['total_amount']) }}</strong>
                                    <br>
                                    <small class="text-muted">{{ ucfirst($campaign['financials']['payment_status']) }}</small>
                                </td>
                                <td>
                                    @if($campaign['purchase_order'])
                                        <div>
                                            {{ $campaign['purchase_order']['po_number'] }}
                                            <br>
                                            @if($campaign['purchase_order']['pdf_url'])
                                                <a href="{{ $campaign['purchase_order']['pdf_url'] }}" 
                                                   target="_blank" 
                                                   class="btn btn-sm btn-outline-primary mt-1">
                                                    <i class="fas fa-file-pdf"></i> PDF
                                                </a>
                                            @endif
                                        </div>
                                    @else
                                        <small class="text-muted">Pending</small>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('customer.campaigns.show', $campaign['id']) }}" 
                                       class="btn btn-sm btn-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $campaigns->appends(request()->query())->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5>No campaigns found</h5>
                    <p class="text-muted">Try adjusting your filters or create a new campaign</p>
                    <a href="{{ route('hoardings.index') }}" class="btn btn-primary mt-2">
                        <i class="fas fa-plus"></i> Browse Hoardings
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
