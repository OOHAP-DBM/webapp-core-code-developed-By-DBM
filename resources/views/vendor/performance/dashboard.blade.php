@extends('layouts.vendor')

@section('title', 'Performance Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="mb-1">Performance Dashboard</h2>
            <p class="text-muted">Track your vendor performance metrics and analytics</p>
        </div>
        <div class="col-md-4 text-end">
            <select id="periodFilter" class="form-select d-inline-block w-auto" onchange="changePeriod(this.value)">
                <option value="week" {{ $period === 'week' ? 'selected' : '' }}>This Week</option>
                <option value="month" {{ $period === 'month' ? 'selected' : '' }}>This Month</option>
                <option value="quarter" {{ $period === 'quarter' ? 'selected' : '' }}>This Quarter</option>
                <option value="year" {{ $period === 'year' ? 'selected' : '' }}>This Year</option>
            </select>
        </div>
    </div>

    <!-- Performance Insights -->
    @if(count($insights) > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-lightbulb me-2"></i>Performance Insights
                    </h5>
                    <div class="row g-3">
                        @foreach($insights as $insight)
                        <div class="col-md-6">
                            <div class="alert alert-{{ $insight['type'] }} mb-0 d-flex align-items-start">
                                <i class="bi bi-{{ $insight['icon'] }} me-2 mt-1"></i>
                                <span>{{ $insight['message'] }}</span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Key Metrics Cards -->
    <div class="row g-4 mb-4">
        <!-- Bookings Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Total Bookings</h6>
                            <h2 class="mb-0">{{ $performance['bookings']['total'] }}</h2>
                        </div>
                        <div class="p-3 bg-primary bg-opacity-10 rounded">
                            <i class="bi bi-calendar-check fs-4 text-primary"></i>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Confirmed</small>
                            <strong class="text-success">{{ $performance['bookings']['confirmed'] }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Cancelled</small>
                            <strong class="text-danger">{{ $performance['bookings']['cancelled'] }}</strong>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $performance['bookings']['total'] > 0 ? ($performance['bookings']['confirmed'] / $performance['bookings']['total'] * 100) : 0 }}%"></div>
                        <div class="progress-bar bg-danger" style="width: {{ $performance['bookings']['cancellation_rate'] }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ number_format($performance['bookings']['cancellation_rate'], 1) }}% cancellation rate
                    </small>
                </div>
            </div>
        </div>

        <!-- Conversion Rate Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Conversion Rate</h6>
                            <h2 class="mb-0">{{ number_format($performance['enquiries']['enquiry_to_booking_ratio'], 1) }}%</h2>
                        </div>
                        <div class="p-3 bg-success bg-opacity-10 rounded">
                            <i class="bi bi-graph-up-arrow fs-4 text-success"></i>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Enquiries</small>
                            <strong>{{ $performance['enquiries']['total_enquiries'] }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Conversions</small>
                            <strong class="text-success">{{ $performance['enquiries']['converted_to_bookings'] }}</strong>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $performance['enquiries']['enquiry_to_booking_ratio'] }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ $performance['enquiries']['pending_responses'] }} pending responses
                    </small>
                </div>
            </div>
        </div>

        <!-- Response Time Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Avg Response Time</h6>
                            <h2 class="mb-0">{{ $performance['response_time']['avg_response_time_formatted'] }}</h2>
                        </div>
                        <div class="p-3 bg-info bg-opacity-10 rounded">
                            <i class="bi bi-clock-history fs-4 text-info"></i>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">24h Compliance</small>
                            <strong class="text-success">{{ number_format($performance['response_time']['compliance_24h_percent'], 0) }}%</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Median Time</small>
                            <strong>{{ App\Services\VendorPerformanceService::formatMinutes($performance['response_time']['median_response_time']) }}</strong>
                        </div>
                    </div>
                    <div class="progress" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $performance['response_time']['compliance_24h_percent'] }}%"></div>
                    </div>
                    <small class="text-muted mt-2 d-block">
                        {{ $performance['response_time']['within_24h'] }} of {{ $performance['response_time']['total_responses'] }} within 24h
                    </small>
                </div>
            </div>
        </div>

        <!-- SLA Score Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">SLA Score</h6>
                            <h2 class="mb-0">{{ number_format($performance['sla']['current_score'], 1) }}</h2>
                        </div>
                        <div class="p-3 bg-warning bg-opacity-10 rounded">
                            <i class="bi bi-shield-check fs-4 text-warning"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-{{ $performance['sla']['score_status'] === 'excellent' ? 'success' : ($performance['sla']['score_status'] === 'good' ? 'primary' : ($performance['sla']['score_status'] === 'fair' ? 'warning' : 'danger')) }} px-3 py-2">
                            {{ ucfirst($performance['sla']['score_status']) }}
                        </span>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Violations</small>
                            <strong class="text-danger">{{ $performance['sla']['total_violations'] }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Compliance</small>
                            <strong class="text-success">{{ number_format($performance['sla']['compliance_rate'], 0) }}%</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rating Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Average Rating</h6>
                            <h2 class="mb-0">{{ number_format($performance['ratings']['average_rating'], 2) }} <small class="text-muted fs-6">/5.0</small></h2>
                        </div>
                        <div class="p-3 bg-danger bg-opacity-10 rounded">
                            <i class="bi bi-star-fill fs-4 text-danger"></i>
                        </div>
                    </div>
                    <div class="mb-3">
                        <span class="badge bg-{{ $performance['ratings']['rating_status'] === 'excellent' ? 'success' : ($performance['ratings']['rating_status'] === 'good' ? 'primary' : 'warning') }} px-3 py-2">
                            {{ ucfirst($performance['ratings']['rating_status']) }}
                        </span>
                    </div>
                    <div class="mb-2">
                        @for($i = 5; $i >= 1; $i--)
                        <div class="d-flex align-items-center mb-1">
                            <span class="text-warning me-2">{{ $i }}<i class="bi bi-star-fill ms-1"></i></span>
                            <div class="progress flex-grow-1" style="height: 6px;">
                                <div class="progress-bar bg-warning" style="width: {{ $performance['ratings']['rating_distribution'][$i . '_star'] ?? 0 }}%"></div>
                            </div>
                            <small class="text-muted ms-2">{{ number_format($performance['ratings']['rating_distribution'][$i . '_star'] ?? 0, 0) }}%</small>
                        </div>
                        @endfor
                    </div>
                    <small class="text-muted">Based on {{ $performance['ratings']['total_ratings'] }} ratings</small>
                </div>
            </div>
        </div>

        <!-- Disputes Card -->
        <div class="col-xl-4 col-md-6">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <h6 class="text-muted mb-1">Disputes</h6>
                            <h2 class="mb-0">{{ $performance['disputes']['total_disputes'] }}</h2>
                        </div>
                        <div class="p-3 bg-secondary bg-opacity-10 rounded">
                            <i class="bi bi-exclamation-triangle fs-4 text-secondary"></i>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <small class="text-muted d-block">Open</small>
                            <strong class="text-danger">{{ $performance['disputes']['open_disputes'] }}</strong>
                        </div>
                        <div class="col-6">
                            <small class="text-muted d-block">Resolved</small>
                            <strong class="text-success">{{ $performance['disputes']['resolved_disputes'] }}</strong>
                        </div>
                    </div>
                    <div class="progress mb-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: {{ $performance['disputes']['resolution_rate'] }}%"></div>
                    </div>
                    <small class="text-muted d-block">
                        {{ number_format($performance['disputes']['resolution_rate'], 0) }}% resolution rate
                    </small>
                    @if($performance['disputes']['vendor_favor_rate'] > 0)
                    <small class="text-success d-block mt-1">
                        <i class="bi bi-check-circle"></i> {{ number_format($performance['disputes']['vendor_favor_rate'], 0) }}% in your favor
                    </small>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4 mb-4">
        <!-- Bookings Trend Chart -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-graph-up me-2"></i>Bookings & Revenue Trend
                    </h5>
                    <canvas id="bookingsTrendChart" height="80"></canvas>
                </div>
            </div>
        </div>

        <!-- Rating Distribution Chart -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-star-fill me-2"></i>Rating Distribution
                    </h5>
                    <canvas id="ratingDistributionChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Conversion Funnel & Response Time Charts -->
    <div class="row g-4 mb-4">
        <!-- Conversion Funnel -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-funnel me-2"></i>Enquiry Conversion Funnel
                    </h5>
                    <canvas id="conversionFunnelChart" height="120"></canvas>
                </div>
            </div>
        </div>

        <!-- Response Time Compliance -->
        <div class="col-xl-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-4">
                        <i class="bi bi-clock-history me-2"></i>Response Time Compliance
                    </h5>
                    <div class="text-center mb-4">
                        <canvas id="responseComplianceChart" height="120"></canvas>
                    </div>
                    <div class="row g-3 text-center">
                        <div class="col-4">
                            <small class="text-muted d-block">Within 6h</small>
                            <strong class="text-success d-block">{{ $performance['response_time']['within_6h'] ?? 0 }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Within 12h</small>
                            <strong class="text-info d-block">{{ $performance['response_time']['within_12h'] ?? 0 }}</strong>
                        </div>
                        <div class="col-4">
                            <small class="text-muted d-block">Within 24h</small>
                            <strong class="text-warning d-block">{{ $performance['response_time']['within_24h'] }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity Section -->
    <div class="row g-4">
        <!-- Pending Enquiries -->
        @if(count($pendingEnquiries) > 0)
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-hourglass-split me-2"></i>Pending Enquiries
                        <span class="badge bg-warning ms-2">{{ count($pendingEnquiries) }}</span>
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($pendingEnquiries, 0, 5) as $enquiry)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="d-block">{{ $enquiry->customer_name }}</strong>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($enquiry->created_at)->diffForHumans() }}</small>
                                </div>
                                @if($enquiry->hours_waiting >= 24)
                                <span class="badge bg-danger">{{ $enquiry->hours_waiting }}h</span>
                                @else
                                <span class="badge bg-warning">{{ $enquiry->hours_waiting }}h</span>
                                @endif
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('vendor.performance.enquiries') }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                        View All Enquiries
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Ratings -->
        @if(count($recentRatings) > 0)
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-star me-2"></i>Recent Ratings
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($recentRatings, 0, 5) as $rating)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <strong>{{ $rating->customer_name }}</strong>
                                <div>
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi bi-star{{ $i <= $rating->customer_rating ? '-fill' : '' }} text-warning"></i>
                                    @endfor
                                </div>
                            </div>
                            @if($rating->customer_feedback)
                            <p class="text-muted small mb-1">{{ Str::limit($rating->customer_feedback, 80) }}</p>
                            @endif
                            <small class="text-muted">{{ \Carbon\Carbon::parse($rating->rated_at)->diffForHumans() }}</small>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('vendor.performance.ratings') }}" class="btn btn-sm btn-outline-primary w-100 mt-3">
                        View All Ratings
                    </a>
                </div>
            </div>
        </div>
        @endif

        <!-- Recent Disputes -->
        @if(count($recentDisputes) > 0)
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">
                        <i class="bi bi-exclamation-triangle me-2"></i>Open Disputes
                        <span class="badge bg-danger ms-2">{{ count($recentDisputes) }}</span>
                    </h5>
                    <div class="list-group list-group-flush">
                        @foreach(array_slice($recentDisputes, 0, 5) as $dispute)
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <strong class="d-block">{{ str_replace('_', ' ', ucwords($dispute->dispute_type)) }}</strong>
                                    <small class="text-muted d-block">{{ $dispute->customer_name }}</small>
                                    <small class="text-muted">{{ \Carbon\Carbon::parse($dispute->disputed_at)->diffForHumans() }}</small>
                                </div>
                                <span class="badge bg-{{ $dispute->status === 'open' ? 'danger' : 'warning' }}">
                                    {{ ucfirst($dispute->status) }}
                                </span>
                            </div>
                        </div>
                        @endforeach
                    </div>
                    <a href="{{ route('vendor.performance.disputes') }}" class="btn btn-sm btn-outline-danger w-100 mt-3">
                        Manage Disputes
                    </a>
                </div>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection

