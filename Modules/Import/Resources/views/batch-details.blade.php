@extends($layout)
@section('title', 'Inventory Details')
@section('page_title', 'Inventory Details')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
        <div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900">Inventory Details</h1>
            <p class="text-gray-600 text-sm mt-1">View rows, images, and manage each hoarding record</p>
        </div>
         <a href="{{ $isAdmin ? route('admin.import.enhanced') : route('vendor.import.enhanced') }}" class="w-full sm:w-auto text-center min-h-[44px] px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Back to Import List
        </a>
        <!-- <a href="{{ $isAdmin ? route('admin.import.enhanced') : route('vendor.import.enhanced') }}" class="w-full sm:w-auto text-center min-h-[44px] px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
            Back to Import List
        </a> -->
    </div>

    <div id="toastContainer" class="fixed top-4 left-3 right-3 sm:left-auto sm:right-4 z-50 space-y-2"></div>

    @php
        $isApprovedBatch = $batch->status === 'approved';
        $autoApprove = \App\Models\Setting::get('auto_hoarding_approval', false);
    @endphp

    <div id="rowEditorSection" class="bg-white rounded-xl shadow mb-6 {{ $isApprovedBatch ? 'hidden' : '' }}">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between gap-3 flex-wrap">
            <h2 class="text-lg font-semibold text-gray-900">Upload Summary</h2>
            @if(!$isAdmin)
                <button 
                    id="approveInventoryBtn"
                    data-auto-approve="{{ $autoApprove ? '1' : '0' }}"
                    class="w-full sm:w-auto min-h-[44px] px-4 py-2 rounded-lg text-white disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed touch-manipulation {{ $isApprovedBatch ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }}"
                    {{ !in_array($batch->status, ['processed', 'completed']) || $isApprovedBatch ? 'disabled' : '' }}
                >
                    {{ $isApprovedBatch 
                        ? 'Approved' 
                        : ($autoApprove ? 'Publish' : 'Send For Approval') 
                    }}
                </button>
            @endif
        </div>
        <div class="p-4 grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-3 text-sm">
            <div><span class="text-gray-500 ">Inventory ID</span><p class="font-semibold  mx-[1.2em]">#{{ $batch->id }}</p></div>
            <div><span class="text-gray-500">Type</span><p class="font-semibold">{{ strtoupper($batch->media_type) }}</p></div>
            <div><span class="text-gray-500">Status</span><p class="font-semibold" id="batchStatusText">{{ $batch->status }}</p></div>
            <div><span class="text-gray-500">Valid Rows</span><p class="font-semibold mx-[1.2em]" id="validCount">{{ $batch->valid_rows }}</p></div>
            <div><span class="text-gray-500">Invalid Rows</span><p class="font-semibold mx-[1.2em]" id="invalidCount">{{ $batch->invalid_rows }}</p></div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow mb-6">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">Search & Filters</h2>
        </div>
        <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
            <input id="searchInput" type="text" placeholder="Search by code or city " class="w-full min-h-[44px] border rounded-lg p-2" />
            <select id="statusFilter" class="w-full min-h-[44px] border rounded-lg p-2">
                <option value="">All statuses</option>
                <option value="valid">valid</option>
                <option value="invalid">invalid</option>
            </select>
            <button id="applyFilter" class="w-full min-h-[44px] px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 cursor-pointer touch-manipulation">Apply</button>
            <button id="resetFilter" class="w-full min-h-[44px] px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 cursor-pointer touch-manipulation">Reset</button>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
           
            <div class="w-full sm:w-auto flex flex-col sm:flex-row sm:items-center gap-3">
                
                <p id="paginationInfo" class="text-sm text-gray-500"></p>
            </div>
        </div>
        <div class="overflow-x-auto mx-3">
            <table class="w-full min-w-[1040px]">
                <thead class="bg-gray-50">
                    <tr>
                        
                        <th class="px-3 py-2 text-left text-sm">S.N.</th>
                        <th class="px-3 py-2 text-left text-sm">Image</th>
                        <th class="px-3 py-2 text-left text-sm">Code</th>
                        <th class="px-3 py-2 text-left text-sm">City</th>
                        <th class="px-3 py-2 text-left text-sm">Width</th>
                        <th class="px-3 py-2 text-left text-sm">Height</th>
                        <th class="px-3 py-2 text-left text-sm">Monthly Rental Price</th> 
                        <th class="px-3 py-2 text-left text-sm">Status</th>
                        <th class="px-3 py-2 text-left text-sm">Error</th>
                        <th class="px-3 py-2 text-left text-sm">Action</th>
                    </tr>
                </thead>
                <tbody id="rowsBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
        <div class="p-4 border-t border-gray-200 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
            <div class="flex items-center justify-between sm:justify-start gap-2">
                <label for="rowsPerPage" class="text-sm text-gray-600">Rows per page</label>
                <select id="rowsPerPage" class="min-h-[44px] border rounded-lg p-1.5 text-sm cursor-pointer">
                    <option value="10">10</option>
                    <option value="15" selected>15</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                </select>
            </div>
            <div class="w-full lg:w-auto flex items-center justify-between lg:justify-start gap-2">
                <button id="rowsPrevBtn" class="min-h-[44px] px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer touch-manipulation">Previous</button>
                <span id="rowsPageLabel" class="text-sm text-gray-600">Page 1 / 1</span>
                <button id="rowsNextBtn" class="min-h-[44px] px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed cursor-pointer touch-manipulation">Next</button>
            </div>
        </div>
    @include('import::components.editRow')

    </div>

