@extends('layouts.admin')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Inventory Import</h1>
    <p class="text-gray-600 mt-2">Upload and manage your inventory imports</p>
</div>

<!-- Toast Notifications -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<!-- Upload Section -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
    <!-- Upload Card -->
    <div class="lg:col-span-1">
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hover:shadow-xl transition-shadow duration-300">
            <div class="px-6 py-8">
                <h2 class="text-xl font-semibold text-gray-900 mb-6">Upload Batch</h2>

                <form id="uploadForm" class="space-y-6">
                    @csrf

                    <!-- Excel File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Excel File (.xlsx)
                        </label>
                        <div class="relative">
                            <input type="file" id="excelFile" name="excel_file" accept=".xlsx"
                                class="hidden" />
                            <label for="excelFile"
                                class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span class="mt-2 text-sm text-gray-600 group-hover:text-blue-600">
                                        <span class="font-semibold">Click to upload</span> or drag
                                    </span>
                                    <span class="text-xs text-gray-500">Max 20MB</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- PPT File Upload -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            PowerPoint File (.pptx)
                        </label>
                        <div class="relative">
                            <input type="file" id="pptFile" name="ppt_file" accept=".pptx,.ppt"
                                class="hidden" />
                            <label for="pptFile"
                                class="flex items-center justify-center w-full px-4 py-3 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">
                                <div class="flex flex-col items-center">
                                    <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-500 transition-colors"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 4v16m8-8H4"></path>
                                    </svg>
                                    <span class="mt-2 text-sm text-gray-600 group-hover:text-blue-600">
                                        <span class="font-semibold">Click to upload</span> or drag
                                    </span>
                                    <span class="text-xs text-gray-500">Max 50MB</span>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Media Type Selection -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-3">
                            Import Type
                        </label>
                        <div class="space-y-2">
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="media_type" value="OOH" checked class="w-4 h-4 text-blue-600 cursor-pointer" />
                                <span class="ml-3 text-sm text-gray-700">OOH (Out of Home)</span>
                            </label>
                            <label class="flex items-center cursor-pointer">
                                <input type="radio" name="media_type" value="DOOH" class="w-4 h-4 text-blue-600 cursor-pointer" />
                                <span class="ml-3 text-sm text-gray-700">DOOH (Digital OOH)</span>
                            </label>
                        </div>
                    </div>

                    <!-- Upload Guidelines -->
                    <div class="space-y-2">
                        <h4 class="text-sm font-semibold text-gray-700">Upload Guidelines</h4>
                        <ul class="text-sm text-gray-600 space-y-1">
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Excel file up to 20MB
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                PowerPoint file up to 50MB
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Select import type
                            </li>
                            <li class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                Both files are optional
                            </li>
                        </ul>
                    </div>

                    <button type="submit"
                        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 rounded-lg transition-colors duration-200 flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Upload Files
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Stats Grid -->
        <div class="grid grid-cols-2 gap-4">
            <div class="bg-white rounded-xl shadow-lg p-5">
                <p class="text-gray-600 text-sm font-medium">Total Batches</p>
                <p id="totalBatches" class="text-3xl font-bold text-blue-600 mt-2">0</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-5">
                <p class="text-gray-600 text-sm font-medium">Processing</p>
                <p id="processingBatches" class="text-3xl font-bold text-yellow-600 mt-2">0</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-5">
                <p class="text-gray-600 text-sm font-medium">Completed</p>
                <p id="completedBatches" class="text-3xl font-bold text-green-600 mt-2">0</p>
            </div>
            <div class="bg-white rounded-xl shadow-lg p-5">
                <p class="text-gray-600 text-sm font-medium">Failed</p>
                <p id="failedBatches" class="text-3xl font-bold text-red-600 mt-2">0</p>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Actions</h3>
            <div class="flex flex-wrap gap-2">
                <button onclick="refreshBatches()"
                    class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg transition-colors text-sm font-medium">
                    Refresh
                </button>
                <button onclick="filterByStatus('processed')"
                    class="px-4 py-2 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors text-sm font-medium">
                    Pending Approval
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Batch List Section -->
<div class="bg-white rounded-xl shadow-lg overflow-hidden">
    <div class="px-6 py-6 border-b border-gray-200">
        <div class="flex items-center justify-between">
            <h2 class="text-2xl font-bold text-gray-900">Import Batches</h2>
            <div class="flex items-center space-x-4">
                <!-- Search Bar -->
                <input type="text" id="searchInput" placeholder="Search batches..."
                    class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
            </div>
        </div>
    </div>

    <!-- Table -->
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-50 border-b border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Batch ID</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Type</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Total</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Valid</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Invalid</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Date</th>
                    <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Actions</th>
                </tr>
            </thead>
            <tbody id="batchesTableBody" class="divide-y divide-gray-200">
                <tr>
                    <td  colspan="8" class="px-6 py-8 text-center text-gray-500">
                        <p class="font-medium">No batches yet</p>
                        <p class="text-sm mt-1">Upload a file to get started</p>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>
