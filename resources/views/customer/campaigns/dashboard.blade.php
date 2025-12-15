@extends('layouts.customer')

@section('title', 'Campaign Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">My Campaigns</h1>
            <p class="text-muted">Manage and track all your advertising campaigns</p>
        </div>
        <div>
            <a href="{{ route('customer.campaigns.export') }}" class="btn btn-outline-primary">
                <i class="fas fa-download"></i> Export
            </a>
            <a href="{{ route('hoardings.index') }}" class="btn btn-primary">
                <i class="fas fa-plus"></i> New Campaign
            </a>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Active Campaigns</p>
                            <h3 class="mb-0">{{ $stats['active_campaigns'] }}</h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-2 rounded">
                            <i class="fas fa-play-circle text-success fs-4"></i>
                        </div>
                    </div>
                    <small class="text-muted">Currently running</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Upcoming</p>
                            <h3 class="mb-0">{{ $stats['upcoming_campaigns'] }}</h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-2 rounded">
                            <i class="fas fa-clock text-info fs-4"></i>
                        </div>
                    </div>
                    <small class="text-muted">Starting soon</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Active Hoardings</p>
                            <h3 class="mb-0">{{ $stats['active_hoardings'] }}</h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-2 rounded">
                            <i class="fas fa-rectangle-ad text-primary fs-4"></i>
                        </div>
                    </div>
                    <small class="text-muted">Live locations</small>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <p class="text-muted mb-1 small">Total Spend</p>
                            <h3 class="mb-0">₹{{ number_format($stats['total_spend']) }}</h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-2 rounded">
                            <i class="fas fa-wallet text-warning fs-4"></i>
                        </div>
                    </div>
                    <small class="text-muted">
                        @if($stats['pending_payments'] > 0)
                            <span class="text-danger">₹{{ number_format($stats['pending_payments']) }} pending</span>
                        @else
                            All paid
                        @endif
                    </small>
                </div>
            </div>
        </div>
    </div>

    <!-- Pending Actions -->
    @if(count($pending_actions) > 0)
    <div class="alert alert-warning mb-4">
        <div class="d-flex align-items-center mb-2">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>Action Required</strong>
        </div>
        <ul class="mb-0">
            @foreach($pending_actions as $action)
                <li>
                    <a href="{{ $action['action_url'] }}" class="text-decoration-none">
                        {{ $action['message'] }}
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
    @endif

    <!-- Active Campaigns -->
    @if(count($active_campaigns) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-play-circle text-success me-2"></i>Active Campaigns</h5>
                <a href="{{ route('customer.campaigns.index', ['status' => 'active']) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Campaign</th>
                            <th>Location</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Progress</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($active_campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($campaign['hoarding']['image_url'])
                                        <img src="{{ $campaign['hoarding']['image_url'] }}" 
                                             alt="{{ $campaign['hoarding']['title'] }}"
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $campaign['hoarding']['title'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $campaign['booking_id'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $campaign['hoarding']['location'] }}<br>
                                <small class="text-muted">{{ $campaign['hoarding']['city'] }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($campaign['dates']['start'])->format('M d') }} - 
                                {{ \Carbon\Carbon::parse($campaign['dates']['end'])->format('M d, Y') }}
                                <br>
                                <small class="text-muted">
                                    @if($campaign['dates']['days_remaining'] > 0)
                                        {{ abs($campaign['dates']['days_remaining']) }} days remaining
                                    @elseif($campaign['dates']['days_remaining'] === 0)
                                        Ends today
                                    @else
                                        Ended {{ abs($campaign['dates']['days_remaining']) }} days ago
                                    @endif
                                </small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $campaign['status_color'] }}">
                                    {{ $campaign['status_label'] }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $totalDays = $campaign['dates']['duration_days'];
                                    $elapsed = $totalDays - abs($campaign['dates']['days_remaining']);
                                    $progress = $totalDays > 0 ? ($elapsed / $totalDays) * 100 : 0;
                                @endphp
                                <div class="progress" style="height: 20px;">
                                    <div class="progress-bar" 
                                         role="progressbar" 
                                         style="width: {{ min($progress, 100) }}%"
                                         aria-valuenow="{{ $progress }}" 
                                         aria-valuemin="0" 
                                         aria-valuemax="100">
                                        {{ number_format($progress, 0) }}%
                                    </div>
                                </div>
                            </td>
                            <td>
                                <a href="{{ route('customer.campaigns.show', $campaign['id']) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Upcoming Campaigns -->
    @if(count($upcoming_campaigns) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-clock text-info me-2"></i>Upcoming Campaigns</h5>
                <a href="{{ route('customer.campaigns.index', ['status' => 'upcoming']) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Campaign</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($upcoming_campaigns as $campaign)
                        <tr>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($campaign['hoarding']['image_url'])
                                        <img src="{{ $campaign['hoarding']['image_url'] }}" 
                                             alt="{{ $campaign['hoarding']['title'] }}"
                                             class="rounded me-2"
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                    @endif
                                    <div>
                                        <strong>{{ $campaign['hoarding']['title'] }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $campaign['booking_id'] }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                {{ $campaign['hoarding']['location'] }}<br>
                                <small class="text-muted">{{ $campaign['hoarding']['city'] }}</small>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($campaign['dates']['start'])->format('M d, Y') }}
                                <br>
                                <small class="text-muted">
                                    In {{ abs($campaign['dates']['days_until_start']) }} days
                                </small>
                            </td>
                            <td>
                                <strong>₹{{ number_format($campaign['financials']['total_amount']) }}</strong>
                            </td>
                            <td>
                                <a href="{{ route('customer.campaigns.show', $campaign['id']) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Completed -->
    @if(count($recent_completed) > 0)
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-check-circle text-success me-2"></i>Recently Completed</h5>
                <a href="{{ route('customer.campaigns.index', ['status' => 'completed']) }}" class="btn btn-sm btn-outline-primary">
                    View All
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Campaign</th>
                            <th>Location</th>
                            <th>Completed</th>
                            <th>Amount</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recent_completed as $campaign)
                        <tr>
                            <td>
                                <strong>{{ $campaign['hoarding']['title'] }}</strong>
                                <br>
                                <small class="text-muted">{{ $campaign['booking_id'] }}</small>
                            </td>
                            <td>
                                {{ $campaign['hoarding']['city'] }}
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($campaign['dates']['end'])->format('M d, Y') }}
                            </td>
                            <td>
                                ₹{{ number_format($campaign['financials']['total_amount']) }}
                            </td>
                            <td>
                                <a href="{{ route('customer.campaigns.show', $campaign['id']) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="fas fa-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    <!-- Recent Updates -->
    @if(count($recent_updates) > 0)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="fas fa-bell text-warning me-2"></i>Recent Updates</h5>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach(array_slice($recent_updates, 0, 5) as $update)
                <div class="timeline-item mb-3">
                    <div class="d-flex">
                        <div class="me-3">
                            <span class="badge bg-primary rounded-circle" style="width: 10px; height: 10px;"></span>
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex justify-content-between">
                                <strong>{{ $update->title ?? 'Update' }}</strong>
                                <small class="text-muted">{{ \Carbon\Carbon::parse($update->created_at)->diffForHumans() }}</small>
                            </div>
                            <p class="mb-0 text-muted small">{{ $update->description ?? '' }}</p>
                            <small class="text-muted">Campaign: {{ $update->booking_number }}</small>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>
@endsection

@push('styles')
<style>
.timeline {
    position: relative;
    padding-left: 20px;
}
.timeline::before {
    content: '';
    position: absolute;
    left: 4px;
    top: 0;
    bottom: 0;
    width: 2px;
    background: #e9ecef;
}
.timeline-item {
    position: relative;
}
</style>
@endpush
