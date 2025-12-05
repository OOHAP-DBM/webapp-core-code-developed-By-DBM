@extends('layouts.admin')

@section('title', 'KYC Verifications')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">
                        <i class="bi bi-shield-check text-primary"></i>
                        KYC Verifications
                    </h2>
                    <p class="text-muted mb-0">Review and approve vendor KYC submissions</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-secondary text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['pending'] }}</h3>
                    <small>Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-info text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['under_review'] }}</h3>
                    <small>Under Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-success text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['approved'] }}</h3>
                    <small>Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-danger text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['rejected'] }}</h3>
                    <small>Rejected</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-warning text-dark">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['resubmission_required'] }}</h3>
                    <small>Resubmission</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm bg-dark text-white">
                <div class="card-body text-center">
                    <h3 class="mb-0">{{ $stats['total'] }}</h3>
                    <small>Total</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.kyc.index') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label small">Status</label>
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="all" {{ request('status') === 'all' || !request('status') ? 'selected' : '' }}>All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ request('status') === 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="resubmission_required" {{ request('status') === 'resubmission_required' ? 'selected' : '' }}>Resubmission Required</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label small">Search</label>
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Business name, PAN, GST, vendor name..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-search"></i> Search
                        </button>
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Sort By</label>
                    <select name="sort_by" class="form-select" onchange="this.form.submit()">
                        <option value="submitted_at" {{ request('sort_by') === 'submitted_at' || !request('sort_by') ? 'selected' : '' }}>Submitted Date</option>
                        <option value="business_name" {{ request('sort_by') === 'business_name' ? 'selected' : '' }}>Business Name</option>
                        <option value="verification_status" {{ request('sort_by') === 'verification_status' ? 'selected' : '' }}>Status</option>
                    </select>
                    <input type="hidden" name="sort_order" value="{{ request('sort_order', 'desc') }}">
                </div>
            </form>
        </div>
    </div>

    <!-- KYC List -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            @if($kycs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Vendor</th>
                                <th>Business Details</th>
                                <th>Contact</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th>Verified By</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kycs as $kyc)
                                <tr>
                                    <td class="align-middle">
                                        <strong>#{{ $kyc->id }}</strong>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <strong>{{ $kyc->vendor->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $kyc->vendor->email }}</small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            <strong>{{ $kyc->business_name }}</strong>
                                            <br>
                                            <small class="text-muted">
                                                {{ strtoupper($kyc->business_type) }} • PAN: {{ $kyc->pan_number }}
                                                @if($kyc->gst_number)
                                                    • GST: {{ $kyc->gst_number }}
                                                @endif
                                            </small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <div>
                                            {{ $kyc->contact_name }}
                                            <br>
                                            <small class="text-muted">{{ $kyc->contact_phone }}</small>
                                        </div>
                                    </td>
                                    <td class="align-middle">
                                        <span class="badge {{ $kyc->status_badge_class }}">
                                            {{ $kyc->status_label }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <small>{{ $kyc->submitted_at ? $kyc->submitted_at->format('d M Y, h:i A') : 'N/A' }}</small>
                                    </td>
                                    <td class="align-middle">
                                        @if($kyc->verifier)
                                            <small>{{ $kyc->verifier->name }}</small>
                                        @else
                                            <small class="text-muted">-</small>
                                        @endif
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('admin.kyc.show', $kyc->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-5">
                    <i class="bi bi-inbox text-muted" style="font-size: 3rem;"></i>
                    <p class="text-muted mt-2">No KYC submissions found</p>
                </div>
            @endif
        </div>
        
        @if($kycs->hasPages())
            <div class="card-footer bg-white">
                {{ $kycs->links() }}
            </div>
        @endif
    </div>
</div>

<style>
.card {
    transition: all 0.3s ease;
}

.table tbody tr {
    cursor: pointer;
}

.table tbody tr:hover {
    background-color: rgba(0, 123, 255, 0.05);
}
</style>
@endsection
