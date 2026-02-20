@extends('layouts.vendor')
@section('title', 'Import Inventory')

@section('content')
<!-- Page Header -->
<div class="mb-8">
    <h1 class="text-3xl sm:text-4xl font-bold text-gray-900">Inventory Import</h1>
    <p class="text-gray-600 mt-2">Upload and manage your inventory imports</p>
</div>

<!-- Toast Notifications -->
<div id="toastContainer" class="fixed top-4 right-4 z-50 space-y-2"></div>

<div id="uploadTimerWidget" class="fixed bottom-4 right-4 z-50 hidden">
    <div id="uploadTimerExpanded" class="bg-white border border-blue-200 rounded-xl shadow-lg p-4 w-80">
        <div class="flex items-start justify-between gap-2">
            <div>
                <p class="text-sm font-semibold text-blue-900">Please wait</p>
                <p id="uploadTimerMessage" class="text-xs text-blue-700 mt-1">Upload is being processed. You can continue browsing other screens.</p>
            </div>
            <div class="flex items-center gap-1">
                <button id="minimizeUploadTimer" type="button" class="px-2 py-1 text-xs bg-blue-50 text-blue-700 rounded hover:bg-blue-100">_</button>
            </div>
        </div>
        <div class="mt-3">
            <p id="uploadTimerCountdown" class="text-lg font-bold text-blue-900">05:00</p>
            <p id="uploadTimerBatch" class="text-xs text-gray-600 mt-1"></p>
        </div>
    </div>

    <button id="uploadTimerMinimized" type="button" class="hidden bg-white border border-blue-200 rounded-full shadow px-3 py-2 text-xs font-semibold text-blue-800 hover:bg-blue-50">
        Processing 05:00
    </button>
