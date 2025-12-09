@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.audit-logs.index') }}">Audit Logs</a></li>
                    <li class="breadcrumb-item active">Log #{{ $auditLog->id }}</li>
                </ol>
            </nav>
            <h1>Audit Log Details</h1>
        </div>
    </div>

    <div class="row">
        <!-- Main Log Details -->
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Log Information</h5>
                </div>
                <div class="card-body">
                    <table class="table">
                        <tr>
                            <th width="200">ID</th>
                            <td>{{ $auditLog->id }}</td>
                        </tr>
                        <tr>
                            <th>Timestamp</th>
                            <td>
                                {{ $auditLog->created_at->format('Y-m-d H:i:s') }}
                                <small class="text-muted">({{ $auditLog->relative_time }})</small>
                            </td>
                        </tr>
                        <tr>
                            <th>Action</th>
                            <td>
                                <span class="badge bg-{{ $auditLog->action == 'created' ? 'success' : ($auditLog->action == 'deleted' ? 'danger' : 'info') }}">
                                    {{ $auditLog->action_label }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Module</th>
                            <td>
                                @if($auditLog->module)
                                    <span class="badge bg-primary">{{ $auditLog->module }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Description</th>
                            <td>{{ $auditLog->description }}</td>
                        </tr>
                        <tr>
                            <th>Model</th>
                            <td>
                                <strong>{{ $auditLog->model_name }}</strong><br>
                                <small class="text-muted">
                                    Type: {{ $auditLog->auditable_type }}<br>
                                    ID: {{ $auditLog->auditable_id }}
                                </small>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            <!-- Changes -->
            @if($auditLog->changed_fields && count($auditLog->changed_fields) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Changes (Before vs After)</h5>
                    </div>
                    <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Field</th>
                                    <th>Old Value</th>
                                    <th></th>
                                    <th>New Value</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($auditLog->changes_summary as $change)
                                    <tr>
                                        <td><strong>{{ $change['field'] }}</strong></td>
                                        <td>
                                            <span class="badge bg-danger">{{ $change['old'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <i class="fas fa-arrow-right"></i>
                                        </td>
                                        <td>
                                            <span class="badge bg-success">{{ $change['new'] }}</span>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            <!-- Metadata -->
            @if($auditLog->metadata && count($auditLog->metadata) > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Additional Metadata</h5>
                    </div>
                    <div class="card-body">
                        <pre>{{ json_encode($auditLog->metadata, JSON_PRETTY_PRINT) }}</pre>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="col-md-4">
            <!-- User Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>User Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>Name:</strong> {{ $auditLog->user_name ?? 'System' }}</p>
                    <p><strong>Email:</strong> {{ $auditLog->user_email ?? '-' }}</p>
                    <p><strong>Type:</strong> <span class="badge bg-secondary">{{ $auditLog->user_type }}</span></p>
                    @if($auditLog->user)
                        <a href="{{ route('admin.audit-logs.user-activity', $auditLog->user_id) }}" class="btn btn-sm btn-outline-primary">
                            View User Activity
                        </a>
                    @endif
                </div>
            </div>

            <!-- Request Info -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5>Request Information</h5>
                </div>
                <div class="card-body">
                    <p><strong>IP Address:</strong><br><code>{{ $auditLog->ip_address ?? '-' }}</code></p>
                    <p><strong>Method:</strong> {{ $auditLog->request_method ?? '-' }}</p>
                    @if($auditLog->request_url)
                        <p><strong>URL:</strong><br><small>{{ $auditLog->request_url }}</small></p>
                    @endif
                    @if($auditLog->user_agent)
                        <p><strong>User Agent:</strong><br><small class="text-muted">{{ Str::limit($auditLog->user_agent, 100) }}</small></p>
                    @endif
                </div>
            </div>

            <!-- Related Logs -->
            @if($relatedLogs->count() > 0)
                <div class="card mb-4">
                    <div class="card-header">
                        <h5>Related Activity</h5>
                        <small class="text-muted">Other logs for this entity</small>
                    </div>
                    <div class="card-body">
                        <div class="list-group list-group-flush">
                            @foreach($relatedLogs as $related)
                                <a href="{{ route('admin.audit-logs.show', $related->id) }}" class="list-group-item list-group-item-action">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <strong>{{ $related->action_label }}</strong><br>
                                            <small class="text-muted">{{ $related->user_name ?? 'System' }}</small>
                                        </div>
                                        <small class="text-muted">{{ $related->created_at->diffForHumans() }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
