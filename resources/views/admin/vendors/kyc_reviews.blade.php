@extends('layouts.admin')

@section('title', 'KYC Reviews & Manual Override')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h2 class="mb-1">KYC Reviews & Manual Override</h2>
                    <p class="text-muted mb-0">Review Razorpay verification status and override when necessary</p>
                </div>
                <div>
                    <button type="button" class="btn btn-outline-primary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-left-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-warning text-uppercase mb-1">Pending Verification</h6>
                            <h3 class="mb-0">{{ $stats['pending_verification'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-success text-uppercase mb-1">Verified</h6>
                            <h3 class="mb-0">{{ $stats['verified'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-check-circle fa-2x text-success opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-danger text-uppercase mb-1">Rejected</h6>
                            <h3 class="mb-0">{{ $stats['rejected'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-times-circle fa-2x text-danger opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-left-secondary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-secondary text-uppercase mb-1">Failed</h6>
                            <h3 class="mb-0">{{ $stats['failed'] ?? 0 }}</h3>
                        </div>
                        <i class="fas fa-exclamation-triangle fa-2x text-secondary opacity-25"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ url('/admin/vendor/kyc-reviews') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Payout Status</label>
                    <select name="payout_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending_verification" {{ request('payout_status') == 'pending_verification' ? 'selected' : '' }}>Pending Verification</option>
                        <option value="verified" {{ request('payout_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        <option value="rejected" {{ request('payout_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                        <option value="failed" {{ request('payout_status') == 'failed' ? 'selected' : '' }}>Failed</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">KYC Status</label>
                    <select name="verification_status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="under_review" {{ request('verification_status') == 'under_review' ? 'selected' : '' }}>Under Review</option>
                        <option value="approved" {{ request('verification_status') == 'approved' ? 'selected' : '' }}>Approved</option>
                        <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Business name, PAN, GST..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-filter"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- KYC List -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">KYC Submissions ({{ $kycs->total() }} total)</h5>
        </div>
        <div class="card-body p-0">
            @if($kycs->count() > 0)
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>ID</th>
                                <th>Vendor</th>
                                <th>Business</th>
                                <th>KYC Status</th>
                                <th>Payout Status</th>
                                <th>Razorpay ID</th>
                                <th>Submitted</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($kycs as $kyc)
                            <tr>
                                <td>
                                    <strong>#{{ $kyc->id }}</strong>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $kyc->vendor->name }}</strong><br>
                                        <small class="text-muted">{{ $kyc->vendor->email }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <strong>{{ $kyc->business_name }}</strong><br>
                                        <small class="text-muted">{{ strtoupper($kyc->business_type) }}</small>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'pending' => 'secondary',
                                            'under_review' => 'info',
                                            'approved' => 'success',
                                            'rejected' => 'danger',
                                            'resubmission_required' => 'warning',
                                        ];
                                        $color = $statusColors[$kyc->verification_status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $color }}">
                                        {{ str_replace('_', ' ', strtoupper($kyc->verification_status)) }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $payoutColors = [
                                            'pending_verification' => 'warning',
                                            'verified' => 'success',
                                            'rejected' => 'danger',
                                            'failed' => 'secondary',
                                        ];
                                        $payoutColor = $payoutColors[$kyc->payout_status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $payoutColor }}">
                                        {{ str_replace('_', ' ', strtoupper($kyc->payout_status)) }}
                                    </span>
                                    @if($kyc->payout_status === 'rejected' && isset($kyc->verification_details['razorpay_rejection_reason']))
                                        <br>
                                        <small class="text-danger">{{ $kyc->verification_details['razorpay_rejection_reason'] }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($kyc->razorpay_subaccount_id)
                                        <small class="font-monospace">{{ substr($kyc->razorpay_subaccount_id, 0, 12) }}...</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <small>{{ $kyc->submitted_at ? $kyc->submitted_at->format('M d, Y H:i') : '-' }}</small>
                                </td>
                                <td>
                                    <a href="{{ url('/admin/vendor/kyc-reviews/' . $kyc->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="card-footer">
                    {{ $kycs->links() }}
                </div>
            @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No KYC submissions found</p>
                </div>
            @endif
        </div>
    </div>
</div>

<style>
.border-left-warning {
    border-left: 4px solid #ffc107;
}
.border-left-success {
    border-left: 4px solid #28a745;
}
.border-left-danger {
    border-left: 4px solid #dc3545;
}
.border-left-secondary {
    border-left: 4px solid #6c757d;
}
.opacity-25 {
    opacity: 0.25;
}
</style>

<script>
function refreshData() {
    window.location.reload();
}
</script>
@endsection