<script>
// API Configuration
const API_BASE = '/api/import';
const UPLOAD_ENDPOINT = `${API_BASE}/upload`;
const BATCHES_ENDPOINT = `${API_BASE}`;
const APPROVE_ENDPOINT = `${API_BASE}/approve`;
const DETAILS_ENDPOINT = `${API_BASE}`;

// Axios Configuration with CSRF Token
document.addEventListener('DOMContentLoaded', function() {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.common['Accept'] = 'application/json';
    
    // Load initial data
    loadBatches();
    
    // Auto-refresh every 30 seconds
    setInterval(loadBatches, 30000);
});

// Toast Notification
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    const toast = document.createElement('div');
    toast.className = `px-4 py-3 rounded-lg text-white font-medium shadow-lg transition-all ${
        type === 'success' ? 'bg-green-500' : 
        type === 'error' ? 'bg-red-500' : 
        'bg-blue-500'
    }`;
    toast.innerText = message;
    
    container.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

// Load Batches
async function loadBatches(status = null) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        let url = BATCHES_ENDPOINT;
        if (status) url += `?status=${status}`;
        
        const response = await axios.get(url, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        const batches = response.data.data || [];
        const pagination = response.data.pagination || {};
        
        // Convert API field names to match renderBatches expectations
        const formattedBatches = batches.map(batch => ({
            id: batch.batch_id || batch.id,
            status: batch.status,
            media_type: batch.media_type,
            total_records: batch.total_rows || batch.total_records,
            valid_records: batch.valid_rows || batch.valid_records,
            invalid_records: batch.invalid_rows || batch.invalid_records,
            created_at: batch.created_at,
        }));
        
        renderBatches(formattedBatches);
        updateStats(formattedBatches);
    } catch (error) {
        console.error('Error loading batches:', error);
        if (error.response?.status === 401) {
            showToast('Unauthenticated. Please login again.', 'error');
            setTimeout(() => window.location.href = '/login', 2000);
        } else {
            showToast('Failed to load batches', 'error');
        }
    }
}

// Render Batches in Table
function renderBatches(batches) {
    const tbody = document.getElementById('batchesTableBody');
    
    if (!batches || batches.length === 0) {
        tbody.innerHTML = `
            <tr>
                <td colspan="8" class="px-6 py-8 text-center text-gray-500">
                    <p class="font-medium">No batches yet</p>
                    <p class="text-sm mt-1">Upload a file to get started</p>
                </td>
            </tr>
        `;
        return;
    }
    
    tbody.innerHTML = batches.map(batch => `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-6 py-4 text-sm text-gray-900 font-medium">#${batch.id}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${
                    batch.media_type === 'OOH' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800'
                }">
                    ${batch.media_type}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${batch.total_records || 0}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${batch.valid_records || 0}</td>
            <td class="px-6 py-4 text-sm text-gray-600">${batch.invalid_records || 0}</td>
            <td class="px-6 py-4 text-sm">
                <span class="px-3 py-1 rounded-full text-xs font-semibold ${getStatusClass(batch.status)}">
                    ${formatStatus(batch.status)}
                </span>
            </td>
            <td class="px-6 py-4 text-sm text-gray-600">${formatDate(batch.created_at)}</td>
            <td class="px-6 py-4 text-sm space-x-2 flex">
                <button onclick="loadBatchDetails(${batch.id})" title="View Details"
                    class="text-blue-600 hover:text-blue-900 hover:underline">
                    View
                </button>
                ${batch.status === 'processed' ? `
                    <button onclick="approveBatch(${batch.id})" title="Approve Batch"
                        class="text-green-600 hover:text-green-900 hover:underline">
                        Approve
                    </button>
                ` : ''}
                ${batch.invalid_records > 0 ? `
                    <button onclick="openErrorModal(${batch.id})" title="View Errors"
                        class="text-red-600 hover:text-red-900 hover:underline">
                        Errors
                    </button>
                ` : ''}
            </td>
        </tr>
    `).join('');
}

// Update Stats
function updateStats(batches) {
    const total = batches.length;
    const processing = batches.filter(b => b.status === 'processing').length;
    const completed = batches.filter(b => b.status === 'completed' || b.status === 'approved').length;
    const failed = batches.filter(b => b.status === 'failed').length;
    
    document.getElementById('totalBatches').innerText = total;
    document.getElementById('processingBatches').innerText = processing;
    document.getElementById('completedBatches').innerText = completed;
    document.getElementById('failedBatches').innerText = failed;
}

// Status Helper
function getStatusClass(status) {
    const classes = {
        'pending': 'bg-yellow-100 text-yellow-800',
        'processing': 'bg-blue-100 text-blue-800',
        'processed': 'bg-purple-100 text-purple-800',
        'approved': 'bg-green-100 text-green-800',
        'completed': 'bg-green-100 text-green-800',
        'failed': 'bg-red-100 text-red-800'
    };
    return classes[status] || 'bg-gray-100 text-gray-800';
}

function formatStatus(status) {
    return status.charAt(0).toUpperCase() + status.slice(1);
}

