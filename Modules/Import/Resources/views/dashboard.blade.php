@extends($layout)

@section('content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-gray-900">Inventory Import</h1>
    <p class="text-gray-600 mt-1">Manage uploads, import batches, and staged rows</p>
</div>

<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<div class="mb-6 border-b border-gray-200">
    <nav class="-mb-px flex space-x-6" aria-label="Tabs">
        <button id="tabUpload" class="tab-btn border-b-2 border-blue-500 py-2 px-1 text-sm font-medium text-blue-600" data-target="uploadPanel">Upload</button>
        <button id="tabBatches" class="tab-btn border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" data-target="batchesPanel">Import Batches</button>
        @if($isAdmin)
            <button id="tabPermissions" class="tab-btn border-b-2 border-transparent py-2 px-1 text-sm font-medium text-gray-500 hover:text-gray-700" data-target="permissionsPanel">Role Permissions</button>
        @endif
    </nav>
</div>

<div id="uploadPanel" class="tab-panel">
    <div class="bg-white rounded-xl shadow p-6 max-w-3xl">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Upload New Batch</h2>
        <form id="uploadForm" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Excel File</label>
                <input type="file" id="excelFile" accept=".xlsx" class="block w-full border border-gray-300 rounded-lg p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">PowerPoint File</label>
                <input type="file" id="pptFile" accept=".ppt,.pptx" class="block w-full border border-gray-300 rounded-lg p-2" required>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Media Type</label>
                <select id="mediaType" class="block w-full border border-gray-300 rounded-lg p-2">
                    <option value="ooh">OOH</option>
                    <option value="dooh">DOOH</option>
                </select>
            </div>
            <button type="submit" id="uploadBtn" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700">Upload</button>
        </form>
    </div>
</div>

<div id="batchesPanel" class="tab-panel hidden">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Import Batches</h2>
            <button id="refreshBatchesBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Refresh</button>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left text-sm">Batch</th>
                        <th class="px-4 py-3 text-left text-sm">Type</th>
                        <th class="px-4 py-3 text-left text-sm">Status</th>
                        <th class="px-4 py-3 text-left text-sm">Rows</th>
                        <th class="px-4 py-3 text-left text-sm">Created</th>
                        <th class="px-4 py-3 text-left text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody id="batchesBody" class="divide-y divide-gray-200">
                    <tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">Loading...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($isAdmin)
<div id="permissionsPanel" class="tab-panel hidden">
    <div class="bg-white rounded-xl shadow overflow-hidden">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h2 class="text-xl font-semibold text-gray-900">Role Import Permissions</h2>
            <button id="refreshPermissionsBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200">Refresh</button>
        </div>
        <div id="permissionsContainer" class="p-4 space-y-4">
            <p class="text-sm text-gray-500">Loading role permissions...</p>
        </div>
    </div>
</div>
@endif

<div id="rowsModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-40 items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-xl w-full max-w-6xl max-h-[90vh] flex flex-col">
        <div class="p-4 border-b border-gray-200 flex items-center justify-between">
            <h3 id="rowsTitle" class="font-semibold text-lg text-gray-900">Batch Rows</h3>
            <button id="closeRowsModal" class="text-gray-500 hover:text-gray-700">Close</button>
        </div>

        <div class="p-4 border-b border-gray-200">
            <form id="rowForm" class="grid grid-cols-1 md:grid-cols-7 gap-2">
                <input type="hidden" id="rowId">
                <input id="rowCode" class="border rounded p-2" placeholder="Code" required>
                <input id="rowCity" class="border rounded p-2" placeholder="City">
                <input id="rowWidth" type="number" step="0.01" min="0" class="border rounded p-2" placeholder="Width">
                <input id="rowHeight" type="number" step="0.01" min="0" class="border rounded p-2" placeholder="Height">
                <select id="rowStatus" class="border rounded p-2">
                    <option value="valid">valid</option>
                    <option value="invalid">invalid</option>
                </select>
                <div class="flex gap-2">
                    <button type="submit" class="bg-blue-600 text-white rounded px-3 py-2 text-sm">Save Row</button>
                    <button type="button" id="resetRowForm" class="bg-gray-100 rounded px-3 py-2 text-sm">Reset</button>
                </div>
            </form>
        </div>

        <div class="flex-1 overflow-auto">
            <table class="w-full">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm">ID</th>
                        <th class="px-4 py-2 text-left text-sm">Code</th>
                        <th class="px-4 py-2 text-left text-sm">City</th>
                        <th class="px-4 py-2 text-left text-sm">W</th>
                        <th class="px-4 py-2 text-left text-sm">H</th>
                        <th class="px-4 py-2 text-left text-sm">Status</th>
                        <th class="px-4 py-2 text-left text-sm">Actions</th>
                    </tr>
                </thead>
                <tbody id="rowsBody" class="divide-y divide-gray-200"></tbody>
            </table>
        </div>
    </div>
</div>

<script>
const API_BASE = '/api/import';
const IS_ADMIN = @json($isAdmin);
let selectedBatchId = null;
let currentRowsById = {};

const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

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

function setupTabs() {
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            document.querySelectorAll('.tab-btn').forEach(b => {
                b.classList.remove('border-blue-500', 'text-blue-600');
                b.classList.add('border-transparent', 'text-gray-500');
            });
            btn.classList.add('border-blue-500', 'text-blue-600');

            document.querySelectorAll('.tab-panel').forEach(panel => panel.classList.add('hidden'));
            document.getElementById(btn.dataset.target).classList.remove('hidden');

            if (btn.dataset.target === 'batchesPanel') {
                loadBatches();
            }

            if (btn.dataset.target === 'permissionsPanel') {
                loadRolePermissions();
            }
        });
    });

    const url = new URL(window.location.href);
    const requestedTab = url.searchParams.get('tab');

    if (requestedTab === 'batches') {
        document.getElementById('tabBatches')?.click();
    }

    if (requestedTab === 'permissions' && IS_ADMIN) {
        document.getElementById('tabPermissions')?.click();
    }
}

