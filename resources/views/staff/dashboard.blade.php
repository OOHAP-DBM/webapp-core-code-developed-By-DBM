@extends('layouts.staff')

@section('page-title', 'Dashboard')

@section('content')
<div class="mb-4">
    <h2 class="mb-1">Welcome, {{ Auth::user()->name }}</h2>
    <p class="text-muted mb-0">
        <span class="badge bg-primary">{{ ucfirst(Auth::user()->staff_type ?? 'Staff') }}</span>
    </p>
</div>

<!-- Stats Overview -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-warning-subtle text-warning">
                            <i class="bi bi-hourglass-split"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Pending Tasks</div>
                        <h3 class="mb-0">{{ $stats['pending'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-primary-subtle text-primary">
                            <i class="bi bi-clock-history"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">In Progress</div>
                        <h3 class="mb-0">{{ $stats['in_progress'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-success-subtle text-success">
                            <i class="bi bi-check-circle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Completed</div>
                        <h3 class="mb-0">{{ $stats['completed'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center">
                    <div class="flex-shrink-0">
                        <div class="stat-icon bg-danger-subtle text-danger">
                            <i class="bi bi-exclamation-triangle"></i>
                        </div>
                    </div>
                    <div class="flex-grow-1 ms-3">
                        <div class="text-muted small">Overdue</div>
                        <h3 class="mb-0">{{ $stats['overdue'] ?? 0 }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Recent Assignments -->
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white">
        <div class="d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Recent Assignments</h5>
            <a href="{{ route('staff.assignments.index') }}" class="btn btn-sm btn-outline-primary">
                View All
            </a>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Task ID</th>
                        <th>Booking</th>
                        <th>Type</th>
                        <th>Due Date</th>
                        <th>Priority</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentAssignments ?? [] as $assignment)
                        <tr>
                            <td><strong>#{{ $assignment->id }}</strong></td>
                            <td>
                                @if($assignment->booking)
                                    <div>{{ $assignment->booking->hoarding->title ?? 'N/A' }}</div>
                                    <small class="text-muted">Booking #{{ $assignment->booking->id }}</small>
                                @else
                                    <span class="text-muted">-</span>
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
                                <small>{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y') }}</small>
                                @if(\Carbon\Carbon::parse($assignment->due_date)->isPast() && $assignment->status !== 'completed')
                                    <span class="badge bg-danger ms-1">Overdue</span>
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
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
                                </span>
                            </td>
                            <td>
                                <a href="{{ route('staff.assignments.show', $assignment->id) }}" 
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                <i class="bi bi-inbox fs-2 d-block mb-2"></i>
                                No assignments yet
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Quick Actions based on staff type -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white">
        <h5 class="mb-0">Quick Actions</h5>
    </div>
    <div class="card-body">
        <div class="row g-3">
            @if(Auth::user()->staff_type === 'graphics')
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'pending']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-palette me-2"></i>New Design Tasks
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'in_progress']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-upload me-2"></i>Upload Designs
                    </a>
                </div>
            @elseif(Auth::user()->staff_type === 'printer')
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'pending']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-printer me-2"></i>Print Jobs
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'in_progress']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-camera me-2"></i>Upload Proof
                    </a>
                </div>
            @elseif(Auth::user()->staff_type === 'mounter')
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'pending']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-geo-alt me-2"></i>Mounting Tasks
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'in_progress']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-camera-video me-2"></i>Upload POD
                    </a>
                </div>
            @elseif(Auth::user()->staff_type === 'surveyor')
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'pending']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-clipboard-check me-2"></i>Survey Tasks
                    </a>
                </div>
                <div class="col-md-4">
                    <a href="{{ route('staff.assignments.index', ['status' => 'in_progress']) }}" 
                       class="btn btn-outline-primary w-100">
                        <i class="bi bi-file-earmark-text me-2"></i>Submit Reports
                    </a>
                </div>
            @endif
            <div class="col-md-4">
                <a href="{{ route('staff.profile.edit') }}" class="btn btn-outline-secondary w-100">
                    <i class="bi bi-person me-2"></i>My Profile
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.stat-icon {
    width: 48px;
    height: 48px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
}
</style>
@endpush