function formatDate(dateString) {
    return new Date(dateString).toLocaleDateString('en-IN', {
        year: 'numeric',
        month: 'short',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
}

// Submit Upload
document.getElementById('uploadForm')?.addEventListener('submit', async (e) => {
    e.preventDefault();
    
    const excelFile = document.getElementById('excelFile').files[0];
    const pptFile = document.getElementById('pptFile').files[0];
    const mediaType = document.querySelector('input[name="media_type"]:checked').value;
    
    if (!excelFile && !pptFile) {
        showToast('Please select at least one file', 'error');
        return;
    }
    
    const formData = new FormData();
    if (excelFile) formData.append('excel_file', excelFile);
    if (pptFile) formData.append('ppt_file', pptFile);
    formData.append('media_type', mediaType);
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await axios.post(UPLOAD_ENDPOINT, formData, {
            headers: {
                'Content-Type': 'multipart/form-data',
                'X-CSRF-TOKEN': csrfToken
            }
        });
        
        showToast('✓ Files uploaded successfully', 'success');
        document.getElementById('uploadForm').reset();
        loadBatches();
    } catch (error) {
        console.error('Upload error:', error);
        const message = error.response?.data?.message || 'Upload failed';
        showToast(`✕ ${message}`, 'error');
    }
});

// Approve Batch
async function approveBatch(batchId) {
    if (!confirm('Are you sure you want to approve this batch? This will create hoardings in the database.')) {
        return;
    }
    
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await axios.post(`${APPROVE_ENDPOINT}/${batchId}`, {}, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        showToast('✓ Batch approved successfully', 'success');
        loadBatches();
    } catch (error) {
        console.error('Approval error:', error);
        const message = error.response?.data?.message || 'Approval failed';
        showToast(`✕ ${message}`, 'error');
    }
}

// Load Batch Details
async function loadBatchDetails(batchId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await axios.get(`${DETAILS_ENDPOINT}/${batchId}/details`, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        const batch = response.data.data || response.data;
        showDetailsModal(batch);
    } catch (error) {
        console.error('Error loading details:', error);
        showToast('Failed to load batch details', 'error');
    }
}

// Show Details Modal
function showDetailsModal(batch) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Batch #${batch.id} Details</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="px-6 py-4 space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-gray-600">Media Type</p>
                        <p class="font-semibold">${batch.media_type}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Status</p>
                        <p class="font-semibold text-capitalize">${batch.status}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Total Records</p>
                        <p class="font-semibold">${batch.total_records}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Valid Records</p>
                        <p class="font-semibold text-green-600">${batch.valid_records}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Invalid Records</p>
                        <p class="font-semibold text-red-600">${batch.invalid_records}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-600">Date</p>
                        <p class="font-semibold">${formatDate(batch.created_at)}</p>
                    </div>
                </div>
                
                ${batch.notes ? `
                    <div class="border-t pt-4">
                        <p class="text-sm text-gray-600 mb-2">Notes</p>
                        <p class="text-gray-700">${batch.notes}</p>
                    </div>
                ` : ''}
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

// Open Error Modal
async function openErrorModal(batchId) {
    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const response = await axios.get(`${DETAILS_ENDPOINT}/${batchId}/details`, {
            headers: { 'X-CSRF-TOKEN': csrfToken }
        });
        
        const invalidRecords = response.data.data?.invalid_records || [];
        showErrorsModal(invalidRecords, batchId);
    } catch (error) {
        console.error('Error loading errors:', error);
        showToast('Failed to load errors', 'error');
    }
}

// Show Errors Modal
function showErrorsModal(errors, batchId) {
    const modal = document.createElement('div');
    modal.className = 'fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4';
    modal.innerHTML = `
        <div class="bg-white rounded-xl max-w-2xl w-full max-h-96 overflow-y-auto">
            <div class="sticky top-0 bg-white border-b border-gray-200 px-6 py-4 flex justify-between items-center">
                <h3 class="text-xl font-bold text-gray-900">Batch #${batchId} - Validation Errors</h3>
                <button onclick="this.closest('.fixed').remove()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
            
            <div class="px-6 py-4">
                ${errors.length === 0 ? `
                    <p class="text-gray-500 text-center py-8">No errors found</p>
                ` : `
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Row</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Code</th>
                                    <th class="px-4 py-3 text-left font-semibold text-gray-900">Error Message</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y">
                                ${errors.map((error, idx) => `
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3 text-gray-600">${idx + 1}</td>
                                        <td class="px-4 py-3 font-mono text-gray-700">${error.code || 'N/A'}</td>
                                        <td class="px-4 py-3 text-red-600">${error.error_message || error.message || 'Unknown error'}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                `}
            </div>
        </div>
    `;
    document.body.appendChild(modal);
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

// Refresh Batches
function refreshBatches() {
    loadBatches();
    showToast('Refreshing batches...', 'info');
}

// Filter by Status
function filterByStatus(status) {
    loadBatches(status);
}
</script>
@endpush
