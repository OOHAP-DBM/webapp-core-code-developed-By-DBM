@extends('layouts.admin')

@section('title', 'Notification Logs')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="mb-4">
        <h2 class="mb-1">Notification Logs</h2>
        <p class="text-muted mb-0">Track all sent notifications across all channels</p>
    </div>

    <!-- Statistics -->
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0">{{ $stats['total_sent'] }}</h4>
                    <small class="text-muted">Total</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-warning">{{ $stats['pending'] }}</h4>
                    <small class="text-muted">Pending</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-info">{{ $stats['sent'] }}</h4>
                    <small class="text-muted">Sent</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-success">{{ $stats['delivered'] }}</h4>
                    <small class="text-muted">Delivered</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-primary">{{ $stats['read'] }}</h4>
                    <small class="text-muted">Read</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <h4 class="mb-0 text-danger">{{ $stats['failed'] }}</h4>
                    <small class="text-muted">Failed</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Channel</label>
                    <select name="channel" class="form-select">
                        <option value="">All Channels</option>
                        @foreach($channels as $key => $label)
                            <option value="{{ $key }}" {{ request('channel') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Status</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="sent" {{ request('status') === 'sent' ? 'selected' : '' }}>Sent</option>
                        <option value="delivered" {{ request('status') === 'delivered' ? 'selected' : '' }}>Delivered</option>
                        <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Failed</option>
                        <option value="read" {{ request('status') === 'read' ? 'selected' : '' }}>Read</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Event Type</label>
                    <select name="event_type" class="form-select">
                        <option value="">All Events</option>
                        @foreach($eventTypes as $key => $label)
                            <option value="{{ $key }}" {{ request('event_type') === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date From</label>
                    <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label">Date To</label>
                    <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Date/Time</th>
                            <th>Recipient</th>
                            <th>Event</th>
                            <th>Channel</th>
                            <th>Status</th>
                            <th>Provider</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td>
                                    {{ $log->created_at->format('M d, Y') }}
                                    <br><small class="text-muted">{{ $log->created_at->format('h:i A') }}</small>
                                </td>
                                <td>
                                    @if($log->user)
                                        <strong>{{ $log->user->name }}</strong>
                                        <br>
                                    @endif
                                    <small class="text-muted">{{ $log->recipient_identifier }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-secondary">
                                        {{ \App\Models\NotificationTemplate::getEventTypes()[$log->event_type] ?? $log->event_type }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->channel_color }}">
                                        {{ ucfirst($log->channel) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $log->status_color }}">
                                        {{ ucfirst($log->status) }}
                                    </span>
                                    @if($log->status === 'failed' && $log->retry_count > 0)
                                        <br><small class="text-muted">Retries: {{ $log->retry_count }}</small>
                                    @endif
                                </td>
                                <td>
                                    @if($log->provider)
                                        <small>{{ $log->provider }}</small>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.notifications.logs.show', $log) }}" 
                                           class="btn btn-outline-primary" title="View Details">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        @if($log->status === 'failed' && $log->canRetry())
                                            <form method="POST" action="{{ route('admin.notifications.logs.retry', $log) }}" style="display: inline;">
                                                @csrf
                                                <button type="submit" class="btn btn-outline-warning" title="Retry">
                                                    <i class="fas fa-redo"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No notification logs found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($logs->hasPages())
            <div class="card-footer bg-white">
                {{ $logs->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
