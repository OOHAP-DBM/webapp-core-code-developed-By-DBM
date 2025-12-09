{{-- Booking Timeline Widget Component --}}
<div class="booking-timeline-widget">
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Booking Timeline</h5>
            <a href="{{ route('admin.bookings.timeline.index', $booking->id) }}" class="btn btn-sm btn-outline-primary">
                <i class="fas fa-expand"></i> View Full Timeline
            </a>
        </div>
        <div class="card-body">
            <!-- Progress Bar -->
            <div class="mb-4">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <small class="text-muted">Overall Progress</small>
                    <strong>{{ number_format($booking->getTimelineProgress(), 0) }}%</strong>
                </div>
                <div class="progress" style="height: 20px;">
                    <div class="progress-bar bg-success" 
                         role="progressbar" 
                         style="width: {{ $booking->getTimelineProgress() }}%;">
                    </div>
                </div>
            </div>

            <!-- Mini Timeline -->
            <div class="mini-timeline">
                @php
                    $events = $booking->timelineEvents()->limit(5)->get();
                    $currentStage = $booking->getCurrentStage();
                @endphp

                @if($currentStage)
                    <div class="alert alert-info mb-3">
                        <strong>Current Stage:</strong> {{ $currentStage->title }}
                        <span class="badge bg-{{ $currentStage->status_color }} float-end">
                            {{ $currentStage->status_label }}
                        </span>
                    </div>
                @endif

                @foreach($events as $event)
                    <div class="mini-timeline-item {{ $event->status }}">
                        <div class="d-flex align-items-center">
                            <div class="mini-timeline-icon bg-{{ $event->event_color }}">
                                <i class="fas {{ $event->event_icon }}"></i>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between">
                                    <div>
                                        <strong>{{ $event->title }}</strong>
                                        @if($event->version)
                                            <span class="badge bg-secondary">v{{ $event->version }}</span>
                                        @endif
                                    </div>
                                    <span class="badge bg-{{ $event->status_color }}">
                                        @if($event->status === 'completed')
                                            <i class="fas fa-check"></i>
                                        @elseif($event->status === 'in_progress')
                                            <i class="fas fa-spinner"></i>
                                        @else
                                            <i class="fas fa-clock"></i>
                                        @endif
                                    </span>
                                </div>
                                <small class="text-muted">{{ $event->description }}</small>
                            </div>
                        </div>
                    </div>
                @endforeach

                @if($booking->timelineEvents()->count() > 5)
                    <div class="text-center mt-3">
                        <small class="text-muted">
                            +{{ $booking->timelineEvents()->count() - 5 }} more events
                        </small>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
.mini-timeline-item {
    padding: 12px 0;
    border-bottom: 1px solid #e9ecef;
}

.mini-timeline-item:last-child {
    border-bottom: none;
}

.mini-timeline-icon {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 16px;
    flex-shrink: 0;
}

.mini-timeline-item.completed {
    opacity: 0.7;
}

.mini-timeline-item.in_progress {
    background-color: #f0f8ff;
}
</style>
