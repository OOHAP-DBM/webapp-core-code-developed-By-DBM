@props(['notification'])

@php
    $data = $notification->data ?? [];
@endphp

<div class="notification-row {{ $notification->read_at ? 'read' : 'unread' }}"
     data-id="{{ $notification->id }}">

    <!-- LEFT -->
    <div class="notification-content">
        <h6>
            {{ $data['title'] ?? 'Notification' }}
        </h6>

        <p>
            {{ $data['message'] ?? '' }}
        </p>

        <span class="text-muted" style="font-size:12px;">
            {{ $notification->created_at->diffForHumans() }}
        </span>
    </div>

    <!-- RIGHT -->
    <div class="notification-action">
        @if(!$notification->read_at)
            <a href="javascript:void(0)"
               class="mark-as-read"
               data-id="{{ $notification->id }}">
                Mark as read
            </a>
        @endif
    </div>
</div>
