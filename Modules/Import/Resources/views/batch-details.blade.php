@extends($layout)
@section('title', 'Inventory Details')
@section('page_title', 'Inventory Details')

@section('content')
<div class="mb-6 flex items-center justify-between gap-3 flex-wrap">
    <div>
        <h1 class="text-2xl font-bold text-gray-900">Import Batch #{{ $batch->id }}</h1>
        <p class="text-gray-600 mt-1">View rows, images, and manage each hoarding record</p>
    </div>
    <a href="{{ $isAdmin ? route('admin.import.enhanced') : route('vendor.import.enhanced') }}" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">
        Back to Import List
    </a>
</div>

<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

@php
    $isApprovedBatch = $batch->status === 'approved';
@endphp

<div id="rowEditorSection" class="bg-white rounded-xl shadow mb-6 {{ $isApprovedBatch ? 'hidden' : '' }}">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between gap-3 flex-wrap">
        <h2 class="text-lg font-semibold text-gray-900">Batch Summary</h2>
        @if(!$isAdmin)
            <button id="approveInventoryBtn" class="px-4 py-2 rounded-lg text-white disabled:opacity-50 disabled:cursor-not-allowed {{ $isApprovedBatch ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }}" {{ !in_array($batch->status, ['processed', 'completed']) || $isApprovedBatch ? 'disabled' : '' }}>
                {{ $isApprovedBatch ? 'Approved' : 'Send For Approval' }}
            </button>
        @endif
    </div>
    <div class="p-4 grid grid-cols-2 md:grid-cols-5 gap-3 text-sm">
        <div><span class="text-gray-500">Batch</span><p class="font-semibold">#{{ $batch->id }}</p></div>
        <div><span class="text-gray-500">Type</span><p class="font-semibold">{{ strtoupper($batch->media_type) }}</p></div>
        <div><span class="text-gray-500">Status</span><p class="font-semibold" id="batchStatusText">{{ $batch->status }}</p></div>
        <div><span class="text-gray-500">Valid Rows</span><p class="font-semibold" id="validCount">{{ $batch->valid_rows }}</p></div>
        <div><span class="text-gray-500">Invalid Rows</span><p class="font-semibold" id="invalidCount">{{ $batch->invalid_rows }}</p></div>
    </div>
</div>

<div class="bg-white rounded-xl shadow mb-6">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Search & Filters</h2>
    </div>
    <div class="p-4 grid grid-cols-1 md:grid-cols-4 gap-3">
        <input id="searchInput" type="text" placeholder="Search by code / city / image" class="border rounded-lg p-2" />
        <select id="statusFilter" class="border rounded-lg p-2">
            <option value="">All statuses</option>
            <option value="valid">valid</option>
            <option value="invalid">invalid</option>
        </select>
        <button id="applyFilter" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Apply</button>
        <button id="resetFilter" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reset</button>
    </div>
</div>

<div class="bg-white rounded-xl shadow mb-6">
    <div class="p-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-900">Create / Edit Row</h2>
    </div>
    <div class="p-4">
        <form id="rowForm" class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <input type="hidden" id="rowId">
            <input id="rowCode" class="border rounded-lg p-2" placeholder="Code" required>
            <input id="rowCity" class="border rounded-lg p-2" placeholder="City">
            <input id="rowWidth" type="number" step="0.01" min="0" class="border rounded-lg p-2" placeholder="Width">
            <input id="rowHeight" type="number" step="0.01" min="0" class="border rounded-lg p-2" placeholder="Height">
            <input id="rowImageFile" type="file" accept="image/*" class="border rounded-lg p-2" />
            <select id="rowStatus" class="border rounded-lg p-2">
                <option value="valid">valid</option>
                <option value="invalid">invalid</option>
            </select>
            <input id="rowErrorMessage" class="border rounded-lg p-2" placeholder="Error Message (optional)">
            <div class="md:col-span-4 flex items-center gap-3">
                <img id="rowImagePreview" src="" alt="Selected row image" class="h-16 w-24 object-cover rounded border hidden" />
                <span id="rowImagePreviewText" class="text-sm text-gray-500">No image selected</span>
            </div>
            <div class="md:col-span-4 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">Save Row</button>
                <button type="button" id="resetRowForm" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200">Reset</button>
            </div>
        </form>
    </div>
