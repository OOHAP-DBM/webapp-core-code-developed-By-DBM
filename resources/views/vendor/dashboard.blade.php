@extends('layouts.vendor')

@section('page-title', 'Dashboard')

@section('content')
@php
    $vendorProfile = auth()->user()->vendorProfile ?? null;
    $pendingStatuses = ['pending_approval', 'draft'];
@endphp

@if(session('status') === 'pending' || ($vendorProfile && in_array($vendorProfile->onboarding_status, $pendingStatuses)))
    <div class="alert alert-warning" style="background: #ffe6e6; color: #333; border-radius: 8px; display: flex; align-items: center; gap: 12px; margin-bottom: 1.5rem;">
        <img src="/images/hourglass.svg" style="height: 32px;">
        <div>
            <strong>Your Vendor Request is Pending!</strong><br>
            <span>Once approved by the admin, you can access the features of OOHAPP Vendor</span>
        </div>
    </div>
@endif
<div class="row g-4 mb-4">
    <!-- Revenue Card -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                <i class="bi bi-currency-rupee"></i>
            </div>
            <div class="stat-label">Total Revenue</div>
            <div class="stat-value">₹{{ number_format($stats['total_revenue'] ?? 0, 0) }}</div>
            <div class="stat-change text-success">
                <i class="bi bi-arrow-up"></i> {{ $stats['revenue_change'] ?? '+12.5' }}% from last month
            </div>
        </div>
    </div>

    <!-- Active Bookings Card -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                <i class="bi bi-calendar-check"></i>
            </div>
            <div class="stat-label">Active Bookings</div>
            <div class="stat-value">{{ $stats['active_bookings'] ?? 0 }}</div>
            <div class="stat-change text-success">
                <i class="bi bi-arrow-up"></i> {{ $stats['bookings_change'] ?? '+8' }} new this week
            </div>
        </div>
    </div>

    <!-- Total Listings Card -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                <i class="bi bi-grid-3x3"></i>
            </div>
            <div class="stat-label">Total Listings</div>
            <div class="stat-value">{{ $stats['total_listings'] ?? 0 }}</div>
            <div class="stat-change text-muted">
                {{ $stats['available_listings'] ?? 0 }} available
            </div>
        </div>
    </div>

    <!-- Pending Tasks Card -->
    <div class="col-xl-3 col-md-6">
        <div class="stat-card">
            <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                <i class="bi bi-exclamation-triangle"></i>
            </div>
            <div class="stat-label">Pending Tasks</div>
            <div class="stat-value">{{ $stats['pending_tasks'] ?? 0 }}</div>
            <div class="stat-change text-danger">
                {{ $stats['overdue_tasks'] ?? 0 }} overdue
            </div>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Revenue Chart -->
    <div class="col-xl-8">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Revenue Overview</h5>
                <div class="btn-group btn-group-sm">
                    <button class="btn btn-outline-secondary active">Week</button>
                    <button class="btn btn-outline-secondary">Month</button>
                    <button class="btn btn-outline-secondary">Year</button>
                </div>
            </div>
            <div class="vendor-card-body">
                <canvas id="revenueChart" height="80"></canvas>
            </div>
        </div>
    </div>

    <!-- Booking Status -->
    <div class="col-xl-4">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Booking Status</h5>
            </div>
            <div class="vendor-card-body">
                <canvas id="bookingStatusChart" height="200"></canvas>
                
                <div class="mt-4">
                    @foreach([
                        ['label' => 'Confirmed', 'count' => $stats['confirmed_bookings'] ?? 0, 'color' => '#10b981'],
                        ['label' => 'Pending', 'count' => $stats['pending_bookings'] ?? 0, 'color' => '#f59e0b'],
                        ['label' => 'Completed', 'count' => $stats['completed_bookings'] ?? 0, 'color' => '#3b82f6'],
                        ['label' => 'Cancelled', 'count' => $stats['cancelled_bookings'] ?? 0, 'color' => '#ef4444']
                    ] as $status)
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-center">
                                <div style="width: 12px; height: 12px; border-radius: 2px; background: {{ $status['color'] }}; margin-right: 8px;"></div>
                                <span class="text-muted small">{{ $status['label'] }}</span>
                            </div>
                            <strong>{{ $status['count'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mt-4">
    <!-- Recent Bookings -->
    <div class="col-xl-7">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Recent Bookings</h5>
                <a href="{{ route('vendor.bookings.index') }}" class="btn btn-sm btn-vendor-primary">View All</a>
            </div>
            <div class="vendor-card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Booking ID</th>
                                <th>Hoarding</th>
                                <th>Customer</th>
                                <th>Duration</th>
                                <th>Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBookings ?? [] as $booking)
                                <tr>
                                    <td><strong>#{{ $booking->id }}</strong></td>
                                    <td>{{ $booking->hoarding->title ?? 'N/A' }}</td>
                                    <td>{{ $booking->customer->name ?? 'N/A' }}</td>
                                    <td>{{ \Carbon\Carbon::parse($booking->start_date)->format('d M') }} - {{ \Carbon\Carbon::parse($booking->end_date)->format('d M') }}</td>
                                    <td><strong>₹{{ number_format($booking->total_amount ?? 0, 0) }}</strong></td>
                                    <td>
                                        <span class="badge 
                                            @if($booking->status === 'confirmed') bg-success
                                            @elseif($booking->status === 'pending') bg-warning
                                            @elseif($booking->status === 'completed') bg-primary
                                            @else bg-danger
                                            @endif">
                                            {{ ucfirst($booking->status) }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">No recent bookings</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Tasks -->
    <div class="col-xl-5">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Pending Tasks</h5>
                <a href="{{ route('vendor.tasks.index') }}" class="btn btn-sm btn-vendor-primary">View All</a>
            </div>
            <div class="vendor-card-body">
                @forelse($pendingTasks ?? [] as $task)
                    <div class="d-flex align-items-start mb-3 pb-3 border-bottom">
                        <div class="form-check me-3">
                            <input class="form-check-input" type="checkbox" id="task{{ $task->id }}">
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <h6 class="mb-0">{{ $task->title }}</h6>
                                <span class="badge 
                                    @if($task->priority === 'high') bg-danger
                                    @elseif($task->priority === 'medium') bg-warning
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($task->priority) }}
                                </span>
                            </div>
                            <p class="text-muted small mb-1">{{ $task->description }}</p>
                            <div class="d-flex align-items-center text-muted small">
                                <i class="bi bi-calendar me-1"></i>
                                <span>Due: {{ \Carbon\Carbon::parse($task->due_date)->format('d M Y') }}</span>
                                <span class="mx-2">•</span>
                                <i class="bi bi-tag me-1"></i>
                                <span>{{ ucfirst($task->type) }}</span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle" style="font-size: 3rem;"></i>
                        <p class="mt-2 mb-0">No pending tasks</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="row g-4 mt-4">
    <div class="col-12">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h5 class="vendor-card-title">Quick Actions</h5>
            </div>
            <div class="vendor-card-body">
                <div class="row g-3">

                    <div class="col-md-3">
                        @if($vendorProfile && $vendorProfile->onboarding_status === 'approved')
                            <a href="{{ route('vendor.listings.create', ['type' => 'ooh']) }}" class="btn btn-outline-primary w-100 py-3">
                                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Add New Listing
                            </a>
                        @else
                            <button type="button" class="btn btn-outline-primary w-100 py-3" onclick="showPendingModal()">
                                <i class="bi bi-plus-circle d-block mb-2" style="font-size: 2rem;"></i>
                                Add New Listing
                            </button>
                        @endif
                    </div>

                    <div class="col-md-3">
                        <a href="{{ route('vendor.bookings.index') }}" class="btn btn-outline-success w-100 py-3">
                            <i class="bi bi-calendar-check d-block mb-2" style="font-size: 2rem;"></i>
                            Manage Bookings
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('vendor.tasks.index') }}" class="btn btn-outline-warning w-100 py-3">
                            <i class="bi bi-list-check d-block mb-2" style="font-size: 2rem;"></i>
                            View Tasks
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="{{ route('vendor.payouts.index') }}" class="btn btn-outline-info w-100 py-3">
                            <i class="bi bi-cash-stack d-block mb-2" style="font-size: 2rem;"></i>
                            Check Payouts
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Pending Modal -->
<div id="pendingModal" style="display:none; position:fixed; z-index:9999; left:0; top:0; width:100vw; height:100vh; background:rgba(0,0,0,0.3);">
    <div style="background:#fff; max-width:400px; margin:10% auto; padding:2rem; border-radius:8px; text-align:center;">
        <img src="/images/billboard.svg" alt="Pending" style="height: 60px;">
        <h2 style="font-size:1.3rem; margin-top:1rem;">Your Vendor Request is Pending!</h2>
        <p style="margin-bottom:1.5rem;">Once Approved you will become a OOHAPP Vendor</p>
        <button onclick="closePendingModal()" class="btn btn-dark mt-3">Done</button>
    </div>