async function loadRolePermissions() {
    if (!IS_ADMIN) return;

    const container = document.getElementById('permissionsContainer');
    if (!container) return;

    try {
        const result = await api(`${API_BASE}/roles/permissions`);
        const permissions = result.data.permissions || [];
        const roles = result.data.roles || [];

        if (!roles.length) {
            container.innerHTML = '<p class="text-sm text-gray-500">No roles found.</p>';
            return;
        }

        container.innerHTML = roles.map(role => {
            const rolePermissionSet = new Set(role.permissions || []);
            const permissionCheckboxes = permissions.map(permission => {
                const checked = rolePermissionSet.has(permission.name) ? 'checked' : '';
                const inputId = `role-${role.id}-${permission.id}`;
                return `
                    <label for="${inputId}" class="flex items-center gap-2 text-sm text-gray-700">
                        <input id="${inputId}" type="checkbox" data-role-id="${role.id}" data-permission="${permission.name}" ${checked} class="rounded border-gray-300 text-blue-600 focus:ring-blue-500" />
                        <span>${permission.name}</span>
                    </label>
                `;
            }).join('');

            return `
                <div class="border border-gray-200 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="font-semibold text-gray-900">${role.name}</h3>
                        <button type="button" onclick="saveRolePermissions(${role.id})" class="px-3 py-1.5 bg-blue-600 text-white rounded text-sm hover:bg-blue-700">Save</button>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        ${permissionCheckboxes}
                    </div>
                </div>
            `;
        }).join('');
    } catch (error) {
        container.innerHTML = `<p class="text-sm text-red-600">${error.message}</p>`;
    }
}

