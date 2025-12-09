@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <h1>Audit Trail & Logs</h1>
            <p class="text-muted">Complete activity history with who, what, when, and where</p>
        </div>
    </div>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Total Logs</h6>
                    <h2>{{ number_format($statistics['total'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Today</h6>
                    <h2>{{ number_format($statistics['today'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">This Week</h6>
                    <h2>{{ number_format($statistics['this_week'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">This Month</h6>
                    <h2>{{ number_format($statistics['this_month'] ?? 0) }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.audit-logs.index') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Action</label>
                    <select name="action" class="form-select">
                        <option value="">All Actions</option>
                        @foreach($actions as $action)
                            <option value="{{ $action }}" {{ request('action') == $action ? 'selected' : '' }}>
                                {{ ucfirst($action) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Module</label>
                    <select name="module" class="form-select">
                        <option value="">All Modules</option>
                        @foreach($modules as $module)
                            <option value="{{ $module }}" {{ request('module') == $module ? 'selected' : '' }}>
                                {{ ucfirst($module) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">From Date</label>
                    <input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label">To Date</label>
                    <input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Description, user..." value="{{ request('search') }}">
                </div>
                <div class="col-md-1">
                    <label class="form-label">&nbsp;</label>
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                </div>
            </form>
            <div class="mt-2">
                <a href="{{ route('admin.audit-logs.export', request()->all()) }}" class="btn btn-sm btn-success">
                    <i class="fas fa-download"></i> Export CSV
                </a>
            </div>
        </div>
    </div>

    <!-- Audit Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Timestamp</th>
                            <th>User</th>
                            <th>Action</th>
                            <th>Module</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Changes</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    <small>{{ $log->created_at->format('Y-m-d') }}</small><br>
                                    <strong>{{ $log->created_at->format('H:i:s') }}</strong><br>
                                    <small class="text-muted">{{ $log->relative_time }}</small>
                                </td>
                                <td>
                                    <strong>{{ $log->user_name ?? 'System' }}</strong><br>
                                    <small class="text-muted">{{ $log->user_email ?? '' }}</small><br>
                                    <span class="badge bg-secondary">{{ $log->user_type }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->action == 'created' ? 'success' : ($log->action == 'deleted' ? 'danger' : 'info') }}">
                                        {{ $log->action_label }}
                                    </span>
                                </td>
                                <td>
                                    @if($log->module)
                                        <span class="badge bg-primary">{{ $log->module }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <strong>{{ $log->model_name }}</strong><br>
                                    {{ $log->description }}
                                </td>
                                <td>
                                    <code>{{ $log->ip_address ?? '-' }}</code>
                                </td>
                                <td>
                                    @if($log->changed_fields && count($log->changed_fields) > 0)
                                        <span class="badge bg-warning">{{ count($log->changed_fields) }} fields</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.audit-logs.show', $log->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No audit logs found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-3">
                {{ $logs->links() }}
            </div>
        </div>
    </div>
</div>
@endsection
