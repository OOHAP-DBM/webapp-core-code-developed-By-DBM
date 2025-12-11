@extends('layouts.admin')

@section('title', 'Revenue Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header with Period Filter -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0">💰 Revenue Dashboard</h1>
            <p class="text-muted mb-0">Comprehensive revenue analytics and insights</p>
        </div>
        <div class="d-flex gap-2">
            <select class="form-select" id="periodFilter" onchange="changePeriod(this.value)">
                <option value="today" {{ $period === 'today' ? 'selected' : '' }}>Today</option>
                <option value="yesterday" {{ $period === 'yesterday' ? 'selected' : '' }}>Yesterday</option>
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
                <option value="last_7_days" {{ $period === 'last_7_days' ? 'selected' : '' }}>Last 7 Days</option>
                <option value="last_30_days" {{ $period === 'last_30_days' ? 'selected' : '' }}>Last 30 Days</option>
            </select>
            <a href="{{ route('admin.revenue.export', ['period' => $period]) }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Key Metrics Cards -->
    <div class="row g-3 mb-4">
        <!-- Gross Revenue -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Gross Revenue</p>
                            <h3 class="mb-0">₹{{ number_format($stats['revenue']['gross_revenue'], 2) }}</h3>
                            @if(isset($dailySnapshots[count($dailySnapshots) - 1]->revenue_growth_percent))
                                <small class="text-{{ $dailySnapshots[count($dailySnapshots) - 1]->revenue_growth_percent >= 0 ? 'success' : 'danger' }}">
                                    <i class="bi bi-{{ $dailySnapshots[count($dailySnapshots) - 1]->revenue_growth_percent >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ abs($dailySnapshots[count($dailySnapshots) - 1]->revenue_growth_percent) }}%
                                </small>
                            @endif
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-currency-rupee text-primary fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Commission Earned -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Commission Earned</p>
                            <h3 class="mb-0">₹{{ number_format($stats['commissions']['total_earned'], 2) }}</h3>
                            <small class="text-muted">
                                Avg {{ number_format($stats['commissions']['average_rate'], 1) }}% rate
                            </small>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-percent text-success fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pending Payouts -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Pending Payouts</p>
                            <h3 class="mb-0">₹{{ number_format($stats['payouts']['pending_payouts'], 2) }}</h3>
                            <small class="text-warning">
                                {{ $stats['payouts']['pending_count'] }} requests
                            </small>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-hourglass-split text-warning fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Bookings -->
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Bookings</p>
                            <h3 class="mb-0">{{ number_format($stats['bookings']['total']) }}</h3>
                            <small class="text-success">
                                {{ $stats['bookings']['confirmed'] }} confirmed
                            </small>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-calendar-check text-info fs-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Paid Revenue</p>
                    <h5 class="mb-0">₹{{ number_format($stats['revenue']['paid_revenue'], 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Pending Revenue</p>
                    <h5 class="mb-0">₹{{ number_format($stats['revenue']['pending_revenue'], 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Tax Collected</p>
                    <h5 class="mb-0">₹{{ number_format($stats['revenue']['tax_collected'], 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Avg Booking</p>
                    <h5 class="mb-0">₹{{ number_format($stats['bookings']['average_value'], 0) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">Invoices</p>
                    <h5 class="mb-0">{{ $stats['revenue']['invoices_count'] }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <p class="text-muted mb-1 small">POS Bookings</p>
                    <h5 class="mb-0">{{ $stats['bookings']['pos_bookings'] }}</h5>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row g-3 mb-4">
        <!-- Revenue Trend Chart -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Revenue Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="revenueTrendChart" height="100"></canvas>
                </div>
            </div>
        </div>

        <!-- Bookings Trend Chart -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Bookings Trend</h5>
                </div>
                <div class="card-body">
                    <canvas id="bookingsTrendChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Payment Methods & Commission Distribution -->
    <div class="row g-3 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Revenue by Payment Method</h5>
                </div>
                <div class="card-body">
                    <canvas id="paymentMethodChart" height="150"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">Commission Rate Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="commissionDistributionChart" height="150"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Performers -->
    <div class="row g-3 mb-4">
        <!-- Top Vendors -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">🏆 Top Vendors</h5>
                    <a href="{{ route('admin.revenue.vendor-revenue', ['period' => $period]) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Vendor</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Avg Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topVendors as $index => $vendor)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : ($index === 2 ? 'danger' : 'light text-dark')) }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <div>
                                                <strong>{{ $vendor['name'] }}</strong>
                                                <br><small class="text-muted">{{ $vendor['email'] }}</small>
                                            </div>
                                        </td>
                                        <td>{{ $vendor['bookings_count'] }}</td>
                                        <td><strong>₹{{ number_format($vendor['total_revenue'], 0) }}</strong></td>
                                        <td>₹{{ number_format($vendor['average_value'], 0) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No vendor data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Top Locations -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">📍 Top Locations</h5>
                    <a href="{{ route('admin.revenue.location-revenue', ['period' => $period]) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Rank</th>
                                    <th>Location</th>
                                    <th>Bookings</th>
                                    <th>Revenue</th>
                                    <th>Vendors</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($topLocations as $index => $location)
                                    <tr>
                                        <td>
                                            <span class="badge bg-{{ $index === 0 ? 'warning' : ($index === 1 ? 'secondary' : ($index === 2 ? 'danger' : 'light text-dark')) }}">
                                                #{{ $index + 1 }}
                                            </span>
                                        </td>
                                        <td>
                                            <strong>{{ $location['city'] }}</strong>
                                            <br><small class="text-muted">{{ $location['state'] }}</small>
                                        </td>
                                        <td>{{ $location['bookings_count'] }}</td>
                                        <td><strong>₹{{ number_format($location['total_revenue'], 0) }}</strong></td>
                                        <td>{{ $location['unique_vendors'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">No location data available</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent High-Value Bookings -->
    <div class="row g-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <h5 class="mb-0">💎 Recent High-Value Bookings (₹50K+)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Booking ID</th>
                                    <th>Customer</th>
                                    <th>Vendor</th>
                                    <th>Location</th>
                                    <th>Amount</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($highValueBookings as $booking)
                                    <tr>
                                        <td><strong>#{{ $booking->id }}</strong></td>
                                        <td>{{ $booking->customer_name }}</td>
                                        <td>{{ $booking->vendor_name }}</td>
                                        <td>{{ $booking->city }} - {{ $booking->location_name }}</td>
                                        <td><strong class="text-success">₹{{ number_format($booking->total_amount, 2) }}</strong></td>
                                        <td>{{ \Carbon\Carbon::parse($booking->confirmed_at)->format('M d, Y H:i') }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">No high-value bookings found</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Revenue Trend Chart
    const revenueTrendCtx = document.getElementById('revenueTrendChart').getContext('2d');
    new Chart(revenueTrendCtx, {
        type: 'line',
        data: {
            labels: @json($trends['labels']),
            datasets: [{
                label: 'Revenue (₹)',
                data: @json($trends['revenue']),
                borderColor: 'rgb(75, 192, 192)',
                backgroundColor: 'rgba(75, 192, 192, 0.1)',
                tension: 0.4,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: true,
                    position: 'top'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Bookings Trend Chart
    const bookingsTrendCtx = document.getElementById('bookingsTrendChart').getContext('2d');
    new Chart(bookingsTrendCtx, {
        type: 'bar',
        data: {
            labels: @json($trends['labels']),
            datasets: [{
                label: 'Bookings',
                data: @json($trends['bookings']),
                backgroundColor: 'rgba(54, 162, 235, 0.6)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });

    // Payment Method Chart
    const paymentMethodCtx = document.getElementById('paymentMethodChart').getContext('2d');
    const paymentMethods = @json($paymentMethods);
    new Chart(paymentMethodCtx, {
        type: 'doughnut',
        data: {
            labels: paymentMethods.map(pm => pm.method),
            datasets: [{
                data: paymentMethods.map(pm => pm.amount),
                backgroundColor: [
                    'rgba(255, 99, 132, 0.6)',
                    'rgba(54, 162, 235, 0.6)',
                    'rgba(255, 206, 86, 0.6)',
                    'rgba(75, 192, 192, 0.6)',
                    'rgba(153, 102, 255, 0.6)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right'
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return context.label + ': ₹' + context.parsed.toLocaleString('en-IN');
                        }
                    }
                }
            }
        }
    });

    // Commission Distribution Chart
    const commissionDistCtx = document.getElementById('commissionDistributionChart').getContext('2d');
    const commissionDist = @json($commissionDistribution);
    new Chart(commissionDistCtx, {
        type: 'bar',
        data: {
            labels: commissionDist.map(cd => cd.range),
            datasets: [{
                label: 'Commission Earned',
                data: commissionDist.map(cd => cd.total),
                backgroundColor: 'rgba(75, 192, 192, 0.6)',
                borderColor: 'rgb(75, 192, 192)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return '₹' + context.parsed.y.toLocaleString('en-IN');
                        }
                    }
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '₹' + (value / 1000) + 'K';
                        }
                    }
                }
            }
        }
    });

    // Period filter change
    function changePeriod(period) {
        window.location.href = '{{ route('admin.revenue.dashboard') }}?period=' + period;
    }
</script>
@endpush
@endsection
