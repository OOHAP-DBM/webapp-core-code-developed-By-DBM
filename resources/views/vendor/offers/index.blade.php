@extends('layouts.vendor')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2><i class="bi bi-tag"></i> My Offers</h2>
        <a href="{{ route('vendor.enquiries.index') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Create New Offer
        </a>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form id="filterForm" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Status</label>
                    <select class="form-select" name="status" id="statusFilter">
                        <option value="">All Statuses</option>
                        <option value="draft">Draft</option>
                        <option value="sent">Sent</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="expired">Expired</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">From Date</label>
                    <input type="date" class="form-control" name="from_date" id="fromDate">
                </div>
                <div class="col-md-3">
                    <label class="form-label">To Date</label>
                    <input type="date" class="form-control" name="to_date" id="toDate">
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="button" class="btn btn-primary me-2" id="applyFilters">
                        <i class="bi bi-funnel"></i> Apply
                    </button>
                    <button type="button" class="btn btn-outline-secondary" id="clearFilters">
                        <i class="bi bi-x-circle"></i> Clear
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Offers Table -->
    <div class="card">
        <div class="card-body">
            <div id="loadingSpinner" class="text-center py-5 d-none">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>

            <div id="offersTable">
                <!-- Will be populated by JavaScript -->
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const loadingSpinner = document.getElementById('loadingSpinner');
    const offersTable = document.getElementById('offersTable');

    function getStatusBadge(status) {
        const badges = {
            'draft': '<span class="badge bg-secondary">Draft</span>',
            'sent': '<span class="badge bg-info">Sent</span>',
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

    function getValidUntilDisplay(validUntil) {
        if (!validUntil) {
            return '<span class="text-muted">No expiry</span>';
        }
        
        const date = new Date(validUntil);
        const now = new Date();
        const diff = date - now;
        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        
        if (days < 0) {
            return '<span class="text-danger">Expired</span>';
        } else if (days === 0) {
            return '<span class="text-danger">Today</span>';
        } else if (days <= 3) {
            return `<span class="text-danger">${days} days</span>`;
        } else if (days <= 7) {
            return `<span class="text-warning">${days} days</span>`;
        } else {
            return `<span class="text-muted">${days} days</span>`;
        }
    }

    function getActions(offer) {
        let actions = `<div class="btn-group">`;
        
        actions += `<a href="/vendor/offers/${offer.id}" class="btn btn-sm btn-outline-primary">
            <i class="bi bi-eye"></i> View
        </a>`;
        
        if (offer.status === 'draft') {
            actions += `<a href="/vendor/offers/${offer.id}/edit" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-pencil"></i> Edit
            </a>`;
            actions += `<button class="btn btn-sm btn-success" onclick="sendOffer(${offer.id})">
                <i class="bi bi-send"></i> Send
            </button>`;
            actions += `<button class="btn btn-sm btn-outline-danger" onclick="deleteOffer(${offer.id})">
                <i class="bi bi-trash"></i>
            </button>`;
        }
        
        actions += `</div>`;
        return actions;
    }

    async function loadOffers() {
        loadingSpinner.classList.remove('d-none');
        offersTable.innerHTML = '';

        try {
            const params = new URLSearchParams();
            const status = document.getElementById('statusFilter').value;
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;

            if (status) params.append('status', status);
            if (fromDate) params.append('from_date', fromDate);
            if (toDate) params.append('to_date', toDate);

            const response = await fetch(`/api/v1/offers?${params.toString()}`, {
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to load offers');
            }

            if (result.data.length === 0) {
                offersTable.innerHTML = `
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-inbox display-1"></i>
                        <p class="mt-3">No offers found</p>
                    </div>`;
                return;
            }

            let tableHTML = `
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Enquiry/Hoarding</th>
                                <th>Version</th>
                                <th>Customer</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Valid Until</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>`;

            result.data.forEach(offer => {
                const hoardingTitle = offer.price_snapshot?.hoarding_title || 'N/A';
                const customerName = offer.price_snapshot?.customer_name || 'N/A';
                const createdDate = new Date(offer.created_at).toLocaleDateString('en-IN');
                
                tableHTML += `
                    <tr>
                        <td>
                            <strong>Enquiry #${offer.enquiry_id}</strong><br>
                            <small class="text-muted">${hoardingTitle}</small>
                        </td>
                        <td><span class="badge bg-primary">v${offer.version}</span></td>
                        <td>${customerName}</td>
                        <td><strong>${formatPrice(offer.price, offer.price_type)}</strong></td>
                        <td>${getStatusBadge(offer.status)}</td>
                        <td>${getValidUntilDisplay(offer.valid_until)}</td>
                        <td>${createdDate}</td>
                        <td>${getActions(offer)}</td>
                    </tr>`;
            });

            tableHTML += `</tbody></table></div>`;
            offersTable.innerHTML = tableHTML;

        } catch (error) {
            offersTable.innerHTML = `
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle"></i> ${error.message}
                </div>`;
        } finally {
            loadingSpinner.classList.add('d-none');
        }
    }

    window.sendOffer = async function(offerId) {
        if (!confirm('Send this offer to the customer?')) return;

        try {
            const response = await fetch(`/api/v1/offers/${offerId}/send`, {
                method: 'PATCH',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Failed to send offer');
            }

            alert('Offer sent successfully!');
            loadOffers();
        } catch (error) {
            alert(error.message);
        }
    };

    window.deleteOffer = async function(offerId) {
        if (!confirm('Delete this draft offer?')) return;

        try {
            const response = await fetch(`/api/v1/offers/${offerId}`, {
                method: 'DELETE',
                headers: {
                    'Authorization': `Bearer ${localStorage.getItem('api_token')}`,
                    'Accept': 'application/json'
                }
            });

            if (!response.ok) {
                const result = await response.json();
                throw new Error(result.message || 'Failed to delete offer');
            }

            alert('Offer deleted successfully!');
            loadOffers();
        } catch (error) {
            alert(error.message);
        }
    };

    document.getElementById('applyFilters').addEventListener('click', loadOffers);
    document.getElementById('clearFilters').addEventListener('click', function() {
        document.getElementById('filterForm').reset();
        loadOffers();
    });

    // Initial load
    loadOffers();
});
</script>
@endsection
