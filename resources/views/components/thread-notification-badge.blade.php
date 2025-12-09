{{-- Thread Notification Badge Component --}}
{{-- Include in navigation to show unread message count --}}

<div class="position-relative d-inline-block" id="threadNotificationBadge">
    <a href="{{ route($role . '.threads.index') }}" class="nav-link">
        <i class="bi bi-chat-dots fs-5"></i>
        <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" 
              id="unreadThreadCount" style="display: none;">
            0
        </span>
    </a>
</div>

<script>
(function() {
    function loadUnreadThreadCount() {
        const role = '{{ $role ?? "customer" }}';
        fetch(`/${role}/threads/unread-count`, {
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const badge = document.getElementById('unreadThreadCount');
                if (badge) {
                    badge.textContent = data.unread_count;
                    badge.style.display = data.unread_count > 0 ? 'inline-block' : 'none';
                }
            }
        })
        .catch(error => {
            console.error('Error loading unread count:', error);
        });
    }
    
    // Load on page load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadUnreadThreadCount);
    } else {
        loadUnreadThreadCount();
    }
    
    // Refresh every 30 seconds
    setInterval(loadUnreadThreadCount, 30000);
})();
</script>
