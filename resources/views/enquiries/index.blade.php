@extends('layouts.app')

@section('title', 'My Enquiries')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2><i class="fas fa-file-alt me-2"></i>My Enquiries</h2>
            <p class="text-muted">Track all your hoarding enquiries and their status</p>
        </div>
        <div class="col-md-4 text-end">
            <a href="{{ route('hoardings.index') }}" class="btn btn-primary">
                <i class="fas fa-plus me-2"></i>Create New Enquiry
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <select class="form-select" id="statusFilter">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="accepted">Accepted</option>
                        <option value="rejected">Rejected</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <input type="text" class="form-control" id="searchInput" placeholder="Search by hoarding name or location...">
                </div>
                <div class="col-md-3 text-end">
                    <button class="btn btn-secondary" id="resetFilters">
                        <i class="fas fa-redo me-1"></i>Reset
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Enquiries List -->
    <div id="enquiriesList">
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
            <p class="mt-3 text-muted">Loading enquiries...</p>
        </div>
    </div>

    <!-- Pagination -->
    <div id="paginationContainer" class="mt-4"></div>
</div>

<style>
.enquiry-card {
    transition: transform 0.2s, box-shadow 0.2s;
}
.enquiry-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.status-badge {
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 0.85rem;
    font-weight: 600;
}
</style>

@push('scripts')
<script>
let currentPage = 1;
let filters = {};

$(document).ready(function() {
    loadEnquiries();
    
    // Filter change
    $('#statusFilter').on('change', function() {
        filters.status = $(this).val();
        currentPage = 1;
        loadEnquiries();
    });
    
    // Search
    let searchTimeout;
    $('#searchInput').on('input', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            filters.search = $(this).val();
            currentPage = 1;
            loadEnquiries();
        }, 500);
    });
    
    // Reset filters
    $('#resetFilters').on('click', function() {
        $('#statusFilter').val('');
        $('#searchInput').val('');
        filters = {};
        currentPage = 1;
        loadEnquiries();
    });
});

function loadEnquiries() {
    const params = new URLSearchParams({
        page: currentPage,
        ...filters
    });
    
    $.ajax({
        url: '/api/v1/customer/enquiries?' + params.toString(),
        method: 'GET',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                renderEnquiries(response.data.data);
                renderPagination(response.data);
            }
        },
        error: function(xhr) {
            showError('Failed to load enquiries');
        }
    });
}

function renderEnquiries(enquiries) {
    const container = $('#enquiriesList');
    container.empty();
    
    if (enquiries.length === 0) {
        container.html(`
            <div class="card">
                <div class="card-body text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No enquiries found</h5>
                    <p class="text-muted">Create your first enquiry to get started</p>
                    <a href="{{ route('hoardings.index') }}" class="btn btn-primary mt-2">
                        Browse Hoardings
                    </a>
                </div>
            </div>
        `);
        return;
    }
    
    enquiries.forEach(enquiry => {
        const statusColors = {
            pending: 'warning',
            accepted: 'success',
            rejected: 'danger',
            cancelled: 'secondary'
        };
        
        const statusColor = statusColors[enquiry.status] || 'secondary';
        const hasOffers = enquiry.offers && enquiry.offers.length > 0;
        const offersCount = hasOffers ? enquiry.offers.length : 0;
        
        const card = `
            <div class="card enquiry-card mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h5 class="mb-2">
                                <i class="fas fa-building me-2 text-primary"></i>
                                ${enquiry.hoarding.name}
                            </h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-map-marker-alt me-1"></i>
                                ${enquiry.hoarding.location}, ${enquiry.hoarding.city}
                            </p>
                            <div class="mb-2">
                                <span class="badge bg-info me-2">
                                    <i class="fas fa-calendar me-1"></i>
                                    ${formatDate(enquiry.preferred_start_date)} - ${formatDate(enquiry.preferred_end_date)}
                                </span>
                                <span class="badge bg-secondary">
                                    ${enquiry.hoarding.type}
                                </span>
                            </div>
                            ${enquiry.message ? `<p class="small text-muted mb-0"><em>"${enquiry.message}"</em></p>` : ''}
                        </div>
                        <div class="col-md-4 text-end">
                            <span class="status-badge bg-${statusColor} text-white d-inline-block mb-3">
                                ${capitalizeFirst(enquiry.status)}
                            </span>
                            <div class="mb-2">
                                ${hasOffers ? `<span class="badge bg-primary">${offersCount} Offer(s)</span>` : ''}
                            </div>
                            <div class="btn-group-vertical w-100">
                                <a href="/threads/${enquiry.thread?.id || '#'}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-comments me-1"></i>View Thread
                                </a>
                                ${enquiry.status === 'pending' ? `
                                    <button class="btn btn-danger btn-sm" onclick="cancelEnquiry(${enquiry.id})">
                                        <i class="fas fa-times me-1"></i>Cancel
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                    <hr>
                    <div class="d-flex justify-content-between align-items-center">
                        <small class="text-muted">
                            <i class="fas fa-clock me-1"></i>
                            Created ${formatDateTime(enquiry.created_at)}
                        </small>
                        <small class="text-muted">
                            Enquiry #${enquiry.id}
                        </small>
                    </div>
                </div>
            </div>
        `;
        
        container.append(card);
    });
}

function renderPagination(data) {
    const container = $('#paginationContainer');
    container.empty();
    
    if (data.last_page <= 1) return;
    
    let html = '<nav><ul class="pagination justify-content-center">';
    
    // Previous button
    html += `
        <li class="page-item ${data.current_page === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page - 1}); return false;">Previous</a>
        </li>
    `;
    
    // Page numbers
    for (let i = 1; i <= data.last_page; i++) {
        if (i === 1 || i === data.last_page || (i >= data.current_page - 2 && i <= data.current_page + 2)) {
            html += `
                <li class="page-item ${i === data.current_page ? 'active' : ''}">
                    <a class="page-link" href="#" onclick="goToPage(${i}); return false;">${i}</a>
                </li>
            `;
        } else if (i === data.current_page - 3 || i === data.current_page + 3) {
            html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
        }
    }
    
    // Next button
    html += `
        <li class="page-item ${data.current_page === data.last_page ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="goToPage(${data.current_page + 1}); return false;">Next</a>
        </li>
    `;
    
    html += '</ul></nav>';
    container.html(html);
}

function goToPage(page) {
    currentPage = page;
    loadEnquiries();
    $('html, body').animate({ scrollTop: 0 }, 'fast');
}

function cancelEnquiry(enquiryId) {
    if (!confirm('Are you sure you want to cancel this enquiry?')) return;
    
    $.ajax({
        url: `/api/v1/customer/enquiries/${enquiryId}/cancel`,
        method: 'POST',
        headers: {
            'Authorization': 'Bearer ' + localStorage.getItem('api_token')
        },
        success: function(response) {
            if (response.success) {
                showSuccess('Enquiry cancelled successfully');
                loadEnquiries();
            }
        },
        error: function(xhr) {
            showError('Failed to cancel enquiry');
        }
    });
}

// Utilities
function formatDate(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' at ' + 
           date.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
}

function capitalizeFirst(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
}

function showSuccess(message) {
    alert(message); // Replace with toast notification
}

function showError(message) {
    alert(message); // Replace with toast notification
}
</script>
@endpush
@endsection
