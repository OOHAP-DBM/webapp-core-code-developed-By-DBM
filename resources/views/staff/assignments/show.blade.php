@extends('layouts.staff')

@section('page-title', 'Assignment Details')

@section('content')
<div class="mb-4">
    <a href="{{ route('staff.assignments.index') }}" class="btn btn-outline-secondary btn-sm mb-3">
        <i class="bi bi-arrow-left"></i> Back to Assignments
    </a>
    <h2 class="mb-1">Task #{{ $assignment->id }}</h2>
    <div class="d-flex gap-2 align-items-center">
        <span class="badge 
            @if($assignment->type === 'graphics') bg-primary
            @elseif($assignment->type === 'printing') bg-info
            @elseif($assignment->type === 'mounting') bg-success
            @elseif($assignment->type === 'survey') bg-warning
            @else bg-secondary
            @endif">
            {{ ucfirst($assignment->type) }}
        </span>
        <span class="badge 
            @if($assignment->status === 'completed') bg-success
            @elseif($assignment->status === 'in_progress') bg-primary
            @elseif($assignment->status === 'pending') bg-warning text-dark
            @elseif($assignment->status === 'rejected') bg-danger
            @else bg-secondary
            @endif">
            {{ ucfirst(str_replace('_', ' ', $assignment->status)) }}
        </span>
        <span class="badge 
            @if($assignment->priority === 'high') bg-danger
            @elseif($assignment->priority === 'medium') bg-warning text-dark
            @else bg-secondary
            @endif">
            {{ ucfirst($assignment->priority) }} Priority
        </span>
    </div>
</div>

