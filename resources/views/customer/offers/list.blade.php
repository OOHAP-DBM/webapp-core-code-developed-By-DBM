@extends('layouts.customer')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="bi bi-envelope-open"></i> Received Offers</h2>

    <!-- Filter Tabs -->
    <ul class="nav nav-tabs mb-4" id="offerTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="all-tab" data-bs-toggle="tab" data-bs-target="#all" 
                    type="button" role="tab">All Offers</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="expiring-tab" data-bs-toggle="tab" data-bs-target="#expiring" 
                    type="button" role="tab">Expiring Soon</button>
        </li>
    </ul>

    <!-- Loading Spinner -->
    <div id="loadingSpinner" class="text-center py-5 d-none">
        <div class="spinner-border text-primary" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>
    </div>

    <!-- Offers Container -->
    <div id="offersContainer">
        <!-- Will be populated by JavaScript -->
    </div>
</div>

<!-- Accept Confirmation Modal -->
<div class="modal fade" id="acceptModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="bi bi-check-circle"></i> Accept Offer</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="acceptModalBody">
                <!-- Will be populated dynamically -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-success" id="confirmAcceptBtn">
                    <i class="bi bi-check-circle"></i> Accept Offer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingSpinner = document.getElementById('loadingSpinner');
    const offersContainer = document.getElementById('offersContainer');
    const acceptModal = new bootstrap.Modal(document.getElementById('acceptModal'));
    let selectedOfferId = null;

    function getStatusBadge(status) {
        const badges = {
            'sent': '<span class="badge bg-info">Pending</span>',
            'accepted': '<span class="badge bg-success">Accepted</span>',
            'rejected': '<span class="badge bg-danger">Rejected</span>',
            'expired': '<span class="badge bg-warning text-dark">Expired</span>'
        };
        return badges[status] || status;
    }

    function formatPrice(price, priceType) {
        const formatted = `₹${parseFloat(price).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})}`;
        const types = {
            'total': ' (Total)',
            'monthly': '/month',
            'weekly': '/week',
            'daily': '/day'
        };
        return formatted + (types[priceType] || '');
    }

    function getDaysUntilExpiry(validUntil) {
        if (!validUntil) return null;
        
        const date = new Date(validUntil);
        const now = new Date();
        const diff = date - now;
        return Math.floor(diff / (1000 * 60 * 60 * 24));
    }

    function getExpiryBadge(validUntil) {
        const days = getDaysUntilExpiry(validUntil);
        
        if (days === null) {
            return '<span class="badge bg-secondary">No expiry</span>';
        } else if (days < 0) {
            return '<span class="badge bg-danger">Expired</span>';
        } else if (days === 0) {
            return '<span class="badge bg-danger"><i class="bi bi-clock"></i> Expires today</span>';
        } else if (days <= 3) {
            return `<span class="badge bg-danger"><i class="bi bi-clock"></i> ${days} days left</span>`;
        } else if (days <= 7) {
            return `<span class="badge bg-warning text-dark"><i class="bi bi-clock"></i> ${days} days left</span>`;
        } else {
            return `<span class="badge bg-info"><i class="bi bi-clock"></i> ${days} days left</span>`;
        }
    }

    function createOfferCard(offer) {
        const hoardingTitle = offer.price_snapshot?.hoarding_title || 'N/A';
        const hoardingLocation = offer.price_snapshot?.hoarding_location || '';
        const vendorName = offer.vendor?.name || offer.price_snapshot?.vendor_name || 'N/A';
        const description = offer.description || 'No description provided';
        const truncatedDesc = description.length > 150 ? description.substring(0, 150) + '...' : description;
        const canAccept = offer.status === 'sent' && (!offer.valid_until || getDaysUntilExpiry(offer.valid_until) >= 0);

        return `
            <div class="card mb-3 ${canAccept ? 'border-primary' : ''}">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0">
                                    ${hoardingTitle}
                                    <span class="badge bg-primary ms-2">v${offer.version}</span>
                                </h5>
                                ${getStatusBadge(offer.status)}
                            </div>
                            
                            <p class="text-muted mb-2">
                                <i class="bi bi-geo-alt"></i> ${hoardingLocation}
                            </p>
                            
                            <p class="mb-2">
                                <strong>Vendor:</strong> ${vendorName}
                            </p>
                            
                            <p class="mb-2" id="desc-${offer.id}">
                                ${truncatedDesc}
                                ${description.length > 150 ? `<a href="#" onclick="toggleDescription(${offer.id}); return false;" class="text-primary">Read more</a>` : ''}
                            </p>
                            <div id="full-desc-${offer.id}" class="d-none">
                                ${description}
                                <a href="#" onclick="toggleDescription(${offer.id}); return false;" class="text-primary">Read less</a>
                            </div>

                            <button class="btn btn-sm btn-outline-secondary" type="button" 
                                    data-bs-toggle="collapse" data-bs-target="#details-${offer.id}">
                                <i class="bi bi-info-circle"></i> View Details
                            </button>
                            
                            <div class="collapse mt-3" id="details-${offer.id}">
                                <div class="card card-body bg-light">
                                    <h6>Booking Details</h6>
                                    <p class="mb-1"><strong>Period:</strong> ${offer.price_snapshot?.preferred_start_date || 'N/A'} to ${offer.price_snapshot?.preferred_end_date || 'N/A'}</p>
                                    <p class="mb-1"><strong>Duration:</strong> ${offer.price_snapshot?.duration_days || 'N/A'} days (${offer.price_snapshot?.duration_type || 'N/A'})</p>
                                    <p class="mb-1"><strong>Hoarding Size:</strong> ${offer.price_snapshot?.hoarding_width || 'N/A'} x ${offer.price_snapshot?.hoarding_height || 'N/A'}</p>
                                    <p class="mb-1"><strong>Original Price:</strong> ₹${parseFloat(offer.price_snapshot?.original_price || 0).toLocaleString('en-IN')}</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4 text-end">
                            <div class="mb-3">
                                <h3 class="text-primary mb-0">${formatPrice(offer.price, offer.price_type)}</h3>
                                ${getExpiryBadge(offer.valid_until)}
                            </div>
                            
                            ${canAccept ? `
                                <button class="btn btn-success w-100 mb-2" onclick="showAcceptModal(${offer.id})">
                                    <i class="bi bi-check-circle"></i> Accept Offer
                                </button>
                                <button class="btn btn-outline-danger w-100" onclick="rejectOffer(${offer.id})">
                                    <i class="bi bi-x-circle"></i> Reject
                                </button>
                            ` : ''}
                            
                            ${offer.status === 'accepted' ? `
                                <div class="alert alert-success mb-0">
                                    <i class="bi bi-check-circle-fill"></i> You accepted this offer
                                </div>
                            ` : ''}
                            
                            ${offer.status === 'rejected' ? `
                                <div class="alert alert-danger mb-0">
                                    <i class="bi bi-x-circle-fill"></i> You rejected this offer
                                </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            </div>`;
    }

    window.toggleDescription = function(offerId) {
        const shortDesc = document.getElementById(`desc-${offerId}`);
        const fullDesc = document.getElementById(`full-desc-${offerId}`);
        shortDesc.classList.toggle('d-none');
        fullDesc.classList.toggle('d-none');
    };

    async function loadOffers(expiringOnly = false) {
        loadingSpinner.classList.remove('d-none');
        offersContainer.innerHTML = '';

        try {
            const response = await fetch('/api/v1/offers', {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to load offers');
            }

            let offers = result.data;

            // Filter expiring soon (within 7 days)
            if (expiringOnly) {
                offers = offers.filter(offer => {
                    const days = getDaysUntilExpiry(offer.valid_until);
                    return days !== null && days >= 0 && days <= 7;
                });
            }

            if (offers.length === 0) {
                offersContainer.innerHTML = `
                    <div class="text-center py-5">
                        <i class="bi bi-inbox display-1 text-muted"></i>
                        <p class="mt-3 text-muted">${expiringOnly ? 'No offers expiring soon' : 'No offers received yet'}</p>
                    </div>`;
                return;
            }

            // Sort by status (sent first) then by expiry
            offers.sort((a, b) => {
                if (a.status === 'sent' && b.status !== 'sent') return -1;
                if (a.status !== 'sent' && b.status === 'sent') return 1;
                
                const daysA = getDaysUntilExpiry(a.valid_until);
                const daysB = getDaysUntilExpiry(b.valid_until);
                
                if (daysA === null) return 1;
                if (daysB === null) return -1;
                
                return daysA - daysB;
            });

            offersContainer.innerHTML = offers.map(offer => createOfferCard(offer)).join('');

        } catch (error) {
            offersContainer.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            loadingSpinner.classList.add('d-none');
        }
    }

    window.showAcceptModal = async function(offerId) {
        selectedOfferId = offerId;
        
        try {
            const response = await fetch(`/api/v1/offers/${offerId}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();
            const offer = result.data;

            document.getElementById('acceptModalBody').innerHTML = `
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i>
                    <strong>Important:</strong> Accepting this offer will automatically reject all other offers for this enquiry.
                </div>
                
                <h6>Offer Details</h6>
                <table class="table table-sm">
                    <tr><th width="40%">Hoarding:</th><td>${offer.price_snapshot?.hoarding_title || 'N/A'}</td></tr>
                    <tr><th>Vendor:</th><td>${offer.vendor?.name || 'N/A'}</td></tr>
                    <tr><th>Price:</th><td><strong class="text-success">${formatPrice(offer.price, offer.price_type)}</strong></td></tr>
                    <tr><th>Version:</th><td>v${offer.version}</td></tr>
                    <tr><th>Booking Period:</th><td>${offer.price_snapshot?.preferred_start_date || 'N/A'} to ${offer.price_snapshot?.preferred_end_date || 'N/A'}</td></tr>
                    <tr><th>Duration:</th><td>${offer.price_snapshot?.duration_days || 'N/A'} days</td></tr>
                    ${offer.valid_until ? `<tr><th>Valid Until:</th><td>${new Date(offer.valid_until).toLocaleString('en-IN')}</td></tr>` : ''}
                </table>
                
                ${offer.description ? `
                    <h6>Description</h6>
                    <p>${offer.description}</p>
                ` : ''}
            `;

            acceptModal.show();
        } catch (error) {
            alert('Failed to load offer details');
        }
    };

    document.getElementById('confirmAcceptBtn').addEventListener('click', async function() {
        if (!selectedOfferId) return;

        this.disabled = true;
        this.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Accepting...';

        try {
            const response = await fetch(`/api/v1/offers/${selectedOfferId}/accept`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to accept offer');
            }

            acceptModal.hide();
            alert('Offer accepted successfully!');
            loadOffers();
        } catch (error) {
            alert(error.message);
        } finally {
            this.disabled = false;
            this.innerHTML = '<i class="bi bi-check-circle"></i> Accept Offer';
        }
    });

    window.rejectOffer = async function(offerId) {
        if (!confirm('Are you sure you want to reject this offer?')) return;

        try {
            const response = await fetch(`/api/v1/offers/${offerId}/reject`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to reject offer');
            }

            alert('Offer rejected successfully');
            loadOffers();
        } catch (error) {
            alert(error.message);
        }
    };

    // Tab switching
    document.getElementById('all-tab').addEventListener('click', () => loadOffers(false));
    document.getElementById('expiring-tab').addEventListener('click', () => loadOffers(true));

    // Initial load
    loadOffers();
});
</script>
@endsection