</div>



@php
    $imageUrlTemplate = $isAdmin
        ? route('admin.import.enhanced.batch.image', ['batch' => $batch->id, 'imageName' => '__IMAGE__'])
        : route('vendor.import.enhanced.batch.image', ['batch' => $batch->id, 'imageName' => '__IMAGE__']);
@endphp

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
const API_BASE = '/api/import';
const BATCH_ID = @json($batch->id);
const IMAGE_URL_TEMPLATE = @json($imageUrlTemplate);
const INITIAL_BATCH_STATUS = @json($batch->status);

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
let currentRowsById = {};
let selectedRowIds = new Set();
let currentBatchStatus = (INITIAL_BATCH_STATUS || '').toLowerCase();
let rowsQueryState = {
    page: 1,
    per_page: 15,
    search: '',
    status: '',
};
let rowsPaginationState = {
    total: 0,
    per_page: 15,
    current_page: 1,
    last_page: 1,
};

function notify(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const item = document.createElement('div');
    item.className = `px-4 py-2 rounded text-white shadow ${type === 'error' ? 'bg-red-600' : 'bg-green-600'}`;
    item.textContent = message;
    container.appendChild(item);
    setTimeout(() => item.remove(), 2500);
}

async function api(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            ...(options.body instanceof FormData ? {} : { 'Content-Type': 'application/json' }),
            ...(options.headers || {}),
        },
        ...options,
    });

    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(data.message || 'Request failed');
    }

    return data;
}

function imageUrl(name) {
    return IMAGE_URL_TEMPLATE.replace('__IMAGE__', encodeURIComponent(name || ''));
}

