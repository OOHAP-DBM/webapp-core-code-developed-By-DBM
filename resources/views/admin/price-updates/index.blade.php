@extends('layouts.admin')

@section('title', 'Price Update Engine')

@section('content')
<style>
    .stat-card {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 15px;
        padding: 25px;
        margin-bottom: 20px;
        box-shadow: 0 10px 20px rgba(102, 126, 234, 0.3);
    }
    .stat-card h3 {
        font-size: 2.5rem;
        font-weight: 700;
        margin-bottom: 5px;
    }
    .stat-card p {
        opacity: 0.9;
        margin: 0;
    }
    .action-card {
        border: 2px solid #e9ecef;
        border-radius: 15px;
        padding: 30px;
        text-align: center;
        transition: all 0.3s;
        height: 100%;
    }
    .action-card:hover {
        border-color: #667eea;
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
    }
    .action-icon {
        font-size: 3rem;
        color: #667eea;
        margin-bottom: 15px;
    }
    .log-table {
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.05);
    }
</style>

<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h2 mb-1">💰 Price Update Engine</h1>
            <p class="text-muted mb-0">Manage hoarding prices with audit trails</p>
        </div>
    </div>

    <!-- Alerts -->
    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-2"></i>{{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    @endif

    <!-- Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <h3>{{ number_format($statistics['total_updates']) }}</h3>
                <p>Total Updates</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <h3>{{ number_format($statistics['single_updates']) }}</h3>
                <p>Single Updates</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <h3>{{ number_format($statistics['bulk_updates']) }}</h3>
                <p>Bulk Updates</p>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <h3>{{ number_format($statistics['total_hoardings_affected']) }}</h3>
                <p>Hoardings Affected</p>
            </div>
        </div>
    </div>

    <!-- Action Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon">
                    <i class="bi bi-pencil-square"></i>
                </div>
                <h4>Single Price Update</h4>
                <p class="text-muted">Update price for one hoarding at a time</p>
                <a href="{{ route('admin.price-updates.single') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-right me-2"></i>Update Single
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon">
                    <i class="bi bi-collection"></i>
                </div>
                <h4>Bulk Price Update</h4>
                <p class="text-muted">Update multiple hoardings by criteria</p>
                <a href="{{ route('admin.price-updates.bulk') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-right me-2"></i>Bulk Update
                </a>
            </div>
        </div>
        <div class="col-md-4">
            <div class="action-card">
                <div class="action-icon">
                    <i class="bi bi-clock-history"></i>
                </div>
                <h4>Update History</h4>
                <p class="text-muted">View audit trail and update logs</p>
                <a href="{{ route('admin.price-updates.logs') }}" class="btn btn-primary mt-3">
                    <i class="bi bi-arrow-right me-2"></i>View Logs
                </a>
            </div>
        </div>
    </div>

    <!-- Recent Updates -->
    <div class="card log-table">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0"><i class="bi bi-list-ul me-2"></i>Recent Price Updates</h5>
        </div>
        <div class="card-body p-0">
            @if($recentLogs->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="text-muted mt-3">No price updates yet</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Hoarding</th>
                            <th>Old Price</th>
                            <th>New Price</th>
                            <th>Change</th>
                            <th>Admin</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentLogs as $log)
                        <tr>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $log->update_type === 'single' ? 'bg-primary' : 'bg-info' }}">
                                    {{ ucfirst($log->update_type) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('admin.hoardings.show', $log->hoarding_id) }}">
                                    {{ $log->hoarding->title ?? 'N/A' }}
                                </a>
                            </td>
                            <td>₹{{ number_format($log->old_monthly_price, 2) }}</td>
                            <td>₹{{ number_format($log->new_monthly_price, 2) }}</td>
                            <td>
                                @php
                                    $change = $log->monthly_price_change;
                                    $changeClass = $change >= 0 ? 'text-success' : 'text-danger';
                                    $changeIcon = $change >= 0 ? 'arrow-up' : 'arrow-down';
                                @endphp
                                <span class="{{ $changeClass }}">
                                    <i class="bi bi-{{ $changeIcon }}"></i>
                                    {{ $change >= 0 ? '+' : '' }}₹{{ number_format(abs($change), 2) }}
                                    ({{ number_format($log->monthly_price_change_percent, 1) }}%)
                                </span>
                            </td>
                            <td>{{ $log->admin->name ?? 'N/A' }}</td>
                            <td>
                                <a href="{{ route('admin.price-updates.logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        </div>
        @if($recentLogs->isNotEmpty())
        <div class="card-footer bg-white text-center">
            <a href="{{ route('admin.price-updates.logs') }}" class="text-decoration-none">
                View All Logs <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
        @endif
    </div>
</div>

@push('scripts')
<script>
    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(alert => {
            new bootstrap.Alert(alert).close();
        });
    }, 5000);
</script>
@endpush
@endsection
