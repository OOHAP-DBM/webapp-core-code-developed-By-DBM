@extends('layouts.customer')

@section('title', 'My Enquiries')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Enquiries</h1>
            <p class="text-muted">Track all your enquiries and responses</p>
        </div>
        <a href="{{ route('customer.enquiries.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> New Enquiry
        </a>
    </div>

    <!-- Summary Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Enquiries</h6>
                    <h3 class="mb-0">{{ $summary['total'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Pending</h6>
                    <h3 class="mb-0 text-warning">{{ $summary['pending'] }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Responded</h6>
                    <h3 class="mb-0 text-success">{{ $summary['responded'] }}</h3>
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
                    <input type="text" name="search" class="form-control" placeholder="Search..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All</option>
                        <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="responded" {{ request('status') == 'responded' ? 'selected' : '' }}>Responded</option>
                        <option value="converted" {{ request('status') == 'converted' ? 'selected' : '' }}>Converted</option>
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

    <!-- Enquiries Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Hoarding</th>
                            <th>Vendor</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($enquiries as $enquiry)
                        <tr>
                            <td><strong>#{{ $enquiry->id }}</strong></td>
                            <td>{{ $enquiry->hoarding->title ?? 'N/A' }}</td>
                            <td>{{ $enquiry->vendor->name ?? 'N/A' }}</td>
                            <td>
                                @php
                                $colors = ['pending' => 'warning', 'responded' => 'info', 'converted' => 'success'];
                                @endphp
                                <span class="badge bg-{{ $colors[$enquiry->status] ?? 'secondary' }}">{{ ucfirst($enquiry->status) }}</span>
                            </td>
                            <td><small>{{ $enquiry->created_at->format('M d, Y') }}</small></td>
                            <td>
                                <a href="{{ route('customer.enquiries.show', $enquiry->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5">
                                <i class="bi bi-question-circle fs-1 text-muted d-block mb-3"></i>
                                <p class="text-muted">No enquiries found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($enquiries->hasPages())
        <div class="card-footer bg-white">{{ $enquiries->links() }}</div>
        @endif
    </div>
</div>
@endsection
