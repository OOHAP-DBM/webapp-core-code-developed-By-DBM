@extends('layouts.admin')

@section('title', 'Milestone Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">📊 Milestone Payment Dashboard</h2>
            <p class="text-muted mb-0">Monitor and manage all milestone payments across quotations</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="refreshData()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="{{ route('admin.milestones.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Total Milestones</p>
                            <h3 class="mb-0">{{ $stats['total_milestones'] }}</h3>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-list-task display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Revenue Collected</p>
                            <h3 class="mb-0 text-success">₹{{ number_format($stats['revenue_collected'], 2) }}</h3>
                            <small class="text-muted">{{ $stats['paid_count'] }} paid</small>
                        </div>
                        <div class="text-success">
                            <i class="bi bi-cash-stack display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Pending Amount</p>
                            <h3 class="mb-0 text-warning">₹{{ number_format($stats['pending_amount'], 2) }}</h3>
                            <small class="text-muted">{{ $stats['pending_count'] }} pending</small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-hourglass-split display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Overdue Amount</p>
                            <h3 class="mb-0 text-danger">₹{{ number_format($stats['overdue_amount'], 2) }}</h3>
                            <small class="text-muted">{{ $stats['overdue_count'] }} overdue</small>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Milestone Status Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="statusChart" height="200"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Payment Timeline (Last 30 Days)</h5>
                </div>
                <div class="card-body">
                    <canvas id="timelineChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.milestones.dashboard') }}" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="due" {{ request('status') === 'due' ? 'selected' : '' }}>Due</option>
                        <option value="overdue" {{ request('status') === 'overdue' ? 'selected' : '' }}>Overdue</option>
                        <option value="paid" {{ request('status') === 'paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Date Range</label>
                    <select name="date_range" class="form-select">
                        <option value="">All Time</option>
                        <option value="today" {{ request('date_range') === 'today' ? 'selected' : '' }}>Today</option>
                        <option value="week" {{ request('date_range') === 'week' ? 'selected' : '' }}>This Week</option>
                        <option value="month" {{ request('date_range') === 'month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="Quotation ID, Customer, Vendor..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Overdue Milestones Table -->
    @if($overdueMilestones->count() > 0)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Overdue Milestones ({{ $overdueMilestones->count() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Quotation</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Milestone</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Days Overdue</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($overdueMilestones as $milestone)
                        <tr>
                            <td><a href="{{ route('admin.quotations.show', $milestone->quotation_id) }}">#{{ $milestone->quotation_id }}</a></td>
                            <td>{{ $milestone->quotation->enquiry->customer->name ?? 'N/A' }}</td>
                            <td>{{ $milestone->quotation->enquiry->vendor->name ?? 'N/A' }}</td>
                            <td>{{ $milestone->title }}</td>
                            <td>₹{{ number_format($milestone->calculated_amount, 2) }}</td>
                            <td>{{ $milestone->due_date->format('M d, Y') }}</td>
                            <td>
                                <span class="badge bg-danger">
                                    {{ $milestone->due_date->diffInDays(now()) }} days
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.milestones.show', $milestone->id) }}" 
                                       class="btn btn-outline-primary" title="View">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    <button class="btn btn-outline-warning" 
                                            onclick="sendReminder({{ $milestone->id }})" title="Send Reminder">
                                        <i class="bi bi-bell"></i>
                                    </button>
                                    <button class="btn btn-outline-success" 
                                            onclick="markPaid({{ $milestone->id }})" title="Mark as Paid">
                                        <i class="bi bi-check-circle"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- All Milestones Table -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-table"></i> All Milestones</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="milestonesTable">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Quotation</th>
                            <th>Customer</th>
                            <th>Vendor</th>
                            <th>Title</th>
                            <th>Amount</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Paid On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($milestones as $milestone)
                        <tr>
                            <td>{{ $milestone->id }}</td>
                            <td><a href="{{ route('admin.quotations.show', $milestone->quotation_id) }}">#{{ $milestone->quotation_id }}</a></td>
                            <td>{{ $milestone->quotation->enquiry->customer->name ?? 'N/A' }}</td>
                            <td>{{ $milestone->quotation->enquiry->vendor->name ?? 'N/A' }}</td>
                            <td>{{ $milestone->title }}</td>
                            <td>₹{{ number_format($milestone->calculated_amount, 2) }}</td>
                            <td>{{ $milestone->due_date ? $milestone->due_date->format('M d, Y') : '-' }}</td>
                            <td>
                                <span class="badge bg-{{
                                    $milestone->status === 'paid' ? 'success' :
                                    ($milestone->status === 'overdue' ? 'danger' :
                                    ($milestone->status === 'due' ? 'warning' : 'secondary'))
                                }}">
                                    {{ ucfirst($milestone->status) }}
                                </span>
                            </td>
                            <td>{{ $milestone->paid_at ? $milestone->paid_at->format('M d, Y') : '-' }}</td>
                            <td>
                                <a href="{{ route('admin.milestones.show', $milestone->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer">
            {{ $milestones->links() }}
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Status Distribution Chart
const statusCtx = document.getElementById('statusChart').getContext('2d');
new Chart(statusCtx, {
    type: 'doughnut',
    data: {
        labels: ['Paid', 'Pending', 'Due', 'Overdue'],
        datasets: [{
            data: [
                {{ $stats['paid_count'] }},
                {{ $stats['pending_count'] }},
                {{ $stats['due_count'] ?? 0 }},
                {{ $stats['overdue_count'] }}
            ],
            backgroundColor: ['#198754', '#6c757d', '#ffc107', '#dc3545']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// Timeline Chart
const timelineCtx = document.getElementById('timelineChart').getContext('2d');
new Chart(timelineCtx, {
    type: 'line',
    data: {
        labels: @json($timelineData['labels'] ?? []),
        datasets: [{
            label: 'Payments Received',
            data: @json($timelineData['data'] ?? []),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    callback: function(value) {
                        return '₹' + value.toLocaleString();
                    }
                }
            }
        }
    }
});

// Helper Functions
function sendReminder(milestoneId) {
    if (confirm('Send payment reminder to customer?')) {
        fetch(`/admin/milestones/${milestoneId}/send-reminder`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message);
            if (data.success) location.reload();
        });
    }
}

function markPaid(milestoneId) {
    if (confirm('Mark this milestone as paid? This action requires verification.')) {
        window.location.href = `/admin/milestones/${milestoneId}/mark-paid`;
    }
}

function refreshData() {
    location.reload();
}
</script>
@endsection
