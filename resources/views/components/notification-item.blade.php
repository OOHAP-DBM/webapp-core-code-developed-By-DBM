{{-- 
    Notification Item Component
    Props: $notification
--}}
@props(['notification'])

<div class="notification-item {{ $notification->read_at ? '' : 'unread' }} p-3 border-bottom">
    <div class="d-flex">
        <!-- Icon -->
        <div class="notification-icon me-3">
            @switch($notification->type)
                @case('booking')
                    <div class="icon-circle bg-primary bg-opacity-10">
                        <i class="bi bi-calendar-check text-primary"></i>
                    </div>
                    @break
                @case('payment')
                    <div class="icon-circle bg-success bg-opacity-10">
                        <i class="bi bi-credit-card text-success"></i>
                    </div>
                    @break
                @case('enquiry')
                    <div class="icon-circle bg-info bg-opacity-10">
                        <i class="bi bi-envelope text-info"></i>
                    </div>
                    @break
                @case('quotation')
                    <div class="icon-circle bg-warning bg-opacity-10">
                        <i class="bi bi-file-text text-warning"></i>
                    </div>
                    @break
                @default
                    <div class="icon-circle bg-secondary bg-opacity-10">
                        <i class="bi bi-bell text-secondary"></i>
                    </div>
            @endswitch
        </div>

        <!-- Content -->
        <div class="flex-grow-1">
            <div class="d-flex justify-content-between align-items-start">
                <div>
                    <h6 class="mb-1 fw-semibold">{{ $notification->title }}</h6>
                    <p class="mb-1 text-muted small">{{ $notification->message }}</p>
                    <span class="text-muted" style="font-size: 0.75rem;">
                        <i class="bi bi-clock"></i> {{ $notification->created_at->diffForHumans() }}
                    </span>
                </div>
                
                @if(!$notification->read_at)
                <span class="badge bg-primary rounded-circle" style="width: 8px; height: 8px;"></span>
                @endif
            </div>

            <!-- Action Button (if applicable) -->
            @if($notification->action_url)
            <a href="{{ $notification->action_url }}" class="btn btn-sm btn-outline-primary mt-2">
                {{ $notification->action_text ?? 'View Details' }}
            </a>
            @endif
        </div>
    </div>
</div>

<style>
.notification-item {
    transition: background-color 0.2s ease;
}

.notification-item:hover {
    background-color: rgba(0, 0, 0, 0.02);
}

.notification-item.unread {
    background-color: rgba(var(--bs-primary-rgb), 0.05);
}

.icon-circle {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

.icon-circle i {
    font-size: 1.25rem;
}
</style>
