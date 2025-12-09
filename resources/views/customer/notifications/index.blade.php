@extends('layouts.customer')

@section('title', 'Notifications - OOHAPP')

@push('styles')
<style>
    .notifications-header {
        background: white;
        border-radius: 16px;
        padding: 32px;
        margin-bottom: 32px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .notifications-container {
        background: white;
        border-radius: 16px;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        overflow: hidden;
    }
</style>
@endpush

@section('content')
<div class="container-fluid px-4">
    <!-- Notifications Header -->
    <div class="notifications-header">
        <div class="row align-items-center">
            <div class="col-md-6">
                <h2 class="mb-2">Notifications</h2>
                <p class="text-muted mb-0">Stay updated with your activities</p>
            </div>
            <div class="col-md-6 text-end">
                @if(isset($notifications) && $notifications->where('read_at', null)->count() > 0)
                <button class="btn btn-outline-primary" onclick="markAllAsRead()">
                    <i class="bi bi-check-all me-2"></i>Mark All as Read
                </button>
                @endif
            </div>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="notifications-container">
        @forelse($notifications ?? [] as $notification)
        <x-notification-item :notification="$notification" />
        @empty
        <div class="text-center py-5">
            <i class="bi bi-bell-slash" style="font-size: 64px; color: #cbd5e1;"></i>
            <h4 class="mt-3">No notifications yet</h4>
            <p class="text-muted">We'll notify you when something important happens</p>
        </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($notifications) && $notifications->hasPages())
    <div class="mt-4">
        {{ $notifications->links() }}
    </div>
    @endif
</div>
@endsection

@push('scripts')
<script>
function markAllAsRead() {
    fetch('/api/v1/customer/notifications/read-all', {
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('token'),
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            window.location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

// Mark individual notification as read on click
document.querySelectorAll('.notification-item.unread').forEach(item => {
    item.addEventListener('click', function() {
        const notificationId = this.dataset.notificationId;
        if (notificationId) {
            fetch(`/api/v1/customer/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + localStorage.getItem('token'),
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
        }
    });
});
</script>
@endpush
