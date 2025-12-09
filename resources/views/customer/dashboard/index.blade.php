@extends('layouts.customer')

@section('title', 'My Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Dashboard</h1>
            <p class="text-muted">Welcome back, {{ auth()->user()->name }}!</p>
        </div>
        <div>
            <form action="{{ route('customer.my.refresh-stats') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-outline-primary">
                    <i class="bi bi-arrow-clockwise"></i> Refresh Stats
                </button>
            </form>
            <small class="text-muted d-block mt-1">
                Last updated: {{ $stats->last_calculated_at ? $stats->last_calculated_at->diffForHumans() : 'Never' }}
            </small>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- Bookings Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Bookings</p>
                            <h3 class="mb-0">{{ $stats->total_bookings }}</h3>
                            <small class="text-success">
                                <i class="bi bi-check-circle"></i> {{ $stats->active_bookings }} active
                            </small>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-calendar-check text-primary fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-primary" role="progressbar" 
                                 style="width: {{ $stats->booking_completion_rate }}%" 
                                 aria-valuenow="{{ $stats->booking_completion_rate }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $stats->booking_completion_rate }}% completion rate</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('customer.my.bookings') }}" class="btn btn-sm btn-outline-primary w-100">
                        View All Bookings
                    </a>
                </div>
            </div>
        </div>

        <!-- Payments Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Total Paid</p>
                            <h3 class="mb-0">₹{{ number_format($stats->total_paid, 2) }}</h3>
                            <small class="text-warning">
                                <i class="bi bi-clock"></i> ₹{{ number_format($stats->total_pending, 2) }} pending
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-wallet2 text-success fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-success" role="progressbar" 
                                 style="width: {{ $stats->payment_completion_rate }}%" 
                                 aria-valuenow="{{ $stats->payment_completion_rate }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $stats->payment_completion_rate }}% paid</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('customer.my.payments') }}" class="btn btn-sm btn-outline-success w-100">
                        View All Payments
                    </a>
                </div>
            </div>
        </div>

        <!-- Enquiries Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Enquiries</p>
                            <h3 class="mb-0">{{ $stats->total_enquiries }}</h3>
                            <small class="text-info">
                                <i class="bi bi-envelope"></i> {{ $stats->pending_enquiries }} pending
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-question-circle text-info fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-info" role="progressbar" 
                                 style="width: {{ $stats->enquiry_response_rate }}%" 
                                 aria-valuenow="{{ $stats->enquiry_response_rate }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $stats->enquiry_response_rate }}% response rate</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('customer.my.enquiries') }}" class="btn btn-sm btn-outline-info w-100">
                        View All Enquiries
                    </a>
                </div>
            </div>
        </div>

        <!-- Invoices Card -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1">Invoices</p>
                            <h3 class="mb-0">{{ $stats->total_invoices }}</h3>
                            <small class="text-danger">
                                <i class="bi bi-exclamation-circle"></i> {{ $stats->unpaid_invoices }} unpaid
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-file-earmark-text text-warning fs-4"></i>
                        </div>
                    </div>
                    <div class="mt-3">
                        <div class="progress" style="height: 5px;">
                            <div class="progress-bar bg-warning" role="progressbar" 
                                 style="width: {{ $stats->invoice_payment_rate }}%" 
                                 aria-valuenow="{{ $stats->invoice_payment_rate }}" 
                                 aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small class="text-muted">{{ $stats->invoice_payment_rate }}% paid</small>
                    </div>
                </div>
                <div class="card-footer bg-transparent border-0">
                    <a href="{{ route('customer.my.invoices') }}" class="btn btn-sm btn-outline-warning w-100">
                        View All Invoices
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats Row -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Offers</h6>
                    <h4 class="mb-0">{{ $stats->total_offers }}</h4>
                    <small class="text-muted">{{ $stats->active_offers }} active</small>
                    <a href="{{ route('customer.my.offers') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Quotations</h6>
                    <h4 class="mb-0">{{ $stats->total_quotations }}</h4>
                    <small class="text-muted">{{ $stats->pending_quotations }} pending</small>
                    <a href="{{ route('customer.my.quotations') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Messages</h6>
                    <h4 class="mb-0">{{ $stats->total_threads }}</h4>
                    <small class="text-muted">{{ $stats->unread_threads }} unread</small>
                    <a href="{{ route('customer.my.threads') }}" class="stretched-link"></a>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <!-- Booking Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Booking Trends</h5>
                    <small class="text-muted">Monthly bookings over time</small>
                </div>
                <div class="card-body">
                    <canvas id="bookingChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Spending Summary -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Spending Summary</h5>
                    <small class="text-muted">By hoarding type</small>
                </div>
                <div class="card-body">
                    <canvas id="spendingChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mt-3">
        <!-- Upcoming Bookings -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Upcoming Bookings</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($upcomingBookings as $booking)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">{{ $booking->hoarding->title ?? 'N/A' }}</h6>
                                <small class="text-muted">
                                    <i class="bi bi-calendar"></i> {{ $booking->start_date->format('M d, Y') }} - {{ $booking->end_date->format('M d, Y') }}
                                </small>
                            </div>
                            <span class="badge bg-primary">{{ ucfirst($booking->status) }}</span>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                        No upcoming bookings
                    </div>
                    @endforelse
                </div>
                @if($upcomingBookings->count() > 0)
                <div class="card-footer bg-white border-0">
                    <a href="{{ route('customer.my.bookings') }}" class="btn btn-sm btn-outline-primary w-100">
                        View All Bookings
                    </a>
                </div>
                @endif
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div class="card-body p-0">
                    @forelse($recentActivities as $activity)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start">
                            <div>
                                <h6 class="mb-1">
                                    @if($activity['type'] == 'booking')
                                    <i class="bi bi-calendar-check text-primary"></i>
                                    @elseif($activity['type'] == 'enquiry')
                                    <i class="bi bi-question-circle text-info"></i>
                                    @endif
                                    {{ $activity['title'] }}
                                </h6>
                                <small class="text-muted">
                                    {{ $activity['date']->diffForHumans() }}
                                </small>
                            </div>
                            <span class="badge bg-{{ $activity['status'] == 'completed' ? 'success' : ($activity['status'] == 'pending' ? 'warning' : 'secondary') }}">
                                {{ ucfirst($activity['status']) }}
                            </span>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="bi bi-clock-history fs-3 d-block mb-2"></i>
                        No recent activity
                    </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Payments Alert -->
    @if($pendingPayments->count() > 0)
    <div class="alert alert-warning mt-4" role="alert">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle fs-4 me-3"></i>
            <div class="flex-grow-1">
                <h6 class="alert-heading mb-1">Pending Payments</h6>
                <p class="mb-0">You have {{ $pendingPayments->count() }} pending payment(s). Please complete them to avoid booking cancellations.</p>
            </div>
            <a href="{{ route('customer.my.payments') }}" class="btn btn-warning btn-sm">View Payments</a>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// Booking Trend Chart
const bookingCtx = document.getElementById('bookingChart').getContext('2d');
new Chart(bookingCtx, {
    type: 'line',
    data: {
        labels: @json($bookingChart['labels']),
        datasets: [{
            label: 'Bookings',
            data: @json($bookingChart['bookings']),
            borderColor: '#0d6efd',
            backgroundColor: 'rgba(13, 110, 253, 0.1)',
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
                    precision: 0
                }
            }
        }
    }
});

// Spending Summary Chart
const spendingCtx = document.getElementById('spendingChart').getContext('2d');
new Chart(spendingCtx, {
    type: 'doughnut',
    data: {
        labels: @json($spendingSummary['categories']),
        datasets: [{
            data: @json($spendingSummary['spending']),
            backgroundColor: [
                '#0d6efd',
                '#198754',
                '#ffc107',
                '#dc3545',
                '#0dcaf0',
                '#6c757d'
            ]
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: true,
        plugins: {
            legend: {
                position: 'bottom'
            }
        }
    }
});
</script>
@endpush
@endsection
