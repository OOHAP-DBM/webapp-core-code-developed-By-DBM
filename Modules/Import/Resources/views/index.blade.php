@extends('layouts.vendor')

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
                                    <p id="excelFileName"
                                        class="mt-2 text-sm text-gray-600 font-medium hidden flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </p>
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
                                    <p id="pptFileName"
                                        class="mt-2 text-sm text-gray-600 font-medium hidden flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-500" fill="currentColor"
                                            viewBox="0 0 20 20">
                                            <path fill-rule="evenodd"
                                                d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                clip-rule="evenodd"></path>
                                        </svg>
                                    </p>
                                </div>
                            </div>

                            <!-- Media Type Selection -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-3">
                                    Import Type
                                </label>
                                <div class="space-y-2">
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="media_type" value="ooh" checked
                                            class="w-4 h-4 text-blue-600" />
                                        <span class="ml-3 text-sm text-gray-700">OOH (Out of Home)</span>
                                    </label>
                                    <label class="flex items-center cursor-pointer">
                                        <input type="radio" name="media_type" value="dooh"
                                            class="w-4 h-4 text-blue-600" />
                                        <span class="ml-3 text-sm text-gray-700">DOOH (Digital OOH)</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <button type="submit" id="submitBtn"
                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-3 px-4 rounded-lg transition-all duration-200 flex items-center justify-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed">
                                <svg id="submitIcon" class="w-5 h-5" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <span id="submitText">Upload Files</span>
                            </button>

                            <!-- Error Messages -->
                            <div id="errorMessages" class="hidden space-y-2"></div>
                        </form>
                    </div>
                </div>

                <!-- Info Card -->
                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <h3 class="font-semibold text-blue-900 mb-2">Upload Guidelines</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li>✓ Excel file up to 20MB</li>
                        <li>✓ PowerPoint file up to 50MB</li>
                        <li>✓ Select import type</li>
                        <li>✓ Both files are optional</li>
                    </ul>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Total Batches -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Total Batches</p>
                                <p id="totalBatches" class="text-3xl font-bold text-gray-900 mt-2">0</p>
                            </div>
                            <div class="bg-blue-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Processing -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Processing</p>
                                <p id="processingBatches" class="text-3xl font-bold text-yellow-600 mt-2">0</p>
                            </div>
                            <div class="bg-yellow-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Completed -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Completed</p>
                                <p id="completedBatches" class="text-3xl font-bold text-green-600 mt-2">0</p>
                            </div>
                            <div class="bg-green-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <!-- Failed -->
                    <div class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-gray-600 text-sm font-medium">Failed</p>
                                <p id="failedBatches" class="text-3xl font-bold text-red-600 mt-2">0</p>
                            </div>
                            <div class="bg-red-100 rounded-full p-3">
                                <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4v2m0 4v2M7.46 7.46a9 9 0 1012.08 12.08A9 9 0 007.46 7.46z">
                                    </path>
                                </svg>
                            </div>
                        </div>
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
                        <!-- Loaded dynamically -->
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                        </path>
                                    </svg>
                                    <p class="text-lg font-medium">No batches yet</p>
                                    <p class="text-sm">Upload a file to get started</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

<!-- Approve Confirmation Modal -->
<div id="approveModal"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full transform transition-all duration-300">
        <div class="p-6 border-b border-gray-200">
            <h3 class="text-lg font-bold text-gray-900">Confirm Approval</h3>
        </div>
        <div class="p-6">
            <p class="text-gray-600 mb-4">
                Are you sure you want to approve this batch? This will create all hoardings from valid records.
            </p>
            <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                <p class="text-sm text-blue-800">
                    <strong>Batch ID:</strong> <span id="approveBatchId" class="font-mono">N/A</span>
                </p>
                <p class="text-sm text-blue-800 mt-2">
                    <strong>Valid Records:</strong> <span id="approveBatchValid">0</span>
                </p>
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end space-x-3">
            <button onclick="closeApproveModal()"
                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                Cancel
            </button>
            <button onclick="confirmApprove()"
                class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition-colors font-medium flex items-center space-x-2 disabled:opacity-50 disabled:cursor-not-allowed"
                id="confirmApproveBtn">
                <span>Approve</span>
            </button>
        </div>
    </div>
</div>