<div class="row g-4">
    <!-- Left Column: Assignment Details -->
    <div class="col-lg-8">
        <!-- Booking Information -->
        @if($assignment->booking)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Booking Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small">Booking ID</label>
                            <div class="fw-semibold">#{{ $assignment->booking->id }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Hoarding</label>
                            <div class="fw-semibold">{{ $assignment->booking->hoarding->title ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Location</label>
                            <div>{{ $assignment->booking->hoarding->address ?? '' }}, {{ $assignment->booking->hoarding->city ?? '' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Customer</label>
                            <div>{{ $assignment->booking->customer->name ?? 'N/A' }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Campaign Duration</label>
                            <div>{{ \Carbon\Carbon::parse($assignment->booking->start_date)->format('d M Y') }} - {{ \Carbon\Carbon::parse($assignment->booking->end_date)->format('d M Y') }}</div>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small">Booking Status</label>
                            <div>
                                <span class="badge bg-info">{{ ucfirst($assignment->booking->status) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Task Details -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Task Details</h5>
            </div>
            <div class="card-body">
                @if($assignment->title)
                    <div class="mb-3">
                        <label class="text-muted small">Title</label>
                        <div class="fw-semibold">{{ $assignment->title }}</div>
                    </div>
                @endif
                @if($assignment->description)
                    <div class="mb-3">
                        <label class="text-muted small">Description</label>
                        <div>{{ $assignment->description }}</div>
                    </div>
                @endif
                @if($assignment->notes)
                    <div class="mb-3">
                        <label class="text-muted small">Additional Notes</label>
                        <div class="alert alert-info mb-0">{{ $assignment->notes }}</div>
                    </div>
                @endif
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="text-muted small">Assigned Date</label>
                        <div>{{ \Carbon\Carbon::parse($assignment->created_at)->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Due Date</label>
                        <div class="fw-semibold text-danger">{{ \Carbon\Carbon::parse($assignment->due_date)->format('d M Y, H:i') }}</div>
                    </div>
                    <div class="col-md-4">
                        <label class="text-muted small">Time Remaining</label>
                        <div>
                            @if($assignment->status === 'completed')
                                <span class="text-success">Completed</span>
                            @else
                                {{ \Carbon\Carbon::parse($assignment->due_date)->diffForHumans() }}
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Type-Specific Upload Section -->
        @if($assignment->type === 'graphics')
            @include('staff.assignments.partials.graphics-upload', ['assignment' => $assignment])
        @elseif($assignment->type === 'printing')
            @include('staff.assignments.partials.printing-upload', ['assignment' => $assignment])
        @elseif($assignment->type === 'mounting')
            @include('staff.assignments.partials.mounting-upload', ['assignment' => $assignment])
        @elseif($assignment->type === 'survey')
            @include('staff.assignments.partials.survey-upload', ['assignment' => $assignment])
        @endif

        <!-- Uploaded Files/Deliverables -->
        @if($assignment->deliverables && count($assignment->deliverables) > 0)
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Submitted Deliverables</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($assignment->deliverables as $deliverable)
                            <div class="col-md-6">
                                <div class="border rounded p-3">
                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-file-earmark fs-4 text-primary me-2"></i>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold">{{ $deliverable->filename ?? 'File' }}</div>
                                            <small class="text-muted">{{ $deliverable->file_type ?? 'Unknown' }}</small>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ $deliverable->file_url }}" class="btn btn-sm btn-outline-primary" target="_blank">
                                            <i class="bi bi-download"></i> Download
                                        </a>
                                        @if(in_array($deliverable->file_type, ['image/jpeg', 'image/png', 'image/jpg']))
                                            <button class="btn btn-sm btn-outline-primary" 
                                                    onclick="viewImage('{{ $deliverable->file_url }}')">
                                                <i class="bi bi-eye"></i> View
                                            </button>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        <!-- Activity Log -->
        @if($assignment->activities && count($assignment->activities) > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Activity Log</h5>
                </div>
                <div class="card-body">
                    <div class="timeline">
                        @foreach($assignment->activities as $activity)
                            <div class="timeline-item mb-3">
                                <div class="d-flex">
                                    <div class="timeline-marker bg-primary"></div>
                                    <div class="ms-3 flex-grow-1">
                                        <div class="fw-semibold">{{ $activity->description }}</div>
                                        <small class="text-muted">
                                            {{ \Carbon\Carbon::parse($activity->created_at)->format('d M Y, H:i') }}
                                            by {{ $activity->user->name ?? 'System' }}
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <!-- Right Column: Actions & Status -->
    <div class="col-lg-4">
        <!-- Action Buttons -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body">
                @if($assignment->status === 'pending')
                    <button class="btn btn-success w-100 mb-2" onclick="acceptAssignment()">
                        <i class="bi bi-check-circle me-2"></i>Accept Assignment
                    </button>
                @endif

                @if($assignment->status === 'in_progress')
                    <button class="btn btn-primary w-100 mb-2" data-bs-toggle="modal" data-bs-target="#completeModal">
                        <i class="bi bi-check-circle me-2"></i>Mark as Complete
                    </button>
                @endif

                @if(in_array($assignment->status, ['pending', 'in_progress']))
                    <button class="btn btn-outline-secondary w-100" data-bs-toggle="modal" data-bs-target="#updateModal">
                        <i class="bi bi-chat-dots me-2"></i>Send Update
                    </button>
                @endif
            </div>
        </div>

        <!-- Contact Information -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white">
                <h5 class="mb-0">Contact Information</h5>
            </div>
            <div class="card-body">
                @if($assignment->booking && $assignment->booking->vendor)
                    <div class="mb-3">
                        <label class="text-muted small">Vendor</label>
                        <div class="fw-semibold">{{ $assignment->booking->vendor->name }}</div>
                        <small class="text-muted">{{ $assignment->booking->vendor->phone }}</small>
                    </div>
                @endif
                @if($assignment->booking && $assignment->booking->customer)
                    <div>
                        <label class="text-muted small">Customer</label>
                        <div class="fw-semibold">{{ $assignment->booking->customer->name }}</div>
                        <small class="text-muted">{{ $assignment->booking->customer->phone }}</small>
                    </div>
                @endif
            </div>
        </div>

        <!-- Progress Tracker -->
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white">
                <h5 class="mb-0">Progress</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <div class="d-flex justify-content-between mb-2">
                        <span>Completion</span>
                        <span class="fw-semibold">{{ $assignment->progress ?? 0 }}%</span>
                    </div>
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" 
                             style="width: {{ $assignment->progress ?? 0 }}%">
                        </div>
                    </div>
                </div>
                @if($assignment->started_at)
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Started {{ \Carbon\Carbon::parse($assignment->started_at)->diffForHumans() }}
                    </small>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Complete Modal -->
<div class="modal fade" id="completeModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Complete Assignment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('staff.assignments.complete', $assignment->id) }}" method="POST">
                @csrf
                <div class="modal-body">
                    <p>Are you sure you want to mark this assignment as complete?</p>
                    <div class="mb-3">
                        <label class="form-label">Completion Notes</label>
                        <textarea class="form-control" name="completion_notes" rows="3" 
                                  placeholder="Add any final notes..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Confirm Completion</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Update Modal -->
<div class="modal fade" id="updateModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Send Update</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="updateForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Update Message *</label>
                        <textarea class="form-control" name="message" rows="3" required 
                                  placeholder="Provide status update to vendor and customer..."></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Send To</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_vendor" checked>
                            <label class="form-check-label">Vendor</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="notify_customer" checked>
                            <label class="form-check-label">Customer</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Send Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function acceptAssignment() {
    if (confirm('Accept this assignment and start working on it?')) {
        fetch(`/staff/assignments/{{ $assignment->id }}/accept`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            }
        });
    }
}

document.getElementById('updateForm')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    
    fetch(`/staff/assignments/{{ $assignment->id }}/send-update`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('updateModal')).hide();
            alert('Update sent successfully!');
            window.location.reload();
        }
    });
});

function viewImage(url) {
    window.open(url, '_blank');
}
</script>
@endpush

@push('styles')
<style>
.timeline-marker {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin-top: 4px;
}
</style>
@endpush