async function saveRolePermissions(roleId) {
    if (!IS_ADMIN) return;

    const checkboxes = Array.from(document.querySelectorAll(`input[type="checkbox"][data-role-id="${roleId}"]`));
    const permissions = checkboxes
        .filter(checkbox => checkbox.checked)
        .map(checkbox => checkbox.dataset.permission);

    try {
        await api(`${API_BASE}/roles/${roleId}/permissions`, {
            method: 'PUT',
            body: JSON.stringify({ permissions }),
        });

        notify('Role permissions updated');
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function loadBatches() {
    try {
        const result = await api(API_BASE);
        const rows = result.data || [];
        const body = document.getElementById('batchesBody');
        if (!rows.length) {
            body.innerHTML = '<tr><td colspan="6" class="px-4 py-6 text-center text-gray-500">No batches found</td></tr>';
            return;
        }

        body.innerHTML = rows.map(batch => {
            const canApprove = !IS_ADMIN && batch.status === 'processed';
            return `
                <tr>
                    <td class="px-4 py-3">#${batch.batch_id}</td>
                    <td class="px-4 py-3">${batch.media_type}</td>
                    <td class="px-4 py-3">${batch.status}</td>
                    <td class="px-4 py-3">${batch.valid_rows}/${batch.total_rows}</td>
                    <td class="px-4 py-3">${new Date(batch.created_at).toLocaleString()}</td>
                    <td class="px-4 py-3 space-x-2">
                        <button onclick="openRows(${batch.batch_id})" class="text-blue-600 hover:underline">Rows</button>
                        <button onclick="editBatch(${batch.batch_id}, '${batch.media_type}')" class="text-indigo-600 hover:underline">Edit</button>
                        <button onclick="deleteBatch(${batch.batch_id})" class="text-red-600 hover:underline">Delete</button>
                        ${canApprove ? `<button onclick="approveBatch(${batch.batch_id})" class="text-green-600 hover:underline">Approve</button>` : ''}
                    </td>
                </tr>
            `;
        }).join('');
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function editBatch(batchId, currentType) {
    const mediaType = prompt('Enter media type (ooh/dooh):', (currentType || 'ooh').toLowerCase());
    if (!mediaType) return;

    try {
        await api(`${API_BASE}/${batchId}`, {
            method: 'PUT',
            body: JSON.stringify({ media_type: mediaType }),
        });
        notify('Batch updated');
        loadBatches();
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function deleteBatch(batchId) {
    if (!confirm(`Delete batch #${batchId}?`)) return;

    try {
        await api(`${API_BASE}/${batchId}/destroy`, { method: 'DELETE' });
        notify('Batch deleted');
        loadBatches();
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function approveBatch(batchId) {
    if (!confirm(`Approve batch #${batchId}?`)) return;

    try {
        await api(`${API_BASE}/${batchId}/approve`, { method: 'POST' });
        notify('Batch approved');
        loadBatches();
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function openRows(batchId) {
    selectedBatchId = batchId;
    document.getElementById('rowsTitle').textContent = `Batch #${batchId} Rows`;
    document.getElementById('rowsModal').classList.remove('hidden');
    document.getElementById('rowsModal').classList.add('flex');
    await loadRows();
}

async function loadRows() {
    if (!selectedBatchId) return;

    try {
        const result = await api(`${API_BASE}/${selectedBatchId}?per_page=100`);
        const rows = result.data.rows || [];
        const body = document.getElementById('rowsBody');
        currentRowsById = rows.reduce((accumulator, row) => {
            accumulator[row.id] = row;
            return accumulator;
        }, {});

        body.innerHTML = rows.map(row => `
            <tr>
                <td class="px-4 py-2">${row.id}</td>
                <td class="px-4 py-2">${row.code || ''}</td>
                <td class="px-4 py-2">${row.city || ''}</td>
                <td class="px-4 py-2">${row.width ?? ''}</td>
                <td class="px-4 py-2">${row.height ?? ''}</td>
                <td class="px-4 py-2">${row.status}</td>
                <td class="px-4 py-2 space-x-2">
                    <button onclick="startEditRow(${row.id})" class="text-indigo-600 hover:underline">Edit</button>
                    <button onclick="deleteRow(${row.id})" class="text-red-600 hover:underline">Delete</button>
                </td>
            </tr>
        `).join('');
    } catch (error) {
        notify(error.message, 'error');
    }
}

function startEditRow(rowId) {
    const data = currentRowsById[rowId];
    if (!data) {
        notify('Unable to load row for editing', 'error');
        return;
    }

    document.getElementById('rowId').value = rowId;
    document.getElementById('rowCode').value = data.code || '';
    document.getElementById('rowCity').value = data.city || '';
    document.getElementById('rowWidth').value = data.width ?? '';
    document.getElementById('rowHeight').value = data.height ?? '';
    document.getElementById('rowStatus').value = data.status || 'valid';
}

async function submitRow(event) {
    event.preventDefault();

    if (!selectedBatchId) return;

    const rowId = document.getElementById('rowId').value;
    const payload = {
        code: document.getElementById('rowCode').value,
        city: document.getElementById('rowCity').value || null,
        width: document.getElementById('rowWidth').value || null,
        height: document.getElementById('rowHeight').value || null,
        status: document.getElementById('rowStatus').value,
    };

    try {
        if (rowId) {
            await api(`${API_BASE}/${selectedBatchId}/rows/${rowId}`, {
                method: 'PUT',
                body: JSON.stringify(payload),
            });
            notify('Row updated');
        } else {
            await api(`${API_BASE}/${selectedBatchId}/rows`, {
                method: 'POST',
                body: JSON.stringify(payload),
            });
            notify('Row created');
        }

        resetRowForm();
        loadRows();
        loadBatches();
    } catch (error) {
        notify(error.message, 'error');
    }
}

async function deleteRow(rowId) {
    if (!confirm(`Delete row #${rowId}?`)) return;

    try {
        await api(`${API_BASE}/${selectedBatchId}/rows/${rowId}`, { method: 'DELETE' });
        notify('Row deleted');
        loadRows();
        loadBatches();
    } catch (error) {
        notify(error.message, 'error');
    }
}

function resetRowForm() {
    document.getElementById('rowForm').reset();
    document.getElementById('rowId').value = '';
}

async function submitUpload(event) {
    event.preventDefault();

    const excel = document.getElementById('excelFile').files[0];
    const ppt = document.getElementById('pptFile').files[0];
    const mediaType = document.getElementById('mediaType').value;

    if (!excel || !ppt) {
        notify('Please select both files', 'error');
        return;
    }

    const formData = new FormData();
    formData.append('excel', excel);
    formData.append('ppt', ppt);
    formData.append('media_type', mediaType);

    const btn = document.getElementById('uploadBtn');
    btn.disabled = true;
    btn.textContent = 'Uploading...';

    try {
        await api(`${API_BASE}/upload`, {
            method: 'POST',
            body: formData,
        });
        notify('Upload started successfully');
        document.getElementById('uploadForm').reset();
    } catch (error) {
        notify(error.message, 'error');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Upload';
    }
}

document.addEventListener('DOMContentLoaded', () => {
    setupTabs();
    document.getElementById('uploadForm').addEventListener('submit', submitUpload);
    document.getElementById('refreshBatchesBtn').addEventListener('click', loadBatches);
    document.getElementById('closeRowsModal').addEventListener('click', () => {
        document.getElementById('rowsModal').classList.add('hidden');
        document.getElementById('rowsModal').classList.remove('flex');
        selectedBatchId = null;
        currentRowsById = {};
        resetRowForm();
    });
    document.getElementById('rowForm').addEventListener('submit', submitRow);
    document.getElementById('resetRowForm').addEventListener('click', resetRowForm);
    if (IS_ADMIN) {
        document.getElementById('refreshPermissionsBtn')?.addEventListener('click', loadRolePermissions);
    }
});
</script>
@endsection
