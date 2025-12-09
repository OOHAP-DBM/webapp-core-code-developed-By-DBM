@extends('layouts.admin')

@section('title', 'Notification Templates')

@section('content')
<div class="container-fluid py-4">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="mb-1">Notification Templates</h2>
            <p class="text-muted mb-0">Manage email, SMS, WhatsApp, and web notification templates</p>
        </div>
        <a href="{{ route('admin.notifications.templates.create') }}" class="btn btn-primary">
            <i class="fas fa-plus"></i> Create Template
        </a>
    </div>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
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

                <div class="col-md-3">
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
                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Template name..." value="{{ request('search') }}">
                </div>

                <div class="col-md-1 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Templates Table -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th>Template Name</th>
                            <th>Event Type</th>
                            <th>Channel</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>System</th>
                            <th>Last Updated</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($templates as $template)
                            <tr>
                                <td>
                                    <strong>{{ $template->name }}</strong>
                                    @if($template->description)
                                        <br><small class="text-muted">{{ Str::limit($template->description, 50) }}</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary">{{ $template->event_type_label }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-{{ $template->channel_color }}">
                                        @if($template->channel === 'email')
                                            <i class="fas fa-envelope"></i>
                                        @elseif($template->channel === 'sms')
                                            <i class="fas fa-sms"></i>
                                        @elseif($template->channel === 'whatsapp')
                                            <i class="fab fa-whatsapp"></i>
                                        @else
                                            <i class="fas fa-bell"></i>
                                        @endif
                                        {{ $template->channel_label }}
                                    </span>
                                </td>
                                <td>{{ $template->priority }}</td>
                                <td>
                                    <form method="POST" action="{{ route('admin.notifications.templates.toggle', $template) }}" style="display: inline;">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-link p-0 text-decoration-none">
                                            @if($template->is_active)
                                                <span class="badge bg-success">Active</span>
                                            @else
                                                <span class="badge bg-danger">Inactive</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td>
                                    @if($template->is_system_default)
                                        <span class="badge bg-info">System</span>
                                    @endif
                                </td>
                                <td>
                                    <small class="text-muted">
                                        {{ $template->updated_at->format('M d, Y') }}
                                        @if($template->updater)
                                            <br>by {{ $template->updater->name }}
                                        @endif
                                    </small>
                                </td>
                                <td class="text-end">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('admin.notifications.templates.show', $template) }}" 
                                           class="btn btn-outline-primary" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="{{ route('admin.notifications.templates.edit', $template) }}" 
                                           class="btn btn-outline-secondary" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('admin.notifications.templates.duplicate', $template) }}" 
                                              style="display: inline;">
                                            @csrf
                                            <button type="submit" class="btn btn-outline-info" title="Duplicate">
                                                <i class="fas fa-copy"></i>
                                            </button>
                                        </form>
                                        @if($template->canBeDeleted())
                                            <form method="POST" action="{{ route('admin.notifications.templates.destroy', $template) }}" 
                                                  onsubmit="return confirm('Are you sure you want to delete this template?')" style="display: inline;">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-outline-danger" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                                    No templates found. Create your first template to get started.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($templates->hasPages())
            <div class="card-footer bg-white">
                {{ $templates->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
