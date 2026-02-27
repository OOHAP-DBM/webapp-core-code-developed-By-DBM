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
            <form action="{{ route('customer.notifications.read', $notification->id) }}" method="POST" style="display:inline">
                @csrf
                <button type="submit" class="text-xs text-blue-600 hover:text-blue-800 bg-transparent border-0 p-0 m-0 cursor-pointer">Mark as read</button>
            </form>
        @endif
    </div>
</div>