function escapeHtml(text) {
    return String(text ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function setApproveButtonState(status, loading = false) {
    const approveBtn = document.getElementById('approveInventoryBtn');
    if (!approveBtn) return;

    const autoApprove = approveBtn.dataset.autoApprove === '1';

    approveBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'bg-gray-400');

    if (loading) {
        approveBtn.disabled = true;
        approveBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        approveBtn.innerHTML = autoApprove 
            ? 'Publishing...' 
            : 'Approving...';
        return;
    }

    if (status === 'approved') {
        approveBtn.disabled = true;
        approveBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        approveBtn.textContent = autoApprove ? 'Published' : 'Approved';
        return;
    }

    const canApprove = ['processed', 'completed'].includes(status);
    approveBtn.disabled = !canApprove;
    approveBtn.classList.add('bg-green-600');
    approveBtn.textContent = autoApprove ? 'Publish' : 'Send For Approval';
}

function applyBatchUiState(status, loading = false) {
    currentBatchStatus = (status || '').toLowerCase();
    setApproveButtonState(currentBatchStatus, loading);

    const rowEditorSection = document.getElementById('rowEditorSection');
    if (rowEditorSection) {
        if (currentBatchStatus === 'approved') {
            rowEditorSection.classList.add('hidden');
        } else {
            rowEditorSection.classList.remove('hidden');
        }
    }

    const selectAll = document.getElementById('rowsSelectAll');
    if (selectAll) {
        selectAll.checked = false;
        selectAll.disabled = currentBatchStatus === 'approved';
    }

    updateBulkDeleteButtonState();
}

function clearRowSelections() {
    selectedRowIds.clear();
    const selectAll = document.getElementById('rowsSelectAll');
    if (selectAll) {
        selectAll.checked = false;
    }
    updateBulkDeleteButtonState();
}

function updateBulkDeleteButtonState() {
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    if (!bulkDeleteBtn) return;

    const selectedCount = selectedRowIds.size;
    bulkDeleteBtn.textContent = `Delete Selected (${selectedCount})`;
    bulkDeleteBtn.disabled = currentBatchStatus === 'approved' || selectedCount === 0;
}

function syncSelectAllCheckbox() {
    const selectAll = document.getElementById('rowsSelectAll');
    if (!selectAll) return;

    const rowIds = Object.keys(currentRowsById);
    if (!rowIds.length || currentBatchStatus === 'approved') {
        selectAll.checked = false;
        selectAll.disabled = true;
        return;
    }

    selectAll.disabled = false;
    selectAll.checked = rowIds.every((rowId) => selectedRowIds.has(Number(rowId)));
}

function toggleRowSelection(rowId, checked) {
    const normalizedId = Number(rowId);
    if (checked) {
        selectedRowIds.add(normalizedId);
    } else {
        selectedRowIds.delete(normalizedId);
    }

    syncSelectAllCheckbox();
    updateBulkDeleteButtonState();
}

function toggleSelectAllRows(checked) {
    Object.keys(currentRowsById).forEach((rowId) => {
        const normalizedId = Number(rowId);
        if (checked) {
            selectedRowIds.add(normalizedId);
        } else {
            selectedRowIds.delete(normalizedId);
        }
    });

    document.querySelectorAll('.row-select-checkbox').forEach((checkbox) => {
        checkbox.checked = checked;
    });

    syncSelectAllCheckbox();
    updateBulkDeleteButtonState();
}

function renderRowsPagination() {
    const prevBtn = document.getElementById('rowsPrevBtn');
    const nextBtn = document.getElementById('rowsNextBtn');
    const pageLabel = document.getElementById('rowsPageLabel');

    if (prevBtn) {
        prevBtn.disabled = (rowsPaginationState.current_page || 1) <= 1;
    }

    if (nextBtn) {
        nextBtn.disabled = (rowsPaginationState.current_page || 1) >= (rowsPaginationState.last_page || 1);
    }

    if (pageLabel) {
        pageLabel.textContent = `Page ${rowsPaginationState.current_page || 1} / ${rowsPaginationState.last_page || 1}`;
    }
}

async function loadRows(page = rowsQueryState.page) {
    rowsQueryState.page = Math.max(1, Number(page || 1));
    rowsQueryState.search = document.getElementById('searchInput').value.trim();
    rowsQueryState.status = document.getElementById('statusFilter').value;
    rowsQueryState.per_page = Number(document.getElementById('rowsPerPage')?.value || rowsQueryState.per_page || 15);

    const query = new URLSearchParams({
        page: String(rowsQueryState.page),
        per_page: String(rowsQueryState.per_page),
    });
    if (rowsQueryState.search) query.append('search', rowsQueryState.search);
    if (rowsQueryState.status) query.append('status', rowsQueryState.status);

    try {
        const result = await api(`${API_BASE}/${BATCH_ID}?${query.toString()}`);
        const payload = result.data || {};
        const rows = payload.rows || [];
        const pagination = payload.pagination || {};
        const batch = payload.batch || {};

        rowsPaginationState = {
            total: pagination.total || 0,
            per_page: pagination.per_page || rowsQueryState.per_page,
            current_page: pagination.current_page || rowsQueryState.page,
            last_page: pagination.last_page || 1,
        };

        currentRowsById = rows.reduce((accumulator, row) => {
            accumulator[row.id] = row;
            return accumulator;
        }, {});

        clearRowSelections();

        document.getElementById('validCount').textContent = batch.valid_rows ?? '-';
        document.getElementById('invalidCount').textContent = batch.invalid_rows ?? '-';
        document.getElementById('batchStatusText').textContent = batch.status ?? '-';
        const from = rows.length ? (((rowsPaginationState.current_page - 1) * rowsPaginationState.per_page) + 1) : 0;
        const to = rows.length ? (from + rows.length - 1) : 0;
        document.getElementById('paginationInfo').textContent = `Showing ${from}-${to} of ${rowsPaginationState.total}`;
        applyBatchUiState(batch.status || currentBatchStatus, false);
        renderRowsPagination();

        const body = document.getElementById('rowsBody');
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="10" class="px-3 py-6 text-center text-gray-500">No rows found</td></tr>';
            syncSelectAllCheckbox();
            return;
        }

        body.innerHTML = rows.map((row, index) => {
            
            const isNoImageError = (row.error_message || '').toLowerCase().includes('no image found');
            const imageCell = row.image_name
                ? `<img src="${imageUrl(row.image_name)}" alt="${escapeHtml(row.image_name)}" class="h-12 w-16 object-cover rounded border">`
                : (isNoImageError
                ? `<div class="h-12 w-16 flex items-center justify-center rounded border border-red-400 bg-white px-1">
                    <span class="text-[10px] leading-tight text-red-500 text-center font-medium">No Image Found</span>
                </div>`
                : '<span class="text-gray-400">-</span>');

            const errorCell = isNoImageError
                ? ''
                : escapeHtml(row.error_message || '');

           const status = (row.status || '').toLowerCase();
            const serialNumber = ((rowsPaginationState.current_page - 1) * rowsPaginationState.per_page) + (index + 1);
            // Status badge — same pill style as screenshot
           // Status badge — EXACT match to your screenshot
            let statusBadge = '';
            if (status === 'valid') {
                statusBadge = `
                    <span class="inline-flex items-center justify-center px-6 py-1 rounded-full bg-[#D9F2E6] text-[#009A5C] font-medium text-sm min-w-[80px]">
                        Valid
                    </span>`;
            } else if (status === 'invalid') {
                statusBadge = `
                    <span class="inline-flex items-center justify-center px-6 py-1 rounded-full bg-[#FFC8C8] text-[#E75858] font-medium text-sm min-w-[80px]">
                        Invalid
                    </span>`;
            } else {
                statusBadge = `<span class="inline-flex items-center px-2 py-1 rounded-full bg-gray-200 text-gray-700 text-sm">${escapeHtml(row.status)}</span>`;
            }

            // Toggle action button logic
            let toggleBtn = '';
            if (currentBatchStatus !== 'approved') {
                if (status === 'valid') {
                    toggleBtn = `
                        <button onclick="toggleRowStatus(${row.id}, 'invalid')"
                            class="min-w-[120px] px-4 py-2 rounded-lg bg-[#E75858] text-white font-medium text-xs bg-red-500 transition cursor-pointer border-none">
                            Mark as invalid
                        </button>`;
                } else if (status === 'invalid') {
                    toggleBtn = `
                        <button onclick="toggleRowStatus(${row.id}, 'valid')"
                            class="min-w-[120px] px-4 py-2 rounded-lg bg-[#2DBF6A] text-white font-medium text-xs bg-green-500 transition cursor-pointer border-none">
                            Mark as Valid
                        </button>`;
                }
            }

            return `
                <tr>
                   
                    <td class="px-3 py-2 text-sm">${serialNumber}</td>
                    <td class="px-3 py-2 text-sm h-10">${imageCell}</td>
                    <td class="px-3 py-2 text-sm">${escapeHtml(row.code)}</td>
                    <td class="px-3 py-2 text-sm">${escapeHtml(row.city || '')}</td>
                    <td class="px-3 py-2 text-sm">${row.width ?? ''}</td>
                    <td class="px-3 py-2 text-sm">${row.height ?? ''}</td>
                    <td class="px-3 py-2 text-sm">${row.base_monthly_price ?? ''}</td>
                    <td class="px-3 py-2 text-sm">${statusBadge}</td>   
                    <td class="px-3 py-2 text-sm text-red-600">${errorCell}</td>
                    <td class="px-3 py-2 text-sm">
                        ${currentBatchStatus === 'approved'
                            ? '<span class="text-gray-400 cursor-not-allowed text-sm">Disabled</span>'
                            : `<div class="flex items-center gap-2">
                                    ${toggleBtn}
                            </div>`}
                    </td>
                </tr>
            `;
        }).join('');

        syncSelectAllCheckbox();
        updateBulkDeleteButtonState();
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function deleteSelectedRows() {
    if (currentBatchStatus === 'approved') {
        if (window.Swal) {
            await Swal.fire({
                icon: 'info',
                title: 'Read-only Batch',
                text: 'Approved batch rows cannot be deleted.',
            });
        }
        return;
    }

    const selectedIds = Array.from(selectedRowIds);
    if (!selectedIds.length) {
        notify('Please select at least one row', 'error');
        return;
    }

    const confirmation = window.Swal
        ? await Swal.fire({
            icon: 'warning',
            title: `Delete ${selectedIds.length} selected row(s)?`,
            text: 'This will remove selected rows and images if no other row uses them.',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete selected',
            confirmButtonColor: '#dc2626',
        })
        : { isConfirmed: confirm(`Delete ${selectedIds.length} selected row(s)?`) };

    if (!confirmation.isConfirmed) return;

    if (window.Swal) {
        Swal.fire({
            title: 'Deleting selected rows...',
            text: 'Please wait while we remove selected rows.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });
    }

    let deletedCount = 0;
    const failedIds = [];

    for (const rowId of selectedIds) {
        try {
            await api(`${API_BASE}/${BATCH_ID}/rows/${rowId}`, { method: 'DELETE' });
            deletedCount += 1;
        } catch (error) {
            failedIds.push(rowId);
        }
    }

    if (window.Swal) {
        Swal.close();
    }

    if (failedIds.length === 0) {
        if (window.Swal) {
            await Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: `${deletedCount} row(s) deleted successfully`,
                timer: 1800,
                showConfirmButton: false,
            });
        } else {
            notify(`${deletedCount} row(s) deleted`);
        }
    } else {
        const failedText = failedIds.slice(0, 5).join(', ');
        const suffix = failedIds.length > 5 ? '...' : '';
        const errorMessage = `${deletedCount} deleted, ${failedIds.length} failed (IDs: ${failedText}${suffix})`;

        if (window.Swal) {
            await Swal.fire({
                icon: 'error',
                title: 'Bulk Delete Completed with Errors',
                text: errorMessage,
            });
        } else {
            notify(errorMessage, 'error');
        }
    }

    clearRowSelections();
    loadRows();
}

async function approveInventory() {
    const approveBtn = document.getElementById('approveInventoryBtn');
    const autoApprove = approveBtn.dataset.autoApprove === '1';
    if (!approveBtn || approveBtn.disabled) {
        return;
    }

  const confirmation = window.Swal
    ? await Swal.fire({
        icon: 'question',
        title: autoApprove 
            ? 'Do you want to publish this inventory?' 
            : 'Send inventory for admin approval?',
        text: autoApprove 
            ? 'Only valid hoardings will be published to the website. Please delete any invalid hoardings before proceeding.'
            : 'Only valid hoardings will be submitted for admin approval. Please delete any invalid hoardings before proceeding.',
        showCancelButton: true,
        confirmButtonText: autoApprove ? 'Yes, publish' : 'Yes, send',
    })
    : { isConfirmed: confirm('Proceed?') };

    if (!confirmation.isConfirmed) {
        return;
    }

    applyBatchUiState(currentBatchStatus, true);

    if (window.Swal) {
        Swal.fire({
            title: autoApprove ? 'Publishing inventory...' : 'Sending for approval...',
            text: autoApprove 
                ? 'Please wait while we publish valid hoardings.'
                : 'Please wait while we send hoardings for admin review.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });
    }

    try {
        const approvalResult = await api(`${API_BASE}/${BATCH_ID}/approve`, {
            method: 'POST',
        });

        const nextStatus = (approvalResult?.data?.status || currentBatchStatus || '').toLowerCase();
        const successMessage = approvalResult?.message || 'Import approved and hoardings created successfully';

        if (window.Swal) {
            Swal.close();
        }

        if (window.Swal) {
            await Swal.fire({
                icon: 'success',
                title: 'Congratulations!',
                text: successMessage,
            });
        } else {
            notify(successMessage);
        }

        applyBatchUiState(nextStatus, false);
        document.getElementById('batchStatusText').textContent = nextStatus || '-';
        loadRows();
    } catch (error) {
        if (window.Swal) {
            Swal.close();
        }

        if (window.Swal) {
            await Swal.fire({
                icon: 'error',
                title: 'Approval Failed',
                text: error.message || 'Failed to approve inventory',
            });
        } else {
            notify(error.message, 'error');
        }

        applyBatchUiState(currentBatchStatus, false);
        loadRows();
    }
}

function openEditModal() {
    document.getElementById('editRowModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeEditModal() {
    document.getElementById('editRowModal').classList.add('hidden');
    document.body.style.overflow = '';
    resetRowForm();
}


async function toggleRowStatus(rowId, newStatus) {
    if (currentBatchStatus === 'approved') return;

    const row = currentRowsById[rowId];
    if (!row) {
        notify('Row not found', 'error');
        return;
    }

    // Prevent marking as valid if base_monthly_price or image_name is missing
    if (newStatus === 'valid') {
        let missingFields = [];
        if (!row.base_monthly_price || isNaN(Number(row.base_monthly_price))) {
            missingFields.push('Monthly Rental Price');
        }
        if (!row.image_name) {
            missingFields.push('Image');
        }
        if (missingFields.length > 0) {
            const msg = `Cannot mark as valid. Missing: ${missingFields.join(', ')}`;
            if (window.Swal) {
                await Swal.fire({
                    icon: 'error',
                    title: 'Validation Error',
                    text: msg,
                });
            } else {
                notify(msg, 'error');
            }
            return;
        }
    }

    try {
        await api(`${API_BASE}/${BATCH_ID}/rows/${rowId}/status`, {
            method: 'PATCH',
            body: JSON.stringify({ status: newStatus }),
        });

        // Update local cache only — no full reload needed
        currentRowsById[rowId] = { ...row, status: newStatus };

        // Re-render only the affected row in the DOM
        const allRows = Object.values(currentRowsById);
        const rowIndex = allRows.findIndex(r => r.id === rowId);
        if (rowIndex !== -1) {
            allRows[rowIndex] = { ...row, status: newStatus };
        }

        // Update valid/invalid counts in summary
        const validCount   = Object.values(currentRowsById).filter(r => r.status === 'valid').length;
        const invalidCount = Object.values(currentRowsById).filter(r => r.status === 'invalid').length;

        // Update summary counts (these reflect current page only — full counts come from API)
        // Reload just to refresh counts accurately
        loadRows(rowsQueryState.page);

        notify(`Marked as ${newStatus}`);

    } catch (error) {
        if (window.Swal) {
            await Swal.fire({
                icon: 'error',
                title: 'Update Failed',
                text: error.message || 'Failed to update status',
            });
        } else {
            notify(error.message, 'error');
        }
    }
}


function buildFieldHtml(fieldDef, value) {
    const val       = escapeHtml(value ?? '');
    const base      = 'w-full min-h-[44px] border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm';
    const reqMark   = fieldDef.required ? '<span class="text-red-500 ml-0.5">*</span>' : '';
    const label     = `<label class="block text-sm font-medium text-gray-700 mb-1">${fieldDef.label}${reqMark}</label>`;
    const spanFull  = (fieldDef.fullWidth || fieldDef.type === 'textarea') ? 'sm:col-span-2' : '';

    let input = '';
    if (fieldDef.type === 'select') {
        const opts = fieldDef.options.map(o =>
            `<option value="${o}" ${String(value) === o ? 'selected' : ''}>${o.charAt(0).toUpperCase() + o.slice(1)}</option>`
        ).join('');
        input = `<select id="rowField_${fieldDef.key}" data-field="${fieldDef.key}" class="${base}">${opts}</select>`;
    } else if (fieldDef.type === 'textarea') {
        input = `<textarea id="rowField_${fieldDef.key}" data-field="${fieldDef.key}" rows="2"
                    placeholder="${fieldDef.placeholder || ''}"
                    class="${base} resize-none">${val}</textarea>`;
    } else {
        const extra = fieldDef.type === 'number' ? 'step="any" min="0"' : '';
        const req   = fieldDef.required ? 'required' : '';
        input = `<input id="rowField_${fieldDef.key}" data-field="${fieldDef.key}"
                    type="${fieldDef.type}" value="${val}"
                    placeholder="${fieldDef.placeholder || ''}"
                    ${extra} ${req} class="${base}" />`;
    }

    return `<div class="${spanFull}">${label}${input}</div>`;
}

// function editRow(rowId) {
//     if (currentBatchStatus === 'approved') {
//         notify('Approved batch is read-only', 'error');
//         return;
//     }

//     const row = currentRowsById[rowId];
//     if (!row) return;

//     document.getElementById('rowId').value = row.id;

//     const grid = document.getElementById('dynamicFieldsGrid');
//     grid.innerHTML = '';

//     ROW_FIELD_DEFINITIONS.forEach(fieldDef => {
//         const value = row[fieldDef.key];
//         // Skip fields where value is null/undefined AND it's not a core field
//         const isCoreField = ['code', 'status', 'city', 'width', 'height'].includes(fieldDef.key);
//         if (!isCoreField && (value === null || value === undefined || value === '')) {
//             return; // skip — no data for this field on this row
//         }
//         grid.insertAdjacentHTML('beforeend', buildFieldHtml(fieldDef, value));
//     });

//     // Image
//     document.getElementById('rowImageFile').value = '';
//     if (row.image_name) {
//         const preview = document.getElementById('rowImagePreview');
//         preview.src = imageUrl(row.image_name);
//         preview.classList.remove('hidden');
//         document.getElementById('rowImagePreviewText').textContent = `Current: ${row.image_name}`;
//     } else {
//         clearImagePreview();
//     }

//     openEditModal();
// }

function resetRowForm() {
    document.getElementById('rowForm').reset();
    document.getElementById('rowId').value = '';
    clearImagePreview();
}

async function submitRow(event) {
    event.preventDefault();

    if (currentBatchStatus === 'approved') {
        if (window.Swal) {
            await Swal.fire({ icon: 'info', title: 'Read-only Batch', text: 'Approved batch rows cannot be edited.' });
        }
        return;
    }

    const rowId    = document.getElementById('rowId').value;
    const imageFile = document.getElementById('rowImageFile').files[0] || null;
    const formData  = new FormData();

    // Collect all dynamically rendered fields
    document.querySelectorAll('#dynamicFieldsGrid [data-field]').forEach(el => {
        formData.append(el.dataset.field, el.value ?? '');
    });

    if (imageFile) {
        formData.append('image', imageFile);
        formData.append('keep_previous_image', '0');
    }

    try {
        if (rowId) {
            formData.append('_method', 'PUT');
            await api(`${API_BASE}/${BATCH_ID}/rows/${rowId}`, { method: 'POST', body: formData });
            if (window.Swal) {
                await Swal.fire({ icon: 'success', title: 'Saved', text: 'Row updated successfully', timer: 1600, showConfirmButton: false });
            } else {
                notify('Row updated');
            }
        } else {
            await api(`${API_BASE}/${BATCH_ID}/rows`, { method: 'POST', body: formData });
            if (window.Swal) {
                await Swal.fire({ icon: 'success', title: 'Created', text: 'Row created successfully', timer: 1600, showConfirmButton: false });
            } else {
                notify('Row created');
            }
        }
        closeEditModal();
        loadRows();
    } catch (error) {
        if (window.Swal) {
            await Swal.fire({ icon: 'error', title: 'Save Failed', text: error.message || 'Failed to save row' });
        } else {
            notify(error.message, 'error');
        }
    }
}

document.getElementById('editRowModal')?.addEventListener('click', (e) => {
    if (e.target === document.getElementById('editRowModal')) closeEditModal();
});
async function deleteRow(rowId) {
    if (currentBatchStatus === 'approved') {
        if (window.Swal) {
            await Swal.fire({
                icon: 'info',
                title: 'Read-only Batch',
                text: 'Approved batch rows cannot be deleted.',
            });
        }
        return;
    }

    const confirmation = window.Swal
        ? await Swal.fire({
            icon: 'warning',
            title: `Delete row #${rowId}?`,
            text: 'This will remove the row and its image if no other row uses it.',
            showCancelButton: true,
            confirmButtonText: 'Yes, delete',
            confirmButtonColor: '#dc2626',
        })
        : { isConfirmed: confirm(`Delete row #${rowId}?`) };

    if (!confirmation.isConfirmed) return;

    if (window.Swal) {
        Swal.fire({
            title: 'Deleting row...',
            text: 'Please wait while we remove this row.',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => Swal.showLoading(),
        });
    }

    try {
        await api(`${API_BASE}/${BATCH_ID}/rows/${rowId}`, { method: 'DELETE' });

        if (window.Swal) {
            Swal.close();
            await Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: 'Row deleted successfully',
                timer: 1600,
                showConfirmButton: false,
            });
        } else {
            notify('Row deleted');
        }

        loadRows();
    } catch (error) {
        if (window.Swal) {
            Swal.close();
            await Swal.fire({
                icon: 'error',
                title: 'Delete Failed',
                text: error.message || 'Failed to delete row',
            });
        } else {
            notify(error.message, 'error');
        }
    }
}

function resetRowForm() {
    document.getElementById('rowForm').reset();
    document.getElementById('rowId').value = '';
    clearImagePreview();
}

function clearImagePreview() {
    const preview = document.getElementById('rowImagePreview');
    preview.src = '';
    preview.classList.add('hidden');
    document.getElementById('rowImagePreviewText').textContent = 'No image selected';
}

function handleImagePreviewChange() {
    const file = document.getElementById('rowImageFile').files[0];
    if (!file) {
        return;
    }

    const preview = document.getElementById('rowImagePreview');
    preview.src = URL.createObjectURL(file);
    preview.classList.remove('hidden');
    document.getElementById('rowImagePreviewText').textContent = `Selected: ${file.name}`;
}

document.addEventListener('DOMContentLoaded', () => {
    applyBatchUiState(currentBatchStatus, false);
    loadRows();

    document.getElementById('applyFilter').addEventListener('click', () => loadRows(1));
    document.getElementById('resetFilter').addEventListener('click', () => {
        document.getElementById('searchInput').value = '';
        document.getElementById('statusFilter').value = '';
        loadRows(1);
    });
    document.getElementById('rowsPerPage')?.addEventListener('change', () => loadRows(1));
    document.getElementById('rowsPrevBtn')?.addEventListener('click', () => {
        if (rowsPaginationState.current_page > 1) {
            loadRows(rowsPaginationState.current_page - 1);
        }
    });
    document.getElementById('rowsNextBtn')?.addEventListener('click', () => {
        if (rowsPaginationState.current_page < rowsPaginationState.last_page) {
            loadRows(rowsPaginationState.current_page + 1);
        }
    });
    document.getElementById('rowForm').addEventListener('submit', submitRow);
    // document.getElementById('resetRowForm').addEventListener('click', resetRowForm);
    document.getElementById('rowImageFile').addEventListener('change', handleImagePreviewChange);
    document.getElementById('approveInventoryBtn')?.addEventListener('click', approveInventory);
    document.getElementById('rowsSelectAll')?.addEventListener('change', (event) => {
        toggleSelectAllRows(event.target.checked);
    });
    document.getElementById('bulkDeleteBtn')?.addEventListener('click', deleteSelectedRows);
});




</script>
@endsection