@push('styles')
<style>
    .card {
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1) !important;
    }
    
    .progress {
        border-radius: 10px;
        overflow: hidden;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
    
    .list-group-item:last-child {
        border-bottom: none;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // Bookings & Revenue Trend Chart
    const bookingsTrendCtx = document.getElementById('bookingsTrendChart').getContext('2d');
    new Chart(bookingsTrendCtx, {
        type: 'line',
        data: {
            labels: {!! json_encode($performance['trends']['labels']) !!},
            datasets: [
                {
                    label: 'Bookings',
                    data: {!! json_encode($performance['trends']['bookings']) !!},
                    borderColor: 'rgb(13, 110, 253)',
                    backgroundColor: 'rgba(13, 110, 253, 0.1)',
                    yAxisID: 'y',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Revenue (₹)',
                    data: {!! json_encode($performance['trends']['revenue']) !!},
                    borderColor: 'rgb(25, 135, 84)',
                    backgroundColor: 'rgba(25, 135, 84, 0.1)',
                    yAxisID: 'y1',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: true,
                    position: 'top',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.dataset.label || '';
                            if (label) {
                                label += ': ';
                            }
                            if (context.parsed.y !== null) {
                                if (context.datasetIndex === 1) {
                                    label += '₹' + context.parsed.y.toLocaleString('en-IN');
                                } else {
                                    label += context.parsed.y;
                                }
                            }
                            return label;
                        }
                    }
                }
            },
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Bookings'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Revenue (₹)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            }
        }
    });

    // Rating Distribution Chart
    const ratingDistCtx = document.getElementById('ratingDistributionChart').getContext('2d');
    new Chart(ratingDistCtx, {
        type: 'doughnut',
        data: {
            labels: ['5 Stars', '4 Stars', '3 Stars', '2 Stars', '1 Star'],
            datasets: [{
                data: [
                    {{ $performance['ratings']['five_star_ratings'] }},
                    {{ $performance['ratings']['four_star_ratings'] }},
                    {{ $performance['ratings']['three_star_ratings'] }},
                    {{ $performance['ratings']['two_star_ratings'] }},
                    {{ $performance['ratings']['one_star_ratings'] }}
                ],
                backgroundColor: [
                    'rgb(25, 135, 84)',
                    'rgb(13, 202, 240)',
                    'rgb(255, 193, 7)',
                    'rgb(253, 126, 20)',
                    'rgb(220, 53, 69)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Conversion Funnel Chart
    const conversionFunnelCtx = document.getElementById('conversionFunnelChart').getContext('2d');
    new Chart(conversionFunnelCtx, {
        type: 'bar',
        data: {
            labels: ['Total Enquiries', 'Quotes Sent', 'Quotes Accepted', 'Converted to Bookings'],
            datasets: [{
                label: 'Count',
                data: [
                    {{ $performance['enquiries']['total_enquiries'] }},
                    {{ $performance['enquiries']['quotations_sent'] }},
                    {{ $performance['enquiries']['quotations_accepted'] }},
                    {{ $performance['enquiries']['converted_to_bookings'] }}
                ],
                backgroundColor: [
                    'rgba(13, 110, 253, 0.8)',
                    'rgba(13, 202, 240, 0.8)',
                    'rgba(25, 135, 84, 0.8)',
                    'rgba(40, 167, 69, 0.8)'
                ]
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        afterLabel: function(context) {
                            if (context.dataIndex === 0) return '';
                            const total = context.chart.data.datasets[0].data[0];
                            const percentage = ((context.parsed.x / total) * 100).toFixed(1);
                            return percentage + '% of total enquiries';
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true
                }
            }
        }
    });

    // Response Time Compliance Chart
    const responseComplianceCtx = document.getElementById('responseComplianceChart').getContext('2d');
    new Chart(responseComplianceCtx, {
        type: 'doughnut',
        data: {
            labels: ['Within 24h', 'Over 24h'],
            datasets: [{
                data: [
                    {{ $performance['response_time']['within_24h'] }},
                    {{ $performance['response_time']['total_responses'] - $performance['response_time']['within_24h'] }}
                ],
                backgroundColor: [
                    'rgb(25, 135, 84)',
                    'rgb(220, 53, 69)'
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: true,
            plugins: {
                legend: {
                    position: 'bottom',
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                            return label + ': ' + value + ' (' + percentage + '%)';
                        }
                    }
                }
            }
        }
    });

    // Period Filter Change
    function changePeriod(period) {
        window.location.href = '{{ route("vendor.performance.dashboard") }}?period=' + period;
    }
</script>
@endpush
