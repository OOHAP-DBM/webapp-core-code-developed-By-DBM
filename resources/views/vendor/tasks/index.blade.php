@extends('layouts.vendor')

@section('page-title', 'Task Management')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">Task Management</h2>
        <p class="text-muted mb-0">Manage graphics, printing, and mounting tasks</p>
    </div>
    <button class="btn btn-vendor-primary" data-bs-toggle="modal" data-bs-target="#createTaskModal">
        <i class="bi bi-plus-circle me-2"></i>Add Task
    </button>
</div>

<!-- Task Stats -->
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #fef3c7; color: #f59e0b;">
                    <i class="bi bi-hourglass-split"></i>
                </div>
                <div class="stat-label">Pending</div>
                <div class="stat-value">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #dbeafe; color: #2563eb;">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div class="stat-label">In Progress</div>
                <div class="stat-value">{{ $stats['in_progress'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #d1fae5; color: #10b981;">
                    <i class="bi bi-check-circle"></i>
                </div>
                <div class="stat-label">Completed</div>
                <div class="stat-value">{{ $stats['completed'] ?? 0 }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="vendor-card">
            <div class="vendor-card-body">
                <div class="stat-icon" style="background: #fee2e2; color: #ef4444;">
                    <i class="bi bi-exclamation-triangle"></i>
                </div>
                <div class="stat-label">Overdue</div>
                <div class="stat-value">{{ $stats['overdue'] ?? 0 }}</div>
            </div>
        </div>
    </div>
</div>

<!-- Task Filters -->
<div class="vendor-card mb-4">
    <div class="vendor-card-body">
        <div class="btn-group mb-3" role="group">
            <input type="radio" class="btn-check" name="taskFilter" id="filterAll" checked>
            <label class="btn btn-outline-primary" for="filterAll">All Tasks</label>
            
            <input type="radio" class="btn-check" name="taskFilter" id="filterGraphics">
            <label class="btn btn-outline-primary" for="filterGraphics">Graphics</label>
            
            <input type="radio" class="btn-check" name="taskFilter" id="filterPrinting">
            <label class="btn btn-outline-primary" for="filterPrinting">Printing</label>
            
            <input type="radio" class="btn-check" name="taskFilter" id="filterMounting">
            <label class="btn btn-outline-primary" for="filterMounting">Mounting</label>
            
            <input type="radio" class="btn-check" name="taskFilter" id="filterMaintenance">
            <label class="btn btn-outline-primary" for="filterMaintenance">Maintenance</label>
        </div>
    </div>
</div>

<!-- Task Kanban Board -->
<div class="row g-4">
    <!-- Pending Column -->
    <div class="col-md-4">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">
                    <i class="bi bi-hourglass text-warning me-2"></i>Pending
                    <span class="badge bg-warning text-dark ms-2">{{ count($pendingTasks ?? []) }}</span>
                </h6>
            </div>
            <div class="vendor-card-body">
                @forelse($pendingTasks ?? [] as $task)
                    <div class="task-card mb-3" data-task-id="{{ $task->id }}" draggable="true">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge 
                                @if($task->type === 'graphics') bg-primary
                                @elseif($task->type === 'printing') bg-info
                                @elseif($task->type === 'mounting') bg-success
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($task->type) }}
                            </span>
                            <span class="badge 
                                @if($task->priority === 'high') bg-danger
                                @elseif($task->priority === 'medium') bg-warning text-dark
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($task->priority) }}
                            </span>
                        </div>
                        <h6 class="mb-2">{{ $task->title }}</h6>
                        <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M') }}
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewTask({{ $task->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="startTask({{ $task->id }})">
                                    <i class="bi bi-play"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="small mb-0">No pending tasks</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- In Progress Column -->
    <div class="col-md-4">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>In Progress
                    <span class="badge bg-primary ms-2">{{ count($inProgressTasks ?? []) }}</span>
                </h6>
            </div>
            <div class="vendor-card-body">
                @forelse($inProgressTasks ?? [] as $task)
                    <div class="task-card mb-3" data-task-id="{{ $task->id }}" draggable="true">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge 
                                @if($task->type === 'graphics') bg-primary
                                @elseif($task->type === 'printing') bg-info
                                @elseif($task->type === 'mounting') bg-success
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($task->type) }}
                            </span>
                            <div class="progress" style="width: 60px; height: 20px;">
                                <div class="progress-bar" role="progressbar" style="width: {{ $task->progress ?? 0 }}%"></div>
                            </div>
                        </div>
                        <h6 class="mb-2">{{ $task->title }}</h6>
                        <p class="text-muted small mb-2">{{ Str::limit($task->description, 100) }}</p>
                        <div class="d-flex justify-content-between align-items-center">
                            <small class="text-muted">
                                <i class="bi bi-calendar me-1"></i>
                                {{ \Carbon\Carbon::parse($task->due_date)->format('d M') }}
                            </small>
                            <div class="btn-group btn-group-sm">
                                <button class="btn btn-outline-primary" onclick="viewTask({{ $task->id }})">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <button class="btn btn-outline-success" onclick="completeTask({{ $task->id }})">
                                    <i class="bi bi-check"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="small mb-0">No tasks in progress</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Completed Column -->
    <div class="col-md-4">
        <div class="vendor-card">
            <div class="vendor-card-header">
                <h6 class="vendor-card-title mb-0">
                    <i class="bi bi-check-circle text-success me-2"></i>Completed
                    <span class="badge bg-success ms-2">{{ count($completedTasks ?? []) }}</span>
                </h6>
            </div>
            <div class="vendor-card-body">
                @forelse($completedTasks ?? [] as $task)
                    <div class="task-card mb-3 opacity-75" data-task-id="{{ $task->id }}">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <span class="badge 
                                @if($task->type === 'graphics') bg-primary
                                @elseif($task->type === 'printing') bg-info
                                @elseif($task->type === 'mounting') bg-success
                                @else bg-secondary
                                @endif">
                                {{ ucfirst($task->type) }}
                            </span>
                            <i class="bi bi-check-circle-fill text-success"></i>
                        </div>
                        <h6 class="mb-2">{{ $task->title }}</h6>
                        <small class="text-muted">
                            <i class="bi bi-check me-1"></i>
                            Completed {{ \Carbon\Carbon::parse($task->completed_at)->diffForHumans() }}
                        </small>
                    </div>
                @empty
                    <div class="text-center text-muted py-3">
                        <i class="bi bi-inbox"></i>
                        <p class="small mb-0">No completed tasks</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>

<!-- Create Task Modal -->
<div class="modal fade" id="createTaskModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create New Task</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('vendor.tasks.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Task Type *</label>
                        <select class="form-select" name="type" required>
                            <option value="">Select Type</option>
                            <option value="graphics">Graphics Design</option>
                            <option value="printing">Printing</option>
                            <option value="mounting">Mounting/Installation</option>
                            <option value="maintenance">Maintenance</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-control" name="title" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Related Booking</label>
                        <select class="form-select" name="booking_id">
                            <option value="">None</option>
                            @foreach($bookings ?? [] as $booking)
                                <option value="{{ $booking->id }}">Booking #{{ $booking->id }} - {{ $booking->hoarding->title ?? '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Priority *</label>
                            <select class="form-select" name="priority" required>
                                <option value="low">Low</option>
                                <option value="medium" selected>Medium</option>
                                <option value="high">High</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Due Date *</label>
                            <input type="date" class="form-control" name="due_date" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-vendor-primary">Create Task</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
.task-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 0.5rem;
    padding: 1rem;
    cursor: move;
    transition: all 0.2s;
}

.task-card:hover {
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    transform: translateY(-2px);
}

.task-card[draggable="true"] {
    cursor: grab;
}

.task-card[draggable="true"]:active {
    cursor: grabbing;
}
</style>
@endpush

@push('scripts')
<script>
function viewTask(id) {
    window.location.href = `/vendor/tasks/${id}`;
}

function startTask(id) {
    if (confirm('Start this task?')) {
        fetch(`/vendor/tasks/${id}/start`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => window.location.reload());
    }
}

function completeTask(id) {
    if (confirm('Mark this task as completed?')) {
        fetch(`/vendor/tasks/${id}/complete`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        }).then(() => window.location.reload());
    }
}

// Drag and Drop (Basic implementation)
const taskCards = document.querySelectorAll('.task-card[draggable="true"]');
let draggedElement = null;

taskCards.forEach(card => {
    card.addEventListener('dragstart', function(e) {
        draggedElement = this;
        this.style.opacity = '0.5';
    });
    
    card.addEventListener('dragend', function() {
        this.style.opacity = '1';
    });
});
</script>
@endpush
