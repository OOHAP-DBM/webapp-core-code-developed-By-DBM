@extends('layouts.customer')

@section('title', 'Notifications')

@push('styles')
<style>
/* ===== PAGE WRAPPER ===== */
.notifications-wrapper {
    max-width: 820px;
    margin: 0 auto;
}

/* ===== HEADER ===== */
.notifications-header {
    background: #ffffff;
    border-radius: 12px;
    padding: 24px 28px;
    margin-bottom: 20px;
    box-shadow: 0 2px 8px rgba(0,0,0,.05);
}

/* ===== LIST CONTAINER ===== */
.notifications-container {
    background: #f3f8ff;
    border-radius: 12px;
    overflow: hidden;
}

/* ===== ROW STYLES ===== */
.notification-row {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    padding: 18px 24px;
    border-bottom: 1px solid #e5e7eb;
    transition: background .2s ease;
}

.notification-row.unread {
    background: #eef5ff;
}

.notification-row.read {
    background: #ffffff;
}

.notification-row:hover {
    background: #e8f1ff;
}

.notification-row:last-child {
    border-bottom: none;
}

/* ===== CONTENT ===== */
.notification-content {
    max-width: 78%;
}

.notification-content h6 {
    font-size: 14px;
    font-weight: 600;
    margin-bottom: 4px;
}

.notification-content p {
    font-size: 13px;
    margin-bottom: 6px;
    color: #4b5563;
}

/* ===== ACTION ===== */
.notification-action {
    white-space: nowrap;
}

.notification-action a {
    font-size: 13px;
    font-weight: 500;
    color: #2563eb;
    text-decoration: none;
}

.notification-action a:hover {
    text-decoration: underline;
}
</style>
@endpush

@section('content')
<div class="container">
    <div class="notifications-wrapper">

       <!-- HEADER -->
        <div class="flex items-center justify-between mb-6">
            <h2 class="text-xl font-bold text-gray-900">
                Notifications
            </h2>

            @if(isset($notifications) && $notifications->whereNull('read_at')->count() > 0)
                <button
                    onclick="markAllAsRead()"
                    class="bg-blue-600 hover:bg-blue-700 text-white text-sm font-medium
                        px-4 py-2 rounded-md transition cursor-pointer">
                    Mark all as read
                </button>
            @endif
        </div>

        <!-- LIST -->
        <div class="notifications-container">
            @forelse($notifications as $notification)
                <x-notification-item :notification="$notification" />
            @empty
                <div class="text-center py-5 bg-white">
                    <p class="text-muted mb-0">No notifications found</p>
                </div>
            @endforelse
        </div>

        <!-- PAGINATION -->
        @if($notifications->hasPages())
            <div class="mt-3">
                {{ $notifications->links() }}
            </div>
        @endif

    </div>
</div>
<script>
function markAllAsRead() {
    fetch("{{ route('customer.notifications.read-all') }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        }
    }).then(() => {
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });
}
</script>
<script>
document.addEventListener('click', function (e) {

    const btn = e.target.closest('.mark-as-read');
    if (!btn) return;

    e.preventDefault();

    const id = btn.dataset.id;

    fetch(`/customer/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute('content')
        }
    }).then(() => {
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });
});
</script>
@endsection
