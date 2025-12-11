@extends('layouts.admin')

@section('title', 'Fraud Detection Dashboard')

@section('content')
<div class="container-fluid py-4">
    <!-- Page Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-1">🛡️ Fraud Detection & Risk Management</h2>
            <p class="text-muted mb-0">Monitor suspicious activities and manage fraud alerts</p>
        </div>
        <div>
            <button class="btn btn-outline-primary" onclick="location.reload()">
                <i class="bi bi-arrow-clockwise"></i> Refresh
            </button>
            <a href="{{ route('admin.fraud.export') }}" class="btn btn-success">
                <i class="bi bi-download"></i> Export Report
            </a>
        </div>
    </div>

    <!-- Alert Statistics -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Critical Alerts</p>
                            <h3 class="mb-0 text-danger">{{ $stats['critical_alerts'] }}</h3>
                            <small class="text-muted">Requires immediate action</small>
                        </div>
                        <div class="text-danger">
                            <i class="bi bi-exclamation-triangle-fill display-4"></i>
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
                            <p class="text-muted mb-1 small">Pending Review</p>
                            <h3 class="mb-0 text-warning">{{ $stats['pending_alerts'] }}</h3>
                            <small class="text-muted">Awaiting review</small>
                        </div>
                        <div class="text-warning">
                            <i class="bi bi-hourglass-split display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Blocked Users</p>
                            <h3 class="mb-0 text-primary">{{ $stats['blocked_users'] }}</h3>
                            <small class="text-muted">Currently blocked</small>
                        </div>
                        <div class="text-primary">
                            <i class="bi bi-shield-slash display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-info">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <p class="text-muted mb-1 small">Suspicious Events (24h)</p>
                            <h3 class="mb-0 text-info">{{ $stats['suspicious_events_24h'] }}</h3>
                            <small class="text-muted">Last 24 hours</small>
                        </div>
                        <div class="text-info">
                            <i class="bi bi-eye-fill display-4"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Type Distribution -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Alert Distribution by Type</h5>
                </div>
                <div class="card-body">
                    <canvas id="alertTypeChart" height="100"></canvas>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Risk Level Distribution</h5>
                </div>
                <div class="card-body">
                    <canvas id="riskLevelChart" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('admin.fraud.dashboard') }}" class="row g-3">
                <div class="col-md-2">
                    <label class="form-label">Alert Type</label>
                    <select name="alert_type" class="form-select">
                        <option value="">All Types</option>
                        <option value="high_value_frequency" {{ request('alert_type') === 'high_value_frequency' ? 'selected' : '' }}>High Value Frequency</option>
                        <option value="gst_mismatch" {{ request('alert_type') === 'gst_mismatch' ? 'selected' : '' }}>GST Mismatch</option>
                        <option value="repeated_payment_failures" {{ request('alert_type') === 'repeated_payment_failures' ? 'selected' : '' }}>Payment Failures</option>
                        <option value="booking_velocity_anomaly" {{ request('alert_type') === 'booking_velocity_anomaly' ? 'selected' : '' }}>Velocity Anomaly</option>
                        <option value="amount_spike_anomaly" {{ request('alert_type') === 'amount_spike_anomaly' ? 'selected' : '' }}>Amount Spike</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Severity</label>
                    <select name="severity" class="form-select">
                        <option value="">All Levels</option>
                        <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
                        <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                        <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                        <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="reviewing" {{ request('status') === 'reviewing' ? 'selected' : '' }}>Reviewing</option>
                        <option value="resolved" {{ request('status') === 'resolved' ? 'selected' : '' }}>Resolved</option>
                        <option value="false_positive" {{ request('status') === 'false_positive' ? 'selected' : '' }}>False Positive</option>
                        <option value="confirmed_fraud" {{ request('status') === 'confirmed_fraud' ? 'selected' : '' }}>Confirmed Fraud</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Time Range</label>
                    <select name="time_range" class="form-select">
                        <option value="24h" {{ request('time_range', '24h') === '24h' ? 'selected' : '' }}>Last 24 Hours</option>
                        <option value="7d" {{ request('time_range') === '7d' ? 'selected' : '' }}>Last 7 Days</option>
                        <option value="30d" {{ request('time_range') === '30d' ? 'selected' : '' }}>Last 30 Days</option>
                        <option value="all" {{ request('time_range') === 'all' ? 'selected' : '' }}>All Time</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" 
                           placeholder="User email, phone, ID..." 
                           value="{{ request('search') }}">
                </div>
                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Critical Alerts - Always Visible -->
    @if($criticalAlerts->count() > 0)
    <div class="card mb-4 border-danger">
        <div class="card-header bg-danger text-white">
            <h5 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Critical Alerts ({{ $criticalAlerts->count() }})</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Alert ID</th>
                            <th>Type</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Risk Score</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($criticalAlerts as $alert)
                        <tr class="alert-row" data-alert-id="{{ $alert->id }}">
                            <td><strong>#{{ $alert->id }}</strong></td>
                            <td>
                                <span class="badge bg-danger">{{ str_replace('_', ' ', ucwords($alert->alert_type, '_')) }}</span>
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $alert->user_email }}</strong><br>
                                    <small class="text-muted">ID: {{ $alert->user_id }}</small>
                                </div>
                            </td>
                            <td>{{ Str::limit($alert->description, 80) }}</td>
                            <td>
                                <div class="progress" style="height: 20px; width: 80px;">
                                    <div class="progress-bar bg-danger" role="progressbar" 
                                         style="width: {{ $alert->risk_score }}%;" 
                                         aria-valuenow="{{ $alert->risk_score }}" aria-valuemin="0" aria-valuemax="100">
                                        {{ $alert->risk_score }}
                                    </div>
                                </div>
                            </td>
                            <td>{{ $alert->created_at->diffForHumans() }}</td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" onclick="viewAlert({{ $alert->id }})" title="View Details">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    @if($alert->status === 'pending')
                                    <button class="btn btn-outline-danger" onclick="blockUser({{ $alert->user_id }}, {{ $alert->id }})" title="Block User">
                                        <i class="bi bi-shield-slash"></i>
                                    </button>
                                    @endif
                                    <button class="btn btn-outline-success" onclick="resolveAlert({{ $alert->id }})" title="Resolve">
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

    <!-- All Fraud Alerts -->
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-table"></i> Fraud Alerts</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Type</th>
                            <th>Severity</th>
                            <th>User</th>
                            <th>Description</th>
                            <th>Risk Score</th>
                            <th>Status</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($alerts as $alert)
                        <tr>
                            <td>{{ $alert->id }}</td>
                            <td><small>{{ str_replace('_', ' ', ucwords($alert->alert_type, '_')) }}</small></td>
                            <td>
                                <span class="badge bg-{{ 
                                    $alert->severity === 'critical' ? 'danger' : 
                                    ($alert->severity === 'high' ? 'warning' : 
                                    ($alert->severity === 'medium' ? 'info' : 'secondary'))
                                }}">
                                    {{ ucfirst($alert->severity) }}
                                </span>
                            </td>
                            <td>
                                <div>
                                    {{ $alert->user_email }}<br>
                                    <small class="text-muted">{{ $alert->user_phone ?? 'N/A' }}</small>
                                </div>
                            </td>
                            <td>{{ Str::limit($alert->description, 60) }}</td>
                            <td>
                                <span class="badge bg-{{ $alert->risk_score >= 80 ? 'danger' : ($alert->risk_score >= 60 ? 'warning' : 'info') }}">
                                    {{ $alert->risk_score }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ 
                                    $alert->status === 'confirmed_fraud' ? 'danger' : 
                                    ($alert->status === 'resolved' ? 'success' : 
                                    ($alert->status === 'false_positive' ? 'secondary' : 'warning'))
                                }}">
                                    {{ str_replace('_', ' ', ucwords($alert->status, '_')) }}
                                </span>
                            </td>
                            <td>{{ $alert->created_at->format('M d, H:i') }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" onclick="viewAlert({{ $alert->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-4">
                                <i class="bi bi-shield-check display-4 text-success"></i>
                                <p class="text-muted mt-2">No fraud alerts found</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($alerts->hasPages())
        <div class="card-footer">
            {{ $alerts->links() }}
        </div>
        @endif
    </div>

    <!-- Recent Suspicious Events -->
    <div class="card mt-4">
        <div class="card-header">
            <h5 class="mb-0"><i class="bi bi-clock-history"></i> Recent Suspicious Events</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Time</th>
                            <th>Event Type</th>
                            <th>User</th>
                            <th>IP Address</th>
                            <th>Risk Score</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($recentEvents as $event)
                        <tr>
                            <td>{{ $event->created_at->format('M d, H:i:s') }}</td>
                            <td><small>{{ str_replace('_', ' ', ucwords($event->event_type, '_')) }}</small></td>
                            <td>{{ $event->user->email ?? 'Guest' }}</td>
                            <td><code>{{ $event->ip_address }}</code></td>
                            <td>
                                <span class="badge bg-{{ $event->risk_score >= 70 ? 'danger' : 'warning' }}">
                                    {{ $event->risk_score }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-info" onclick="viewEvent({{ $event->id }})">
                                    <i class="bi bi-info-circle"></i>
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Alert Details Modal -->
<div class="modal fade" id="alertModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Alert Details</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="alertModalBody">
                <!-- Content loaded via JavaScript -->
            </div>
        </div>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Alert Type Distribution Chart
const alertTypeCtx = document.getElementById('alertTypeChart').getContext('2d');
new Chart(alertTypeCtx, {
    type: 'bar',
    data: {
        labels: @json($chartData['alert_types']['labels'] ?? []),
        datasets: [{
            label: 'Number of Alerts',
            data: @json($chartData['alert_types']['data'] ?? []),
            backgroundColor: [
                'rgba(220, 53, 69, 0.7)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(13, 110, 253, 0.7)',
                'rgba(25, 135, 84, 0.7)',
                'rgba(108, 117, 125, 0.7)',
            ],
            borderColor: [
                'rgb(220, 53, 69)',
                'rgb(255, 193, 7)',
                'rgb(13, 110, 253)',
                'rgb(25, 135, 84)',
                'rgb(108, 117, 125)',
            ],
            borderWidth: 1
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
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

// Risk Level Distribution Chart
const riskLevelCtx = document.getElementById('riskLevelChart').getContext('2d');
new Chart(riskLevelCtx, {
    type: 'doughnut',
    data: {
        labels: ['Critical', 'High', 'Medium', 'Low'],
        datasets: [{
            data: @json($chartData['risk_levels']['data'] ?? [0, 0, 0, 0]),
            backgroundColor: ['#dc3545', '#ffc107', '#0d6efd', '#6c757d']
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false
    }
});

// View alert details
function viewAlert(alertId) {
    fetch(`/admin/fraud/alerts/${alertId}`)
        .then(response => response.json())
        .then(data => {
            document.getElementById('alertModalBody').innerHTML = `
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Alert Type:</strong> ${data.alert_type}</p>
                        <p><strong>Severity:</strong> <span class="badge bg-${data.severity === 'critical' ? 'danger' : 'warning'}">${data.severity}</span></p>
                        <p><strong>Risk Score:</strong> ${data.risk_score}</p>
                        <p><strong>Confidence:</strong> ${data.confidence_level}%</p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>User:</strong> ${data.user_email}</p>
                        <p><strong>Phone:</strong> ${data.user_phone || 'N/A'}</p>
                        <p><strong>Created:</strong> ${data.created_at}</p>
                        <p><strong>Status:</strong> ${data.status}</p>
                    </div>
                </div>
                <hr>
                <p><strong>Description:</strong></p>
                <p>${data.description}</p>
                ${data.metadata ? `<p><strong>Metadata:</strong> <pre>${JSON.stringify(data.metadata, null, 2)}</pre></p>` : ''}
                <hr>
                <div class="d-flex gap-2">
                    <button class="btn btn-success" onclick="resolveAlert(${alertId}, 'resolved')">Mark Resolved</button>
                    <button class="btn btn-warning" onclick="resolveAlert(${alertId}, 'false_positive')">False Positive</button>
                    <button class="btn btn-danger" onclick="resolveAlert(${alertId}, 'confirmed_fraud')">Confirm Fraud</button>
                </div>
            `;
            new bootstrap.Modal(document.getElementById('alertModal')).show();
        });
}

// Resolve alert
function resolveAlert(alertId, resolution = 'resolved') {
    if (confirm(`Are you sure you want to mark this alert as ${resolution}?`)) {
        fetch(`/admin/fraud/alerts/${alertId}/resolve`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ resolution: resolution })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

// Block user
function blockUser(userId, alertId) {
    const reason = prompt('Enter reason for blocking this user:');
    if (reason) {
        fetch(`/admin/fraud/users/${userId}/block`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                reason: reason,
                alert_id: alertId 
            })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert('User blocked successfully');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        });
    }
}

// View event details
function viewEvent(eventId) {
    // Similar to viewAlert - fetch and display event details
    console.log('View event:', eventId);
}
</script>
@endsection