</div>

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

            </div>

            <!-- Stats Cards -->
            <div class="lg:col-span-2">
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <!-- Total Batches -->
                    <div onclick="openImportManagement('')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
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
                    <div onclick="openImportManagement('processing')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
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
                    <div onclick="openImportManagement('completed')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
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
                    <div onclick="openImportManagement('failed')" class="bg-white rounded-xl shadow-lg p-6 hover:shadow-xl transition-shadow cursor-pointer">
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
                            Manage Inventory
                        </button>
                    </div>
                </div>

                <div class="mt-6 bg-blue-50 border border-blue-200 rounded-xl p-5">
                    <h3 class="font-semibold text-blue-900 mb-2">Upload Guidance</h3>
                    <ul class="text-sm text-blue-800 space-y-1 mb-4">
                        <li>✓ Excel file up to 20MB</li>
                        <li>✓ PowerPoint file up to 50MB</li>
                        <li>✓ Use sample template columns exactly for smooth import</li>
                        <li>✓ For DOOH, include additional pricing fields</li>
                    </ul>

                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('vendor.import.sample-template', ['mediaType' => 'ooh']) }}" class="px-4 py-2 bg-white border border-blue-200 text-blue-800 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                            Download OOH Sample (CSV)
                        </a>
                        <a href="{{ route('vendor.import.sample-template', ['mediaType' => 'dooh']) }}" class="px-4 py-2 bg-white border border-blue-200 text-blue-800 rounded-lg text-sm font-medium hover:bg-blue-100 transition-colors">
                            Download DOOH Sample (CSV)
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Batch List Section -->
        <div class="bg-white rounded-xl shadow-lg overflow-hidden hidden">
            <div class="px-6 py-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h2 class="text-2xl font-bold text-gray-900">Import History</h2>
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

            <div class="px-6 py-4 border-t border-gray-200 flex items-center justify-between">
                <p id="historyPageInfo" class="text-sm text-gray-600">Showing 0-0 of 0</p>
                <div class="flex items-center space-x-2">
                    <button id="historyPrevBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                    <span id="historyPageLabel" class="text-sm text-gray-600">Page 1 / 1</span>
                    <button id="historyNextBtn" class="px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
                </div>
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
    const DETAILS_BASE = @json(route('vendor.import.enhanced'));
    const IMPORT_MANAGEMENT_URL = @json(route('vendor.import.enhanced'));
    const UPLOAD_LOCK_KEY = 'import_upload_lock_state_v1';
    const UPLOAD_LOCK_DURATION_SECONDS = 300;
    const historyState = {
        page: 1,
        per_page: 10,
        search: '',
    };
    const historyPagination = {
        total: 0,
        current_page: 1,
        last_page: 1,
        from: 0,
        to: 0,
    };
    let uploadTimerInterval = null;
    let uploadStatusPollInterval = null;

    function createHttpClient() {
        if (window.axios) {
            return window.axios;
        }

        const request = async (url, options = {}) => {
            const response = await fetch(url, {
                credentials: 'same-origin',
                ...options,
            });

            const text = await response.text();
            let data = {};

            try {
                data = text ? JSON.parse(text) : {};
            } catch (e) {
                data = { message: text || 'Request failed' };
            }

            if (!response.ok) {
                const error = new Error(data?.message || `HTTP ${response.status}`);
                error.response = {
                    status: response.status,
                    data,
                };
                throw error;
            }

            return {
                data,
                status: response.status,
            };
        };

        return {
            defaults: { headers: { common: {} } },
            get(url, config = {}) {
                return request(url, {
                    method: 'GET',
                    headers: config.headers || {},
                });
            },
            post(url, body = {}, config = {}) {
                const headers = { ...(config.headers || {}) };
                let payload = body;

                if (!(body instanceof FormData)) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                    payload = JSON.stringify(body);
                }

                return request(url, {
                    method: 'POST',
                    headers,
                    body: payload,
                });
            },
        };
    }

    const http = createHttpClient();
    
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    
    // Configure Axios with CSRF token
    http.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    http.defaults.headers.common['Accept'] = 'application/json';

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        setupFileInputs();
        setupFormSubmit();
        setupUploadLockTimerUi();
        restoreUploadLockState();
        loadBatches();
        setupHistoryPagination();
        setupHistorySearch();
        // Refresh every 5 seconds
        setInterval(() => loadBatches(historyState.page), 15000);
    });

    function setupHistoryPagination() {
        document.getElementById('historyPrevBtn')?.addEventListener('click', () => {
            if (historyPagination.current_page > 1) {
                loadBatches(historyPagination.current_page - 1);
            }
        });

        document.getElementById('historyNextBtn')?.addEventListener('click', () => {
            if (historyPagination.current_page < historyPagination.last_page) {
                loadBatches(historyPagination.current_page + 1);
            }
        });
    }

    function setupHistorySearch() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;

        let timer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => {
                historyState.search = (e.target.value || '').trim();
                loadBatches(1);
            }, 350);
        });
    }

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

    function getUploadLockState() {
        try {
            const raw = localStorage.getItem(UPLOAD_LOCK_KEY);
            return raw ? JSON.parse(raw) : null;
        } catch (e) {
            return null;
        }
    }

    function setUploadLockState(state) {
        localStorage.setItem(UPLOAD_LOCK_KEY, JSON.stringify(state));
    }

    function clearUploadLockState() {
        localStorage.removeItem(UPLOAD_LOCK_KEY);
    }

    function formatRemaining(seconds) {
        const safe = Math.max(0, Number(seconds || 0));
        const mm = String(Math.floor(safe / 60)).padStart(2, '0');
        const ss = String(safe % 60).padStart(2, '0');
        return `${mm}:${ss}`;
    }

    function setUploadFormLocked(locked) {
        const uploadForm = document.getElementById('uploadForm');
        if (!uploadForm) return;

        uploadForm.querySelectorAll('input, button').forEach((element) => {
            if (element.id === 'minimizeUploadTimer') {
                return;
            }
            element.disabled = locked;
        });

        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        if (submitBtn && submitText) {
            if (locked) {
                submitBtn.disabled = true;
                submitText.textContent = 'Please wait...';
            } else {
                submitBtn.disabled = false;
                submitText.textContent = 'Upload Files';
            }
        }
    }

    function setupUploadLockTimerUi() {
        document.getElementById('minimizeUploadTimer')?.addEventListener('click', () => {
            const lock = getUploadLockState();
            if (!lock) return;
            lock.minimized = true;
            setUploadLockState(lock);
            renderUploadLock(lock);
        });

        document.getElementById('uploadTimerMinimized')?.addEventListener('click', () => {
            const lock = getUploadLockState();
            if (!lock) return;
            lock.minimized = false;
            setUploadLockState(lock);
            renderUploadLock(lock);
        });
    }

    function renderUploadLock(lockState) {
        const widget = document.getElementById('uploadTimerWidget');
        const expanded = document.getElementById('uploadTimerExpanded');
        const minimized = document.getElementById('uploadTimerMinimized');
        const countdown = document.getElementById('uploadTimerCountdown');
        const message = document.getElementById('uploadTimerMessage');
        const batchLabel = document.getElementById('uploadTimerBatch');

        if (!widget || !expanded || !minimized || !countdown || !message || !batchLabel) {
            return;
        }

        if (!lockState) {
            widget.classList.add('hidden');
            expanded.classList.remove('hidden');
            minimized.classList.add('hidden');
            return;
        }

        widget.classList.remove('hidden');

        const remaining = Math.max(0, Math.floor((Number(lockState.expiresAt || 0) - Date.now()) / 1000));
        const timeText = formatRemaining(remaining);
        countdown.textContent = timeText;
        minimized.textContent = `Processing ${timeText}`;
        batchLabel.textContent = `Batch #${lockState.batchId || '-'} is processing`;

        const statusText = (lockState.status || 'processing').toLowerCase();
        if (statusText === 'failed') {
            message.textContent = 'Processing failed. Please re-upload after checking content.';
        } else if (statusText === 'completed') {
            message.textContent = 'Processing completed successfully.';
        } else {
            message.textContent = 'Upload is being processed. You can continue browsing other screens.';
        }

        if (lockState.minimized) {
            expanded.classList.add('hidden');
            minimized.classList.remove('hidden');
        } else {
            expanded.classList.remove('hidden');
            minimized.classList.add('hidden');
        }
    }

    function stopUploadLockIntervals() {
        if (uploadTimerInterval) {
            clearInterval(uploadTimerInterval);
            uploadTimerInterval = null;
        }
        if (uploadStatusPollInterval) {
            clearInterval(uploadStatusPollInterval);
            uploadStatusPollInterval = null;
        }
    }

    function releaseUploadLock(finalStatus, withMessage = true) {
        stopUploadLockIntervals();
        const lock = getUploadLockState();

        setUploadFormLocked(false);
        clearUploadLockState();
        renderUploadLock(null);

        if (!withMessage) {
            return;
        }

        if (finalStatus === 'completed') {
            if (window.Swal) {
                Swal.fire({
                    icon: 'success',
                    title: 'Import Completed',
                    text: 'Import completed successfully. You can upload again.',
                });
            } else {
                showToast('Import completed successfully. You can upload again.', 'success');
            }
        } else if (finalStatus === 'failed') {
            showToast('Import failed. Please re-upload after checking content.', 'error');
        } else if (finalStatus === 'timeout') {
            showToast('Import did not complete in 5 minutes. Please re-upload after checking content.', 'error');
        } else if (lock?.status) {
            showToast(`Import status changed to ${lock.status}.`, 'info');
        }
    }

    async function pollUploadStatus() {
        const lock = getUploadLockState();
        if (!lock?.batchId) {
            return;
        }

        try {
            const response = await http.get(`${API_BASE}/${lock.batchId}/status`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                }
            });

            const status = (response?.data?.data?.status || '').toLowerCase();
            if (!status) {
                return;
            }

            lock.status = status;
            setUploadLockState(lock);
            renderUploadLock(lock);

            if (status === 'completed' || status === 'failed') {
                releaseUploadLock(status, true);
            }
        } catch (error) {
            console.error('Status polling failed:', error);
        }
    }

    function startUploadLock(batchId) {
        const now = Date.now();
        const lock = {
            batchId,
            startedAt: now,
            expiresAt: now + (UPLOAD_LOCK_DURATION_SECONDS * 1000),
            minimized: false,
            status: 'processing',
        };

        setUploadLockState(lock);
        setUploadFormLocked(true);
        renderUploadLock(lock);
        stopUploadLockIntervals();

        uploadTimerInterval = setInterval(() => {
            const state = getUploadLockState();
            if (!state) {
                stopUploadLockIntervals();
                return;
            }

            const remaining = Math.floor((Number(state.expiresAt || 0) - Date.now()) / 1000);
            if (remaining <= 0) {
                releaseUploadLock('timeout', true);
                return;
            }

            renderUploadLock(state);
        }, 1000);

        pollUploadStatus();
        uploadStatusPollInterval = setInterval(pollUploadStatus, 10000);
    }

    function restoreUploadLockState() {
        const lock = getUploadLockState();
        if (!lock) {
            setUploadFormLocked(false);
            renderUploadLock(null);
            return;
        }

        const remaining = Math.floor((Number(lock.expiresAt || 0) - Date.now()) / 1000);
        if (remaining <= 0) {
            releaseUploadLock('timeout', false);
            return;
        }

        setUploadFormLocked(true);
        renderUploadLock(lock);

        stopUploadLockIntervals();
        uploadTimerInterval = setInterval(() => {
            const state = getUploadLockState();
            if (!state) {
                stopUploadLockIntervals();
                return;
            }

            const left = Math.floor((Number(state.expiresAt || 0) - Date.now()) / 1000);
            if (left <= 0) {
                releaseUploadLock('timeout', true);
                return;
            }

            renderUploadLock(state);
        }, 1000);

        pollUploadStatus();
        uploadStatusPollInterval = setInterval(pollUploadStatus, 10000);
    }

    async function submitUpload(e) {
        e.preventDefault();

        const existingLock = getUploadLockState();
        if (existingLock) {
            showError('Please wait until current import completes or fails. You can upload again after that.');
            return;
        }
        
        const excelFile = document.getElementById('excelFile').files[0];
        const pptFile = document.getElementById('pptFile').files[0];
        const mediaType = document.querySelector('input[name="media_type"]:checked').value;

        if (!excelFile || !pptFile) {
            showError('Please select both Excel and PowerPoint files');
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
            formData.append('excel', excelFile);
            formData.append('ppt', pptFile);
            formData.append('media_type', mediaType);

            const response = await http.post(API_BASE + '/upload', formData, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            showToast('Upload successful!', 'success');
            document.getElementById('uploadForm').reset();
            document.getElementById('excelFileName').classList.add('hidden');
            document.getElementById('pptFileName').classList.add('hidden');

            const uploadedBatchId = response?.data?.batch_id || response?.data?.data?.batch_id;
            if (uploadedBatchId) {
                startUploadLock(uploadedBatchId);
                showToast('Please wait. Processing started and upload is locked for up to 5 minutes.', 'info');
            }

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
    async function loadBatches(page = historyState.page) {
        try {
            historyState.page = Math.max(1, Number(page || 1));
            const params = new URLSearchParams({
                page: String(historyState.page),
                per_page: String(historyState.per_page),
            });

            if (historyState.search) {
                params.append('search', historyState.search);
            }

            const response = await http.get(`${API_BASE}?${params.toString()}`, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const batches = response.data.data || [];
            const pagination = response.data.pagination || {};
            historyPagination.total = pagination.total || 0;
            historyPagination.current_page = pagination.current_page || 1;
            historyPagination.last_page = pagination.last_page || 1;
            historyPagination.from = pagination.from || 0;
            historyPagination.to = pagination.to || 0;

            const summary = response.data.summary || null;
            updateStats(batches, summary);

            const tableContainer = document.getElementById('batchesTableBody');
            if (tableContainer && !tableContainer.closest('.hidden')) {
                renderBatches(batches);
                renderHistoryPagination();
            }
        } catch (error) {
            console.error('Failed to load batches:', error);
        }
    }

    function renderHistoryPagination() {
        const info = document.getElementById('historyPageInfo');
        const label = document.getElementById('historyPageLabel');
        const prevBtn = document.getElementById('historyPrevBtn');
        const nextBtn = document.getElementById('historyNextBtn');

        if (info) {
            info.textContent = `Showing ${historyPagination.from}-${historyPagination.to} of ${historyPagination.total}`;
        }
        if (label) {
            label.textContent = `Page ${historyPagination.current_page} / ${historyPagination.last_page}`;
        }
        if (prevBtn) {
            prevBtn.disabled = historyPagination.current_page <= 1;
        }
        if (nextBtn) {
            nextBtn.disabled = historyPagination.current_page >= historyPagination.last_page;
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
                <td class="px-6 py-4 text-sm font-mono text-blue-600">#${batch.batch_id}</td>
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
                        <button onclick="loadBatchDetails(${batch.batch_id})" 
                            class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors text-xs font-medium">
                            View
                        </button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function updateStats(batches, summary = null) {
        const total = summary?.total ?? (historyPagination.total || batches.length);
        const processing = summary?.processing ?? batches.filter(b => b.status === 'processing').length;
        const completed = summary?.completed ?? batches.filter(b => b.status === 'completed').length;
        const failed = summary?.failed ?? batches.filter(b => b.status === 'failed').length;

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
            const response = await http.post(`${API_BASE}/${batchId}/approve`, {}, {
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
            const response = await http.get(`${API_BASE}/${batchId}/details`, {
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
        openImportManagement(status);
    }

    function openImportManagement(status = '') {
        const url = new URL(IMPORT_MANAGEMENT_URL, window.location.origin);
        url.searchParams.set('tab', 'batches');
        window.location.href = url.toString();
    }

    async function loadBatchDetails(batchId) {
        window.location.href = `${DETAILS_BASE}/batches/${batchId}`;
    }
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
