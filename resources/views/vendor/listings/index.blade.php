@extends('layouts.vendor')

@section('page-title', 'My Listings')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="mb-1">My Listings</h2>
        <p class="text-muted mb-0">Manage your OOH and DOOH inventory</p>
    </div>
    <div class="btn-group">
        <a href="{{ route('vendor.listings.create', ['type' => 'ooh']) }}" class="btn btn-vendor-primary">
            <i class="bi bi-plus-circle me-2"></i>Add OOH
        </a>
        <a href="{{ route('vendor.listings.create', ['type' => 'dooh']) }}" class="btn btn-outline-primary">
            <i class="bi bi-plus-circle me-2"></i>Add DOOH
        </a>
    </div>
</div>

<!-- Filters -->
<div class="vendor-card mb-4">
    <div class="vendor-card-body">
        <form action="{{ route('vendor.listings.index') }}" method="GET" class="row g-3">
            <div class="col-md-3">
                <input type="text" name="search" class="form-control" placeholder="Search listings..." value="{{ request('search') }}">
            </div>
            <div class="col-md-2">
                <select name="type" class="form-select">
                    <option value="">All Types</option>
                    <option value="ooh" {{ request('type') === 'ooh' ? 'selected' : '' }}>OOH</option>
                    <option value="dooh" {{ request('type') === 'dooh' ? 'selected' : '' }}>DOOH</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select">
                    <option value="">All Status</option>
                    <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="city" class="form-select">
                    <option value="">All Cities</option>
                    @foreach($cities ?? [] as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <div class="btn-group w-100">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-search"></i> Filter
                    </button>
                    <a href="{{ route('vendor.listings.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-x-circle"></i> Clear
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Listings Table -->
<div class="vendor-card">
    <div class="vendor-card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>
                            <input type="checkbox" class="form-check-input" id="selectAll">
                        </th>
                        <th>Image</th>
                        <th>Title & Location</th>
                        <th>Type</th>
                        <th>Size</th>
                        <th>Price/Month</th>
                        <th>Status</th>
                        <th>Bookings</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($listings ?? [] as $listing)
                        <tr>
                            <td>
                                <input type="checkbox" class="form-check-input listing-checkbox" value="{{ $listing->id }}">
                            </td>
                            <td>
                                @if($listing->image)
                                    <img src="{{ asset('storage/' . $listing->image) }}" 
                                         alt="{{ $listing->title }}" 
                                         class="rounded"
                                         style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <div class="bg-secondary rounded d-flex align-items-center justify-content-center"
                                         style="width: 60px; height: 60px;">
                                        <i class="bi bi-image text-white"></i>
                                    </div>
                                @endif
                            </td>
                            <td>
                                <div>
                                    <strong>{{ $listing->title }}</strong>
                                    <br>
                                    <small class="text-muted">
                                        <i class="bi bi-geo-alt"></i>
                                        {{ $listing->city }}, {{ $listing->state }}
                                    </small>
                                </div>
                            </td>
                            <td>
                                <span class="badge {{ $listing->media_type === 'dooh' ? 'bg-info' : 'bg-primary' }}">
                                    {{ strtoupper($listing->media_type ?? 'ooh') }}
                                </span>
                                <br>
                                <small class="text-muted">{{ ucfirst($listing->type) }}</small>
                            </td>
                            <td>{{ $listing->width }}' × {{ $listing->height }}'</td>
                            <td><strong>₹{{ number_format($listing->price_per_month, 0) }}</strong></td>
                            <td>
                                <span class="badge 
                                    @if($listing->status === 'approved') bg-success
                                    @elseif($listing->status === 'pending') bg-warning
                                    @elseif($listing->status === 'rejected') bg-danger
                                    @else bg-secondary
                                    @endif">
                                    {{ ucfirst($listing->status) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark">{{ $listing->bookings_count ?? 0 }}</span>
                            </td>
                            <td>
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('vendor.listings.edit', $listing->id) }}" 
                                       class="btn btn-outline-primary" 
                                       title="Edit">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button type="button" 
                                            class="btn btn-outline-info" 
                                            title="View" 
                                            onclick="viewListing({{ $listing->id }})">
                                        <i class="bi bi-eye"></i>
                                    </button>
                                    <button type="button" 
                                            class="btn btn-outline-danger" 
                                            title="Delete" 
                                            onclick="deleteListing({{ $listing->id }})">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5">
                                <i class="bi bi-inbox" style="font-size: 3rem; color: #ddd;"></i>
                                <p class="text-muted mt-3 mb-0">No listings found</p>
                                <a href="{{ route('vendor.listings.create', ['type' => 'ooh']) }}" class="btn btn-vendor-primary mt-3">
                                    <i class="bi bi-plus-circle me-2"></i>Add Your First Listing
                                </a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if(isset($listings) && $listings->hasPages())
        <div class="vendor-card-body border-top">
            {{ $listings->links() }}
        </div>
    @endif
</div>

<!-- Bulk Actions Bar (shown when items selected) -->
<div id="bulkActionsBar" class="position-fixed bottom-0 start-0 w-100 bg-primary text-white p-3 shadow-lg" style="display: none; z-index: 1050; margin-left: var(--sidebar-width);">
    <div class="container-fluid">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span id="selectedCount">0</span> items selected
            </div>
            <div class="btn-group">
                <button class="btn btn-light btn-sm" onclick="bulkUpdatePrice()">
                    <i class="bi bi-currency-rupee me-1"></i>Update Price
                </button>
                <button class="btn btn-light btn-sm" onclick="bulkUpdateStatus()">
                    <i class="bi bi-toggle-on me-1"></i>Change Status
                </button>
                <button class="btn btn-danger btn-sm" onclick="bulkDelete()">
                    <i class="bi bi-trash me-1"></i>Delete
                </button>
                <button class="btn btn-outline-light btn-sm" onclick="clearSelection()">
                    <i class="bi bi-x"></i> Cancel
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Select All
document.getElementById('selectAll')?.addEventListener('change', function() {
    const checkboxes = document.querySelectorAll('.listing-checkbox');
    checkboxes.forEach(cb => cb.checked = this.checked);
    updateBulkActions();
});

// Individual checkbox change
document.querySelectorAll('.listing-checkbox').forEach(cb => {
    cb.addEventListener('change', updateBulkActions);
});

function updateBulkActions() {
    const checked = document.querySelectorAll('.listing-checkbox:checked');
    const bulkBar = document.getElementById('bulkActionsBar');
    const selectedCount = document.getElementById('selectedCount');
    
    if (checked.length > 0) {
        bulkBar.style.display = 'block';
        selectedCount.textContent = checked.length;
    } else {
        bulkBar.style.display = 'none';
    }
}

function clearSelection() {
    document.querySelectorAll('.listing-checkbox').forEach(cb => cb.checked = false);
    document.getElementById('selectAll').checked = false;
    updateBulkActions();
}

function getSelectedIds() {
    return Array.from(document.querySelectorAll('.listing-checkbox:checked')).map(cb => cb.value);
}

function bulkUpdatePrice() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    const newPrice = prompt('Enter new price per month:');
    if (newPrice && !isNaN(newPrice)) {
        window.location.href = `/vendor/listings/bulk-update?ids=${ids.join(',')}&price=${newPrice}`;
    }
}

function bulkUpdateStatus() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    const status = prompt('Enter new status (active/inactive):');
    if (status) {
        window.location.href = `/vendor/listings/bulk-update?ids=${ids.join(',')}&status=${status}`;
    }
}

function bulkDelete() {
    const ids = getSelectedIds();
    if (ids.length === 0) return;
    
    if (confirm(`Are you sure you want to delete ${ids.length} listing(s)?`)) {
        // Implement delete logic
        alert('Bulk delete functionality will be implemented');
    }
}

function viewListing(id) {
    window.location.href = `/vendor/listings/${id}`;
}

function deleteListing(id) {
    if (confirm('Are you sure you want to delete this listing?')) {
        fetch(`/vendor/listings/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                window.location.reload();
            } else {
                alert('Failed to delete listing');
            }
        });
    }
}
</script>
@endpush
