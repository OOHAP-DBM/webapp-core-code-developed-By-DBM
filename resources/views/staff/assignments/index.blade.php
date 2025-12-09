@extends('layouts.staff')

@section('page-title', 'My Assignments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">My Assignments</h2>
        <p class="text-muted mb-0">Manage your tasks and deliverables</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('staff.assignments.index') }}" class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select name="status" class="form-select" onchange="this.form.submit()">
                    <option value="">All Status</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Priority</label>
                <select name="priority" class="form-select" onchange="this.form.submit()">
                    <option value="">All Priorities</option>
                    <option value="high" {{ request('priority') === 'high' ? 'selected' : '' }}>High</option>
                    <option value="medium" {{ request('priority') === 'medium' ? 'selected' : '' }}>Medium</option>
                    <option value="low" {{ request('priority') === 'low' ? 'selected' : '' }}>Low</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Search</label>
                <input type="text" name="search" class="form-control" 
                       placeholder="Task ID or Booking" value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <label class="form-label">&nbsp;</label>
                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Search
                    </button>
                    <a href="{{ route('staff.assignments.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Assignments List -->
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Booking Details</th>
                        <th>Type</th>
                        <th>Assigned Date</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($assignments ?? [] as $assignment)
                        <tr>
                            <td><strong>#{{ $assignment->id }}</strong></td>
                            <td>
                                @if($assignment->booking)
                                    <div class="fw-semibold">{{ $assignment->booking->hoarding->title ?? 'N/A' }}</div>
                                    <small class="text-muted">
                                        Booking #{{ $assignment->booking->id }}<br>
                                        {{ $assignment->booking->hoarding->city ?? '' }}
                                    </small>
                                @else
                                    <div>{{ $assignment->title ?? 'General Task' }}</div>
                                    <small class="text-muted">{{ Str::limit($assignment->description ?? '', 50) }}</small>
                                @endif
                            </td>
                            <td>
                                <span class="badge 
                                    @if($assignment->type === 'graphics') bg-primary
                                    @elseif($assignment->type === 'printing') bg-info
                                    @elseif($assignment->type === 'mounting') bg-success
                                    @elseif($assignment->type === 'survey') bg-warning
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($assignment->type) }}
                                </span>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($assignment->created_at)->format('d M Y') }}</small>
                            </td>
                            <td>
                                <small>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}</small>
                                @if(\Carbon\Carbon::parse($assignment->due_date)->isPast() && $assignment->status !== 'completed')
                                    <br><span class="badge bg-danger">Overdue</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge 
                                    @if($assignment->priority === 'high') bg-danger
                                    @elseif($assignment->priority === 'medium') bg-warning text-dark
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($assignment->priority) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge 
                                    @if($assignment->status === 'completed') bg-success
                                    @elseif($assignment->status === 'in_progress') bg-primary
                                    @elseif($assignment->status === 'pending') bg-warning text-dark
                                    @elseif($assignment->status === 'rejected') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                </span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('staff.assignments.show', $assignment->id) }}" 
                                       class="btn btn-outline-primary">
                                        <i class="bi bi-eye"></i>
                                    </a>
                                    @if($assignment->status === 'pending')
                                        <button type="button" class="btn btn-outline-success" 
                                                onclick="acceptAssignment({{ $assignment->id }})">
                                            <i class="bi bi-check"></i>
                                        </button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No assignments found
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if(isset($assignments) && $assignments->hasPages())
            <div class="mt-3">
                {{ $assignments->links() }}
            </div>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
function acceptAssignment(id) {
    if (confirm('Accept this assignment?')) {
        fetch(`/staff/assignments/${id}/accept`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Content-Type': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert(data.message || 'Failed to accept assignment');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred');
        });
    }
}
</script>
@endpush
