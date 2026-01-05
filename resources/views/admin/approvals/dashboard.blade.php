@extends('layouts.admin')

@section('title', 'Hoarding Approvals')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">Hoarding Approval Dashboard</h2>
            <p class="text-muted">Review and approve vendor-submitted hoardings</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('admin.approvals.export') }}?status={{ $status }}" class="btn btn-outline-primary">
                <i class="bi bi-download me-2"></i>Export CSV
            </a>
            <a href="{{ route('admin.approvals.settings') }}" class="btn btn-outline-secondary">
                <i class="bi bi-gear"></i>
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Pending Review</h6>
                            <h2 class="mb-0 text-warning">{{ $stats['pending'] }}</h2>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <i class="bi bi-hourglass-split fs-4 text-warning"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Under Verification</h6>
                            <h2 class="mb-0 text-info">{{ $stats['under_verification'] }}</h2>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 rounded">
                            <i class="bi bi-search fs-4 text-info"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">Approved Today</h6>
                            <h2 class="mb-0 text-success">{{ $stats['approved_today'] }}</h2>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 rounded">
                            <i class="bi bi-check-circle fs-4 text-success"></i>
                        </div>
                    </div>
                    <small class="text-muted">{{ $stats['total_approved'] }} this {{ $period }}</small>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h6 class="text-muted mb-1">SLA Breaches</h6>
                            <h2 class="mb-0 text-danger">{{ $stats['sla_breaches'] }}</h2>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 rounded">
                            <i class="bi bi-exclamation-triangle fs-4 text-danger"></i>
                        </div>
                    </div>
                    <small class="text-muted">Avg: {{ $stats['avg_approval_time'] ?? 'N/A' }}h</small>
                </div>
            </div>
        </div>
    </div>

    <!-- SLA Breaches Alert -->
    @if(count($slaBreaches) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill me-3 fs-4"></i>
                <div>
                    <strong>{{ count($slaBreaches) }} hoardings have exceeded the 48-hour SLA!</strong>
                    <p class="mb-0">These require immediate attention.</p>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Filters -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form method="GET" action="{{ route('admin.approvals.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Status Filter</label>
                            <select name="status" class="form-select" onchange="this.form.submit()">
                                <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Pending</option>
                                <option value="pending" {{ $status === 'pending' ? 'selected' : '' }}>Pending Review</option>
                                <option value="under_verification" {{ $status === 'under_verification' ? 'selected' : '' }}>Under Verification</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Period</label>
                            <select name="period" class="form-select" onchange="this.form.submit()">
                                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Approvals Table -->
    <div class="row">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-list-check me-2"></i>Pending Approvals
                        <span class="badge bg-primary ms-2">{{ count($hoardings) }}</span>
                    </h5>
                    
                    @if(count($hoardings) > 0)
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><input type="checkbox" id="selectAll"></th>
                                    <th>ID</th>
                                    <th>Location</th>
                                    <th>City</th>
                                    <th>Vendor</th>
                                    <th>Type</th>
                                    <th>Size</th>
                                    <th>Price/Month</th>
                                    <th>Status</th>
                                    <th>Submitted</th>
                                    <th>Waiting</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($hoardings as $hoarding)
                                <tr class="{{ \Carbon\Carbon::parse($hoarding->submitted_at)->diffInHours(now()) > 48 ? 'table-danger' : '' }}">
                                    <td><input type="checkbox" class="hoarding-checkbox" value="{{ $hoarding->id }}"></td>
                                    <td><strong>#{{ $hoarding->id }}</strong></td>
                                    <td>
                                        <strong>{{ $hoarding->location_name }}</strong>
                                        <br><small class="text-muted">v{{ $hoarding->current_version }}</small>
                                    </td>
                                    <td>{{ $hoarding->city }}</td>
                                    <td>
                                        {{ $hoarding->vendor_name }}
                                        <br><small class="text-muted">{{ $hoarding->vendor_email }}</small>
                                    </td>
                                    <td><span class="badge bg-secondary">{{ ucfirst($hoarding->board_type) }}</span></td>
                                    <td>{{ $hoarding->width }}x{{ $hoarding->height }}m</td>
                                    <td>₹{{ number_format($hoarding->price_per_month, 0) }}</td>
                                    <td>
                                        @if($hoarding->status === 'pending')
                                        <span class="badge bg-warning">Pending</span>
                                        @elseif($hoarding->status === 'under_verification')
                                        <span class="badge bg-info">Verifying</span>
                                        @endif
                                    </td>
                                    <td>
                                        <small>{{ \Carbon\Carbon::parse($hoarding->submitted_at)->format('M d, Y') }}</small>
                                        <br><small class="text-muted">{{ \Carbon\Carbon::parse($hoarding->submitted_at)->format('h:i A') }}</small>
                                    </td>
                                    <td>
                                        @php
                                            $hours = \Carbon\Carbon::parse($hoarding->submitted_at)->diffInHours(now());
                                            $days = floor($hours / 24);
                                        @endphp
                                        <span class="badge bg-{{ $hours > 48 ? 'danger' : ($hours > 24 ? 'warning' : 'success') }}">
                                            {{ $days > 0 ? $days . 'd ' : '' }}{{ $hours % 24 }}h
                                        </span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.approvals.show', $hoarding->id) }}" class="btn btn-sm btn-primary">
                                            <i class="bi bi-eye"></i> Review
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Bulk Actions -->
                    <div class="mt-3">
                        <form method="POST" action="{{ route('admin.approvals.bulk-approve') }}" id="bulkApproveForm">
                            @csrf
                            <input type="hidden" name="hoarding_ids" id="bulkHoardingIds">
                            <button type="submit" class="btn btn-success" id="bulkApproveBtn" disabled>
                                <i class="bi bi-check-circle me-2"></i>Bulk Approve Selected
                            </button>
                        </form>
                    </div>
                    @else
                    <div class="text-center py-5">
                        <i class="bi bi-inbox fs-1 text-muted"></i>
                        <p class="text-muted mt-3">No pending approvals</p>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-clock-history me-2"></i>Recent Activity
                    </h5>
                    
                    <div class="timeline">
                        @foreach($recentActivity as $activity)
                        <div class="timeline-item mb-3">
                            <div class="d-flex">
                                <div class="timeline-marker me-3">
                                    @if($activity->action === 'approved')
                                    <i class="bi bi-check-circle-fill text-success"></i>
                                    @elseif($activity->action === 'rejected')
                                    <i class="bi bi-x-circle-fill text-danger"></i>
                                    @elseif($activity->action === 'submitted')
                                    <i class="bi bi-upload text-primary"></i>
                                    @else
                                    <i class="bi bi-circle-fill text-secondary"></i>
                                    @endif
                                </div>
                                <div class="flex-grow-1">
                                    <div class="d-flex justify-content-between">
                                        <div>
                                            <strong>{{ $activity->performer_name }}</strong>
                                            <span class="text-muted">{{ ucfirst(str_replace('_', ' ', $activity->action)) }}</span>
                                            <strong>{{ $activity->location_name }}</strong>
                                        </div>
                                        <small class="text-muted">{{ \Carbon\Carbon::parse($activity->performed_at)->diffForHumans() }}</small>
                                    </div>
                                    @if($activity->notes)
                                    <small class="text-muted d-block mt-1">{{ Str::limit($activity->notes, 100) }}</small>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
    .timeline-marker {
        font-size: 1.2rem;
    }
    
    .timeline-item {
        padding-bottom: 1rem;
        border-left: 2px solid #e9ecef;
        padding-left: 1rem;
        margin-left: 0.6rem;
    }
    
    .timeline-item:last-child {
        border-left: none;
    }
    
    .table-danger {
        background-color: rgba(220, 53, 69, 0.1);
    }
</style>
@endpush

@push('scripts')
<script>
    // Select all functionality
    document.getElementById('selectAll').addEventListener('change', function() {
        const checkboxes = document.querySelectorAll('.hoarding-checkbox');
        checkboxes.forEach(cb => cb.checked = this.checked);
        updateBulkButton();
    });

    // Individual checkbox change
    document.querySelectorAll('.hoarding-checkbox').forEach(checkbox => {
        checkbox.addEventListener('change', updateBulkButton);
    });

    function updateBulkButton() {
        const checked = document.querySelectorAll('.hoarding-checkbox:checked');
        const bulkBtn = document.getElementById('bulkApproveBtn');
        const bulkIds = document.getElementById('bulkHoardingIds');
        
        if (checked.length > 0) {
            bulkBtn.disabled = false;
            bulkBtn.textContent = `Bulk Approve Selected (${checked.length})`;
            
            const ids = Array.from(checked).map(cb => cb.value);
            bulkIds.value = JSON.stringify(ids);
        } else {
            bulkBtn.disabled = true;
            bulkBtn.innerHTML = '<i class="bi bi-check-circle me-2"></i>Bulk Approve Selected';
        }
    }

    // Bulk approve form submission
    document.getElementById('bulkApproveForm').addEventListener('submit', function(e) {
        if (!confirm('Are you sure you want to approve all selected hoardings?')) {
            e.preventDefault();
        }
    });
</script>
@endpush
