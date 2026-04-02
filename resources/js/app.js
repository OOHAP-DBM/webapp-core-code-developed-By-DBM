import './bootstrap';

/**
 * Global: Closes vendor sidebar on mobile before navigating back
 * Usage: <button onclick="handleBackWithSidebarClose()">...</button>
 */
window.handleBackWithSidebarClose = function() {
    const sidebar = document.getElementById('vendor-sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    if (sidebar && overlay && window.innerWidth <= 1023) {
        sidebar.classList.add('hidden');
        overlay.classList.remove('active');
        document.body.classList.remove('sidebar-open');
        setTimeout(() => window.history.back(), 150);
    } else {
        window.history.back();
    }
};
