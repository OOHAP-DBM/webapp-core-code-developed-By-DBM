/**
 * Shortlist/Wishlist Module (PROMPT 50)
 * Handles adding/removing hoardings from user's shortlist
 * Provides real-time UI updates and cross-device sync
 */

class ShortlistManager {
    constructor() {
        this.baseUrl = '/customer/shortlist';
        this.init();
    }

    /**
     * Initialize shortlist functionality
     */
    init() {
        // Update count on page load
        this.updateCount();
        
        // Bind click handlers to all wishlist buttons
        this.bindWishlistButtons();
    }

    /**
     * Bind click handlers to wishlist heart icons
     */
    bindWishlistButtons() {
        document.addEventListener('click', (e) => {
            const btn = e.target.closest('.btn-wishlist');
            if (btn) {
                e.preventDefault();
                e.stopPropagation();
                const hoardingId = btn.dataset.hoardingId;
                this.toggle(hoardingId, btn);
            }
        });
    }

    /**
     * Toggle wishlist status for a hoarding
     * @param {number} hoardingId - The hoarding ID
     * @param {HTMLElement} btn - The clicked button element
     */
    async toggle(hoardingId, btn) {
        try {
            // Disable button during request
            btn.disabled = true;
            
            const response = await fetch(`${this.baseUrl}/toggle/${hoardingId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();

            if (data.success) {
                // Update button UI
                this.updateButtonUI(btn, data.isWishlisted);
                
                // Update count badge
                this.updateCountBadge(data.count);
                
                // Show toast notification
                this.showToast(data.message);
            }
        } catch (error) {
            console.error('Error toggling wishlist:', error);
            this.showToast('Failed to update shortlist', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    /**
     * Update button UI based on wishlist status
     * @param {HTMLElement} btn - The button element
     * @param {boolean} isWishlisted - Whether item is wishlisted
     */
    updateButtonUI(btn, isWishlisted) {
        const icon = btn.querySelector('i');
        
        if (isWishlisted) {
            // Filled heart
            icon.classList.remove('bi-heart');
            icon.classList.add('bi-heart-fill');
            btn.classList.add('active');
            btn.setAttribute('title', 'Remove from shortlist');
        } else {
            // Outline heart
            icon.classList.remove('bi-heart-fill');
            icon.classList.add('bi-heart');
            btn.classList.remove('active');
            btn.setAttribute('title', 'Add to shortlist');
        }
    }

    /**
     * Update the shortlist count badge in navigation
     * @param {number} count - The new count
     */
    updateCountBadge(count) {
        const badge = document.getElementById('shortlist-count');
        if (badge) {
            badge.textContent = count;
            badge.style.display = count > 0 ? 'inline-flex' : 'none';
        }
    }

    /**
     * Get current shortlist count from server
     */
    async updateCount() {
        try {
            const response = await fetch(`${this.baseUrl}/count`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            
            if (data.success) {
                this.updateCountBadge(data.count);
            }
        } catch (error) {
            console.error('Error fetching shortlist count:', error);
        }
    }

    /**
     * Check if a specific hoarding is wishlisted
     * @param {number} hoardingId - The hoarding ID
     * @returns {Promise<boolean>}
     */
    async check(hoardingId) {
        try {
            const response = await fetch(`${this.baseUrl}/check/${hoardingId}`, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                }
            });

            const data = await response.json();
            return data.isWishlisted || false;
        } catch (error) {
            console.error('Error checking wishlist status:', error);
            return false;
        }
    }

    /**
     * Show toast notification
     * @param {string} message - The message to display
     * @param {string} type - The notification type (success, error, info)
     */
    showToast(message, type = 'success') {
        // Check if Bootstrap Toast is available
        const toastContainer = document.getElementById('toast-container');
        if (!toastContainer) {
            // Fallback to simple alert
            console.log(message);
            return;
        }

        // Create toast element
        const toastId = `toast-${Date.now()}`;
        const bgClass = type === 'error' ? 'bg-danger' : 'bg-success';
        
        const toastHTML = `
            <div id="${toastId}" class="toast align-items-center text-white ${bgClass} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        <i class="bi bi-${type === 'error' ? 'x-circle' : 'check-circle'}"></i>
                        ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `;
        
        toastContainer.insertAdjacentHTML('beforeend', toastHTML);
        
        const toastElement = document.getElementById(toastId);
        const toast = new bootstrap.Toast(toastElement, {
            autohide: true,
            delay: 3000
        });
        
        toast.show();
        
        // Remove from DOM after hidden
        toastElement.addEventListener('hidden.bs.toast', () => {
            toastElement.remove();
        });
    }
}

// Initialize on DOM ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        window.shortlistManager = new ShortlistManager();
    });
} else {
    window.shortlistManager = new ShortlistManager();
}