</div>

@push('scripts')
<script>
function showPendingModal() {
    document.getElementById('pendingModal').style.display = 'block';
}
function closePendingModal() {
    document.getElementById('pendingModal').style.display = 'none';
}
</script>
@endpush

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Revenue Chart
const revenueCtx = document.getElementById('revenueChart');
if (revenueCtx) {
    new Chart(revenueCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($revenueChartLabels ?? ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']) !!},
            datasets: [{
                label: 'Revenue (₹)',
                data: {!! json_encode($revenueChartData ?? [12000, 19000, 15000, 25000, 22000, 30000, 28000]) !!},
                borderColor: '#2563eb',
                backgroundColor: 'rgba(37, 99, 235, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            },
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
}

// Booking Status Chart
const statusCtx = document.getElementById('bookingStatusChart');
if (statusCtx) {
    new Chart(statusCtx, {
        type: 'doughnut',
        data: {
            labels: ['Confirmed', 'Pending', 'Completed', 'Cancelled'],
            datasets: [{
                data: [
                    {{ $stats['confirmed_bookings'] ?? 0 }},
                    {{ $stats['pending_bookings'] ?? 0 }},
                    {{ $stats['completed_bookings'] ?? 0 }},
                    {{ $stats['cancelled_bookings'] ?? 0 }}
                ],
                backgroundColor: ['#10b981', '#f59e0b', '#3b82f6', '#ef4444']
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                }
            }
        }
    });
}
</script>
@endpush
