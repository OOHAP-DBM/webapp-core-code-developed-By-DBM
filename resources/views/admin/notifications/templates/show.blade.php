@extends('layouts.admin')

@section('title', $template->name)

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-start mb-4">
        <div>
            <a href="{{ route('admin.notifications.templates.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 class="mb-1">{{ $template->name }}</h2>
            <p class="text-muted mb-0">
                <span class="badge bg-{{ $template->channel_color }}">{{ $template->channel_label }}</span>
                <span class="badge bg-secondary ms-1">{{ $template->event_type_label }}</span>
                @if($template->is_active)
                    <span class="badge bg-success ms-1">Active</span>
                @else
                    <span class="badge bg-danger ms-1">Inactive</span>
                @endif
                @if($template->is_system_default)
                    <span class="badge bg-info ms-1">System Default</span>
                @endif
            </p>
        </div>
        <div class="btn-group">
            <a href="{{ route('admin.notifications.templates.edit', $template) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#testModal">
                <i class="fas fa-paper-plane"></i> Test Send
            </button>
            <form method="POST" action="{{ route('admin.notifications.templates.duplicate', $template) }}" style="display: inline;">
                @csrf
                <button type="submit" class="btn btn-outline-secondary">
                    <i class="fas fa-copy"></i> Duplicate
                </button>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Left Column -->
        <div class="col-lg-8">
            <!-- Template Content -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Template Content</h5>
                </div>
                <div class="card-body">
                    @if($template->subject)
                        <div class="mb-4">
                            <h6>Subject</h6>
                            <div class="bg-light p-3 rounded">
                                <code>{{ $template->subject }}</code>
                            </div>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h6>Body (Plain Text)</h6>
                        <div class="bg-light p-3 rounded">
                            <pre class="mb-0">{{ $template->body }}</pre>
                        </div>
                    </div>

                    @if($template->html_body)
                        <div>
                            <h6>HTML Body</h6>
                            <div class="bg-light p-3 rounded">
                                <pre class="mb-0"><code>{{ $template->html_body }}</code></pre>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Statistics -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Usage Statistics</h5>
                </div>
                <div class="card-body">
                    <div class="row text-center">
                        <div class="col-md-4">
                            <h3 class="mb-0">{{ $stats['total_sent'] }}</h3>
                            <small class="text-muted">Total Sent</small>
                        </div>
                        <div class="col-md-4">
                            <h3 class="mb-0 text-success">{{ $stats['success_rate'] }}%</h3>
                            <small class="text-muted">Success Rate</small>
                        </div>
                        <div class="col-md-4">
                            <h3 class="mb-0 text-danger">{{ $stats['failed_count'] }}</h3>
                            <small class="text-muted">Failed</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Logs -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recent Notifications</h5>
                    <a href="{{ route('admin.notifications.logs.index', ['template_id' => $template->id]) }}" class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Recipient</th>
                                    <th>Status</th>
                                    <th>Sent At</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentLogs as $log)
                                    <tr>
                                        <td>
                                            @if($log->user)
                                                {{ $log->user->name }}
                                                <br><small class="text-muted">{{ $log->recipient_identifier }}</small>
                                            @else
                                                {{ $log->recipient_identifier }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-{{ $log->status_color }}">{{ ucfirst($log->status) }}</span>
                                        </td>
                                        <td>
                                            @if($log->sent_at)
                                                {{ $log->sent_at->format('M d, Y h:i A') }}
                                            @else
                                                <span class="text-muted">Not sent</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.notifications.logs.show', $log) }}" class="btn btn-sm btn-outline-primary">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted">
                                            No notifications sent using this template yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column -->
        <div class="col-lg-4">
            <!-- Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Details</h6>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt>Event Type</dt>
                        <dd>{{ $template->event_type_label }}</dd>

                        <dt>Channel</dt>
                        <dd>{{ $template->channel_label }}</dd>

                        <dt>Priority</dt>
                        <dd>{{ $template->priority }}</dd>

                        <dt>Status</dt>
                        <dd>
                            @if($template->is_active)
                                <span class="badge bg-success">Active</span>
                            @else
                                <span class="badge bg-danger">Inactive</span>
                            @endif
                        </dd>

                        @if($template->description)
                            <dt>Description</dt>
                            <dd>{{ $template->description }}</dd>
                        @endif

                        <dt>Created</dt>
                        <dd>
                            {{ $template->created_at->format('M d, Y h:i A') }}
                            @if($template->creator)
                                <br><small class="text-muted">by {{ $template->creator->name }}</small>
                            @endif
                        </dd>

                        <dt>Last Updated</dt>
                        <dd>
                            {{ $template->updated_at->format('M d, Y h:i A') }}
                            @if($template->updater)
                                <br><small class="text-muted">by {{ $template->updater->name }}</small>
                            @endif
                        </dd>
                    </dl>
                </div>
            </div>

            <!-- Available Placeholders -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h6 class="mb-0">Available Placeholders</h6>
                </div>
                <div class="card-body">
                    @if($template->available_placeholders)
                        <div class="list-group list-group-flush">
                            @foreach($template->available_placeholders as $placeholder => $description)
                                <div class="list-group-item px-0 py-2 border-0">
                                    <code class="text-primary">{{ $placeholder }}</code>
                                    <br><small class="text-muted">{{ $description }}</small>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Test Send Modal -->
<div class="modal fade" id="testModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('admin.notifications.templates.test', $template) }}">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Test Send Notification</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Recipient</label>
                        <input type="text" name="recipient" class="form-control" required
                               placeholder="{{ $template->channel === 'email' ? 'email@example.com' : ($template->channel === 'sms' || $template->channel === 'whatsapp' ? '+919999999999' : 'user@example.com') }}">
                        <small class="text-muted">
                            @if($template->channel === 'email')
                                Enter email address
                            @elseif($template->channel === 'sms' || $template->channel === 'whatsapp')
                                Enter phone number with country code
                            @else
                                Enter email or user ID
                            @endif
                        </small>
                    </div>

                    <div class="alert alert-info">
                        <small><i class="fas fa-info-circle"></i> Test data will be used for placeholders</small>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-paper-plane"></i> Send Test
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