<!-- Error Details Modal -->
<div id="errorModal"
    class="fixed inset-0 bg-black bg-opacity-50 z-40 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full transform transition-all duration-300 flex flex-col max-h-screen">
        <div class="p-6 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-bold text-gray-900">Error Details</h3>
                <button onclick="closeErrorModal()"
                    class="text-gray-400 hover:text-gray-600 transition-colors">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto p-6">
            <div id="errorTableContainer" class="overflow-x-auto">
                <!-- Loaded dynamically -->
            </div>
        </div>
        <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
            <button onclick="closeErrorModal()"
                class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition-colors font-medium">
                Close
            </button>
        </div>
    </div>
</div>

<script>
    const API_BASE = '/api/import';
    
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Configure Axios with CSRF token
    axios.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    axios.defaults.headers.common['Accept'] = 'application/json';

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        setupFileInputs();
        setupFormSubmit();
        loadBatches();
        // Refresh every 5 seconds
        setInterval(loadBatches, 5000);
    });

    // File input handlers
    function setupFileInputs() {
        const excelInput = document.getElementById('excelFile');
        const pptInput = document.getElementById('pptFile');

        excelInput.addEventListener('change', (e) => {
            const fileName = e.target.files[0]?.name || '';
            updateFileDisplay('excelFileName', fileName);
        });

        pptInput.addEventListener('change', (e) => {
            const fileName = e.target.files[0]?.name || '';
            updateFileDisplay('pptFileName', fileName);
        });

        // Drag and drop
        setupDragDrop(excelInput);
        setupDragDrop(pptInput);
    }

    function setupDragDrop(input) {
        const label = input.nextElementSibling;
        
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, preventDefaults, false);
        });

        function preventDefaults(e) {
            e.preventDefault();
            e.stopPropagation();
        }

        ['dragenter', 'dragover'].forEach(eventName => {
            label.addEventListener(eventName, () => {
                label.classList.add('bg-blue-50', 'border-blue-400');
            });
        });

        ['dragleave', 'drop'].forEach(eventName => {
            label.addEventListener(eventName, () => {
                label.classList.remove('bg-blue-50', 'border-blue-400');
            });
        });

        label.addEventListener('drop', (e) => {
            const dt = e.dataTransfer;
            const files = dt.files;
            input.files = files;
            const event = new Event('change', { bubbles: true });
            input.dispatchEvent(event);
        });
    }

    function updateFileDisplay(elementId, fileName) {
        const element = document.getElementById(elementId);
        if (fileName) {
            element.textContent = `✓ ${fileName}`;
            element.classList.remove('hidden');
        } else {
            element.classList.add('hidden');
        }
    }

    // Form submission
    function setupFormSubmit() {
        document.getElementById('uploadForm').addEventListener('submit', submitUpload);
    }

    async function submitUpload(e) {
        e.preventDefault();
        
        const excelFile = document.getElementById('excelFile').files[0];
        const pptFile = document.getElementById('pptFile').files[0];
        const mediaType = document.querySelector('input[name="media_type"]:checked').value;

        if (!excelFile && !pptFile) {
            showError('Please select at least one file');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitIcon = document.getElementById('submitIcon');
        const errorMessages = document.getElementById('errorMessages');
        
        submitBtn.disabled = true;
        submitText.textContent = 'Uploading...';
        submitIcon.classList.add('animate-spin');
        errorMessages.classList.add('hidden');

        try {
            const formData = new FormData();
            if (excelFile) formData.append('file', excelFile);
            if (pptFile) formData.append('ppt_file', pptFile);
            formData.append('media_type', mediaType);

            const response = await axios.post(API_BASE + '/upload', formData, {
                headers: {
                    'Content-Type': 'multipart/form-data',
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            showToast('Upload successful!', 'success');
            document.getElementById('uploadForm').reset();
            document.getElementById('excelFileName').classList.add('hidden');
            document.getElementById('pptFileName').classList.add('hidden');
            await loadBatches();
        } catch (error) {
            const errors = error.response?.data?.errors || {};
            if (Object.keys(errors).length > 0) {
                displayErrors(errors);
            } else {
                showToast(error.response?.data?.message || 'Upload failed', 'error');
            }
        } finally {
            submitBtn.disabled = false;
            submitText.textContent = 'Upload Files';
            submitIcon.classList.remove('animate-spin');
        }
    }

    function displayErrors(errors) {
        const container = document.getElementById('errorMessages');
        container.innerHTML = '';
        Object.values(errors).flat().forEach(error => {
            const div = document.createElement('div');
            div.className = 'bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm';
            div.textContent = error;
            container.appendChild(div);
        });
        container.classList.remove('hidden');
    }

    function showError(message) {
        const container = document.getElementById('errorMessages');
        container.innerHTML = `<div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg text-sm">${message}</div>`;
        container.classList.remove('hidden');
    }

    // Load batches
    async function loadBatches() {
        try {
            const response = await axios.get(API_BASE, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const batches = response.data.data || [];
            renderBatches(batches);
            updateStats(batches);
        } catch (error) {
            console.error('Failed to load batches:', error);
        }
    }

    function renderBatches(batches) {
        const tbody = document.getElementById('batchesTableBody');
        
        if (batches.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            <p class="text-lg font-medium">No batches yet</p>
                            <p class="text-sm">Upload a file to get started</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        tbody.innerHTML = batches.map(batch => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm font-mono text-blue-600">#${batch.id}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${
                        batch.media_type === 'ooh' 
                            ? 'bg-purple-100 text-purple-800' 
                            : 'bg-indigo-100 text-indigo-800'
                    }">
                        ${batch.media_type?.toUpperCase() || 'N/A'}
                    </span>
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">${batch.total_rows}</td>
                <td class="px-6 py-4 text-sm">
                    <span class="text-green-600 font-medium">${batch.valid_rows}</span>
                </td>
                <td class="px-6 py-4 text-sm">
                    <span class="text-red-600 font-medium">${batch.invalid_rows}</span>
                </td>
                <td class="px-6 py-4 text-sm">
                    ${getStatusBadge(batch.status)}
                </td>
                <td class="px-6 py-4 text-sm text-gray-600">
                    ${formatDate(batch.created_at)}
                </td>
                <td class="px-6 py-4 text-sm">
                    <div class="flex items-center space-x-2">
                        ${batch.status === 'processed' ? `
                            <button onclick="openApproveModal(${batch.id}, ${batch.valid_rows})" 
                                class="px-3 py-1 bg-green-100 hover:bg-green-200 text-green-700 rounded-lg transition-colors text-xs font-medium">
                                Approve
                            </button>
                        ` : ''}
                        ${batch.invalid_rows > 0 ? `
                            <button onclick="openErrorModal(${batch.id})" 
                                class="px-3 py-1 bg-red-100 hover:bg-red-200 text-red-700 rounded-lg transition-colors text-xs font-medium">
                                Errors
                            </button>
                        ` : ''}
                        <button onclick="loadBatchDetails(${batch.id})" 
                            class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors text-xs font-medium">
                            View
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateStats(batches) {
        const total = batches.length;
        const processing = batches.filter(b => b.status === 'processing').length;
        const completed = batches.filter(b => b.status === 'completed').length;
        const failed = batches.filter(b => b.status === 'failed').length;

        document.getElementById('totalBatches').textContent = total;
        document.getElementById('processingBatches').textContent = processing;
        document.getElementById('completedBatches').textContent = completed;
        document.getElementById('failedBatches').textContent = failed;
    }

    function getStatusBadge(status) {
        const badgeConfig = {
            'uploaded': { bg: 'bg-gray-100', text: 'text-gray-800', icon: '📤' },
            'processing': { bg: 'bg-yellow-100', text: 'text-yellow-800', icon: '⏳' },
            'processed': { bg: 'bg-blue-100', text: 'text-blue-800', icon: '✓' },
            'completed': { bg: 'bg-green-100', text: 'text-green-800', icon: '✓✓' },
            'cancelled': { bg: 'bg-gray-100', text: 'text-gray-800', icon: '✕' },
            'failed': { bg: 'bg-red-100', text: 'text-red-800', icon: '✕' }
        };

        const config = badgeConfig[status] || badgeConfig['uploaded'];
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${config.bg} ${config.text}">
                    ${config.icon} ${status.charAt(0).toUpperCase() + status.slice(1)}
                </span>`;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        const date = new Date(dateString);
        return new Intl.DateTimeFormat('en-US', {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        }).format(date);
    }

    // Approve modal
    function openApproveModal(batchId, validRows) {
        document.getElementById('approveBatchId').textContent = `#${batchId}`;
        document.getElementById('approveBatchValid').textContent = validRows;
        document.getElementById('approveModal').classList.remove('hidden');
        document.getElementById('approveModal').dataset.batchId = batchId;
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
    }

    async function confirmApprove() {
        const batchId = document.getElementById('approveModal').dataset.batchId;
        const btn = document.getElementById('confirmApproveBtn');
        
        btn.disabled = true;
        
        try {
            const response = await axios.post(`${API_BASE}/${batchId}/approve`, {}, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            showToast('Batch approved successfully!', 'success');
            closeApproveModal();
            await loadBatches();
        } catch (error) {
            showToast(error.response?.data?.message || 'Approval failed', 'error');
        } finally {
            btn.disabled = false;
        }
    }

    // Error modal
    async function openErrorModal(batchId) {
        try {
            const response = await axios.get(`${API_BASE}/${batchId}/details`, {
                headers: {
                    'Authorization': `Bearer ${getAuthToken()}`
                }
            });

            const invalidRecords = response.data.data?.invalid_records || [];
            renderErrorTable(invalidRecords);
            document.getElementById('errorModal').classList.remove('hidden');
        } catch (error) {
            showToast('Failed to load error details', 'error');
        }
    }

    function renderErrorTable(records) {
        const container = document.getElementById('errorTableContainer');
        
        if (records.length === 0) {
            container.innerHTML = '<p class="text-gray-600">No errors found</p>';
            return;
        }

        let html = `
            <table class="w-full text-sm">
                <thead>
                    <tr class="bg-gray-50 border-b">
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Row</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Code</th>
                        <th class="px-4 py-3 text-left font-semibold text-gray-900">Error Message</th>
                    </tr>
                </thead>
                <tbody class="divide-y">
        `;

        records.forEach((record, index) => {
            html += `
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3 text-gray-600">${index + 1}</td>
                    <td class="px-4 py-3 font-mono text-gray-700">${record.code || 'N/A'}</td>
                    <td class="px-4 py-3 text-red-600">${record.error_message || 'Unknown error'}</td>
                </tr>
            `;
        });

        html += `
                </tbody>
            </table>
        `;

        container.innerHTML = html;
    }

    function closeErrorModal() {
        document.getElementById('errorModal').classList.add('hidden');
    }

    // Toast notifications
    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        
        const typeConfig = {
            'success': { bg: 'bg-green-500', icon: '✓' },
            'error': { bg: 'bg-red-500', icon: '✕' },
            'info': { bg: 'bg-blue-500', icon: 'ℹ' }
        };

        const config = typeConfig[type] || typeConfig['info'];

        toast.className = `${config.bg} text-white px-6 py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-pulse`;
        toast.innerHTML = `
            <span class="font-bold">${config.icon}</span>
            <span>${message}</span>
        `;

        container.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    }

    // Utility functions
    function getAuthToken() {
        return document.querySelector('[data-auth-token]')?.dataset.authToken || 
               document.querySelector('meta[name="auth-token"]')?.content || 
               'test-token';
    }

    function refreshBatches() {
        loadBatches();
        showToast('Batches refreshed', 'info');
    }

    function filterByStatus(status) {
        loadBatches(); // In production, add status filter parameter
        showToast(`Filtered by: ${status}`, 'info');
    }

    async function loadBatchDetails(batchId) {
        try {
            const response = await axios.get(`${API_BASE}/${batchId}/details`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });
            console.log('Batch details:', response.data);
            showToast('Batch details loaded', 'info');
        } catch (error) {
            showToast('Failed to load batch details', 'error');
        }
    }

    // Search functionality
    document.getElementById('searchInput')?.addEventListener('input', (e) => {
        const searchTerm = e.target.value.toLowerCase();
        document.querySelectorAll('#batchesTableBody tr').forEach(row => {
            const text = row.textContent.toLowerCase();
            row.style.display = text.includes(searchTerm) ? '' : 'none';
        });
    });
</script>

<style>
    @keyframes spin {
        to {
            transform: rotate(360deg);
        }
    }

    .animate-spin {
        animation: spin 1s linear infinite;
    }

    @keyframes pulse {
        0%, 100% {
            opacity: 1;
        }
        50% {
            opacity: 0.8;
        }
    }

    .animate-pulse {
        animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
    }
</style>
@endsection
