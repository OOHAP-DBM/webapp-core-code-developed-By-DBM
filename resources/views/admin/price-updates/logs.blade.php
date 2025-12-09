@extends('layouts.admin')

@section('title', 'Price Update Logs')

@section('content')
<div class="container-fluid py-4">
    <div class="card shadow">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h4 class="mb-0"><i class="bi bi-clock-history me-2"></i>Price Update History</h4>
            <a href="{{ route('admin.price-updates.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back
            </a>
        </div>
        <div class="card-body">
            <!-- Filters -->
            <form method="GET" class="row g-3 mb-4">
                <div class="col-md-3">
                    <label class="form-label">Update Type</label>
                    <select class="form-select form-select-sm" name="update_type">
                        <option value="">All Types</option>
                        <option value="single" {{ ($filters['update_type'] ?? '') === 'single' ? 'selected' : '' }}>Single</option>
                        <option value="bulk" {{ ($filters['update_type'] ?? '') === 'bulk' ? 'selected' : '' }}>Bulk</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Admin</label>
                    <select class="form-select form-select-sm" name="admin_id">
                        <option value="">All Admins</option>
                        @foreach($admins as $admin)
                        <option value="{{ $admin->id }}" {{ ($filters['admin_id'] ?? '') == $admin->id ? 'selected' : '' }}>
                            {{ $admin->name }}
                        </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" class="form-control form-control-sm" name="date_from" value="{{ $filters['date_from'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" class="form-control form-control-sm" name="date_to" value="{{ $filters['date_to'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-sm me-2">
                        <i class="bi bi-funnel me-1"></i>Filter
                    </button>
                    <a href="{{ route('admin.price-updates.logs') }}" class="btn btn-secondary btn-sm">Reset</a>
                </div>
            </form>

            <!-- Logs Table -->
            @if($logs->isEmpty())
            <div class="text-center py-5">
                <i class="bi bi-inbox" style="font-size: 3rem; color: #dee2e6;"></i>
                <p class="text-muted mt-3">No update logs found</p>
            </div>
            @else
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Date & Time</th>
                            <th>Type</th>
                            <th>Hoarding</th>
                            <th>Old → New (Monthly)</th>
                            <th>Change</th>
                            <th>Admin</th>
                            <th>Reason</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>#{{ $log->id }}</td>
                            <td>{{ $log->created_at->format('M d, Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $log->update_type === 'single' ? 'bg-primary' : 'bg-info' }}">
                                    {{ ucfirst($log->update_type) }}
                                </span>
                                @if($log->batch_id)
                                <br><small class="text-muted">Batch: {{ substr($log->batch_id, 0, 8) }}</small>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.hoardings.show', $log->hoarding_id) }}" target="_blank">
                                    {{ $log->hoarding->title ?? 'N/A' }}
                                </a>
                            </td>
                            <td>
                                ₹{{ number_format($log->old_monthly_price, 2) }}
                                <i class="bi bi-arrow-right mx-1"></i>
                                ₹{{ number_format($log->new_monthly_price, 2) }}
                            </td>
                            <td>
                                @php
                                    $change = $log->monthly_price_change;
                                    $changePercent = $log->monthly_price_change_percent;
                                @endphp
                                <span class="{{ $change >= 0 ? 'text-success' : 'text-danger' }}">
                                    <i class="bi bi-{{ $change >= 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                    {{ $change >= 0 ? '+' : '' }}₹{{ number_format(abs($change), 2) }}
                                    <br><small>({{ number_format($changePercent, 1) }}%)</small>
                                </span>
                            </td>
                            <td>{{ $log->admin->name ?? 'N/A' }}</td>
                            <td>
                                @if($log->reason)
                                <small>{{ Str::limit($log->reason, 30) }}</small>
                                @else
                                <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.price-updates.logs.show', $log->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="mt-4">
                {{ $logs->withQueryString()->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
