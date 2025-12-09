@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-12">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.index') }}">Bookings</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('admin.bookings.show', $booking->id) }}">Booking #{{ $booking->id }}</a></li>
                    <li class="breadcrumb-item active">Timeline</li>
                </ol>
            </nav>
            <h1>Booking Timeline</h1>
        </div>
    </div>

    <!-- Progress Bar -->
    <div class="card mb-4">
        <div class="card-body">
            <h5>Overall Progress</h5>
            <div class="progress" style="height: 30px;">
                <div class="progress-bar bg-success" role="progressbar" 
                     style="width: {{ $progress }}%;" 
                     aria-valuenow="{{ $progress }}" 
                     aria-valuemin="0" 
                     aria-valuemax="100">
                    {{ $progress }}%
                </div>
            </div>
            
            @if($currentStage)
                <div class="mt-3">
                    <strong>Current Stage:</strong> 
                    <span class="badge bg-{{ $currentStage->status_color }}">
                        {{ $currentStage->title }}
                    </span>
                </div>
            @endif

            @if($nextEvent)
                <div class="mt-2">
                    <strong>Next Event:</strong> 
                    {{ $nextEvent->title }} 
                    <small class="text-muted">(Scheduled: {{ $nextEvent->scheduled_at->format('M d, Y') }})</small>
                </div>
            @endif
        </div>
    </div>

    <!-- Booking Info Summary -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Booking ID</h6>
                    <h4>#{{ $booking->id }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Customer</h6>
                    <h6>{{ $booking->customer->name }}</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Duration</h6>
                    <h6>{{ $booking->duration_days }} days</h6>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <h6 class="text-muted">Campaign Dates</h6>
                    <h6>{{ $booking->start_date->format('M d') }} - {{ $booking->end_date->format('M d, Y') }}</h6>
                </div>
            </div>
        </div>
    </div>

    <!-- Timeline -->
    <div class="card">
        <div class="card-header">
            <h5>Timeline Events</h5>
            <button class="btn btn-sm btn-outline-secondary float-end" onclick="rebuildTimeline()">
                <i class="fas fa-sync-alt"></i> Rebuild Timeline
            </button>
        </div>
        <div class="card-body">
            <div class="timeline">
                @foreach($timeline as $event)
                    <div class="timeline-item {{ $event->status }}">
                        <div class="timeline-marker bg-{{ $event->event_color }}">
                            <i class="fas {{ $event->event_icon }}"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="d-flex justify-content-between align-items-start">
                                <div>
                                    <h6 class="mb-1">
                                        {{ $event->title }}
                                        @if($event->version)
                                            <span class="badge bg-secondary">v{{ $event->version }}</span>
                                        @endif
                                    </h6>
                                    <p class="text-muted mb-1">{{ $event->description }}</p>
                                    
                                    @if($event->scheduled_at)
                                        <small class="text-muted">
                                            <i class="fas fa-calendar"></i> Scheduled: {{ $event->scheduled_at->format('M d, Y H:i') }}
                                        </small>
                                    @endif
                                    
                                    @if($event->completed_at)
                                        <br><small class="text-success">
                                            <i class="fas fa-check-circle"></i> Completed: {{ $event->completed_at->format('M d, Y H:i') }}
                                        </small>
                                    @endif
                                    
                                    @if($event->duration_formatted)
                                        <br><small class="text-info">
                                            <i class="fas fa-clock"></i> Duration: {{ $event->duration_formatted }}
                                        </small>
                                    @endif
                                </div>
                                <div>
                                    <span class="badge bg-{{ $event->status_color }}">
                                        {{ $event->status_label }}
                                    </span>
                                    <span class="badge bg-{{ $event->event_color }}">
                                        {{ ucfirst($event->event_category) }}
                                    </span>
                                </div>
                            </div>

                            <!-- Action buttons for production stages -->
                            @if(in_array($event->event_type, ['graphics', 'printing', 'mounting', 'proof']) && $event->status !== 'completed')
                                <div class="mt-2">
                                    @if($event->status === 'pending')
                                        <button class="btn btn-sm btn-primary" onclick="startStage('{{ $event->event_type }}')">
                                            <i class="fas fa-play"></i> Start
                                        </button>
                                    @endif
                                    @if($event->status === 'in_progress')
                                        <button class="btn btn-sm btn-success" onclick="completeStage('{{ $event->event_type }}')">
                                            <i class="fas fa-check"></i> Complete
                                        </button>
                                    @endif
                                </div>
                            @endif

                            <!-- User info -->
                            @if($event->user_name)
                                <div class="mt-2">
                                    <small class="text-muted">
                                        <i class="fas fa-user"></i> {{ $event->user_name }}
                                    </small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

<style>
.timeline {
    position: relative;
    padding: 0;
    list-style: none;
}

.timeline:before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 40px;
    width: 4px;
    background: #e9ecef;
}

.timeline-item {
    position: relative;
    padding-left: 90px;
    margin-bottom: 40px;
}

.timeline-marker {
    position: absolute;
    left: 24px;
    width: 36px;
    height: 36px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    z-index: 2;
}

.timeline-content {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border: 1px solid #dee2e6;
}

.timeline-item.completed .timeline-content {
    background: #e7f5e7;
    border-color: #28a745;
}

.timeline-item.in_progress .timeline-content {
    background: #e7f3ff;
    border-color: #007bff;
}

.timeline-item.failed .timeline-content {
    background: #ffe7e7;
    border-color: #dc3545;
}

.timeline-item.cancelled .timeline-content {
    background: #fff3cd;
    border-color: #ffc107;
}
</style>

<script>
function startStage(stage) {
    if (!confirm(`Start ${stage} stage?`)) return;
    
    fetch(`{{ route('admin.bookings.timeline.start-stage', $booking->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ stage: stage })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function completeStage(stage) {
    if (!confirm(`Complete ${stage} stage?`)) return;
    
    fetch(`{{ route('admin.bookings.timeline.complete-stage', $booking->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ stage: stage })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function rebuildTimeline() {
    if (!confirm('Rebuild timeline from scratch? This will reset all timeline events.')) return;
    
    fetch(`{{ route('admin.bookings.timeline.rebuild', $booking->id) }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}
</script>
@endsection