</div>

<div class="bg-white rounded-xl shadow overflow-hidden">
    <div class="p-4 border-b border-gray-200 flex items-center justify-between">
        <h2 class="text-lg font-semibold text-gray-900">Batch Rows</h2>
        <p id="paginationInfo" class="text-sm text-gray-500"></p>
    </div>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[980px]">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-3 py-2 text-left text-sm">ID</th>
                    <th class="px-3 py-2 text-left text-sm">Image</th>
                    <th class="px-3 py-2 text-left text-sm">Code</th>
                    <th class="px-3 py-2 text-left text-sm">City</th>
                    <th class="px-3 py-2 text-left text-sm">Width</th>
                    <th class="px-3 py-2 text-left text-sm">Height</th>
                    <th class="px-3 py-2 text-left text-sm">Status</th>
                    <th class="px-3 py-2 text-left text-sm">Error</th>
                    <th class="px-3 py-2 text-left text-sm">Actions</th>
                </tr>
            </thead>
            <tbody id="rowsBody" class="divide-y divide-gray-200"></tbody>
        </table>
    </div>
    <div class="p-4 border-t border-gray-200 flex items-center justify-between gap-3 flex-wrap">
        <div class="flex items-center gap-2">
            <label for="rowsPerPage" class="text-sm text-gray-600">Rows per page</label>
            <select id="rowsPerPage" class="border rounded-lg p-1.5 text-sm">
                <option value="10">10</option>
                <option value="15" selected>15</option>
                <option value="25">25</option>
                <option value="50">50</option>
            </select>
        </div>
        <div class="flex items-center gap-2">
            <button id="rowsPrevBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
            <span id="rowsPageLabel" class="text-sm text-gray-600">Page 1 / 1</span>
            <button id="rowsNextBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
        </div>
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

    approveBtn.classList.remove('bg-green-600', 'hover:bg-green-700', 'bg-gray-400');

    if (loading) {
        approveBtn.disabled = true;
        approveBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        approveBtn.innerHTML = '<span class="inline-flex items-center gap-2"><svg class="animate-spin h-4 w-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"></path></svg>Approving...</span>';
        return;
    }

    if (status === 'approved') {
        approveBtn.disabled = true;
        approveBtn.classList.add('bg-gray-400', 'cursor-not-allowed');
        approveBtn.textContent = 'Approved';
        return;
    }

    const canApprove = ['processed', 'completed'].includes(status);
    approveBtn.disabled = !canApprove;
    approveBtn.classList.add('bg-green-600');
    if (canApprove) {
        approveBtn.classList.add('hover:bg-green-700');
    } else {
        approveBtn.classList.add('cursor-not-allowed');
    }
    approveBtn.textContent = 'Send For Approval';
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
            body.innerHTML = '<tr><td colspan="9" class="px-3 py-6 text-center text-gray-500">No rows found</td></tr>';
            return;
        }

        body.innerHTML = rows.map(row => {
            const imageCell = row.image_name
                ? `<img src="${imageUrl(row.image_name)}" alt="${escapeHtml(row.image_name)}" class="h-12 w-16 object-cover rounded border">`
                : '<span class="text-gray-400">-</span>';

            return `
                <tr>
                    <td class="px-3 py-2 text-sm">${row.id}</td>
                    <td class="px-3 py-2 text-sm">${imageCell}</td>
                    <td class="px-3 py-2 text-sm">${escapeHtml(row.code)}</td>
                    <td class="px-3 py-2 text-sm">${escapeHtml(row.city || '')}</td>
                    <td class="px-3 py-2 text-sm">${row.width ?? ''}</td>
                    <td class="px-3 py-2 text-sm">${row.height ?? ''}</td>
                    <td class="px-3 py-2 text-sm">${escapeHtml(row.status)}</td>
                    <td class="px-3 py-2 text-sm text-red-600">${escapeHtml(row.error_message || '')}</td>
                    <td class="px-3 py-2 text-sm space-x-2">
                        ${currentBatchStatus === 'approved'
                            ? '<span class="text-gray-400 cursor-not-allowed">Disabled</span>'
                            : `<button onclick="editRow(${row.id})" class="text-indigo-600 hover:underline">Edit</button><button onclick="deleteRow(${row.id})" class="text-red-600 hover:underline">Delete</button>`}
                    </td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function approveInventory() {
    const approveBtn = document.getElementById('approveInventoryBtn');
    if (!approveBtn || approveBtn.disabled) {
        return;
    }

    const confirmation = window.Swal
        ? await Swal.fire({
            icon: 'question',
            title: 'Do you want to publish this inventory?',
            text: 'This will submit your hoardings for admin approval before website publishing.',
            showCancelButton: true,
            confirmButtonText: 'Yes, publish',
        })
        : { isConfirmed: confirm('Approve inventory for this batch?') };

    if (!confirmation.isConfirmed) {
        return;
    }

    applyBatchUiState(currentBatchStatus, true);

    if (window.Swal) {
        Swal.fire({
            title: 'Approving inventory...',
            text: 'Please wait while we process valid rows.',
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

function editRow(rowId) {
    if (currentBatchStatus === 'approved') {
        notify('Approved batch is read-only', 'error');
        return;
    }

    const row = currentRowsById[rowId];
    if (!row) return;

    document.getElementById('rowId').value = row.id;
    document.getElementById('rowCode').value = row.code || '';
    document.getElementById('rowCity').value = row.city || '';
    document.getElementById('rowWidth').value = row.width ?? '';
    document.getElementById('rowHeight').value = row.height ?? '';
    document.getElementById('rowImageFile').value = '';
    document.getElementById('rowStatus').value = row.status || 'valid';
    document.getElementById('rowErrorMessage').value = row.error_message || '';

    if (row.image_name) {
        const preview = document.getElementById('rowImagePreview');
        preview.src = imageUrl(row.image_name);
        preview.classList.remove('hidden');
        document.getElementById('rowImagePreviewText').textContent = `Current image: ${row.image_name}`;
    } else {
        clearImagePreview();
    }
}

async function submitRow(event) {
    event.preventDefault();

    if (currentBatchStatus === 'approved') {
        if (window.Swal) {
            await Swal.fire({
                icon: 'info',
                title: 'Read-only Batch',
                text: 'Approved batch rows cannot be edited.',
            });
        }
        return;
    }

    const rowId = document.getElementById('rowId').value;
    const imageFile = document.getElementById('rowImageFile').files[0] || null;

    const formData = new FormData();
    formData.append('code', document.getElementById('rowCode').value);
    formData.append('city', document.getElementById('rowCity').value || '');
    formData.append('width', document.getElementById('rowWidth').value || '');
    formData.append('height', document.getElementById('rowHeight').value || '');
    formData.append('status', document.getElementById('rowStatus').value);
    formData.append('error_message', document.getElementById('rowErrorMessage').value || '');

    if (imageFile) {
        formData.append('image', imageFile);
        formData.append('keep_previous_image', '0');
    }

    try {
        if (rowId) {
            formData.append('_method', 'PUT');
            await api(`${API_BASE}/${BATCH_ID}/rows/${rowId}`, {
                method: 'POST',
                body: formData,
            });
            if (window.Swal) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Row updated successfully',
                    timer: 1800,
                    showConfirmButton: false,
                });
            } else {
                notify('Row updated');
            }
        } else {
            await api(`${API_BASE}/${BATCH_ID}/rows`, {
                method: 'POST',
                body: formData,
            });
            if (window.Swal) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Success',
                    text: 'Row created successfully',
                    timer: 1800,
                    showConfirmButton: false,
                });
            } else {
                notify('Row created');
            }
        }

        resetRowForm();
        loadRows();
    } catch (error) {
        if (window.Swal) {
            await Swal.fire({
                icon: 'error',
                title: 'Save Failed',
                text: error.message || 'Failed to save row',
            });
        } else {
            notify(error.message, 'error');
        }
    }
}

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
    document.getElementById('resetRowForm').addEventListener('click', resetRowForm);
    document.getElementById('rowImageFile').addEventListener('change', handleImagePreviewChange);
    document.getElementById('approveInventoryBtn')?.addEventListener('click', approveInventory);
});
</script>
@endsection
