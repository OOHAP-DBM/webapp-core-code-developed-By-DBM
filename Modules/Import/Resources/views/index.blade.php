@extends('layouts.vendor')
@section('title', 'Import Inventory')

@section('content')
<div class="p-4 sm:p-6 lg:p-8">
        <!-- Page Header -->
    <div class="mb-6">
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Inventory Import</h1>
        <p class="text-gray-500 mt-1 text-sm">Upload and manage your inventory imports</p>
    </div>

    <!-- Toast Notifications -->
    <div id="toastContainer" class="fixed top-4 left-3 right-3 sm:left-auto sm:right-4 z-50 space-y-2"></div>

    <!-- Upload Timer Widget (unchanged) -->
    <div id="uploadTimerWidget" class="fixed bottom-4 left-3 right-3 sm:left-auto sm:right-4 z-50 hidden">
        <div id="uploadTimerExpanded" class="bg-white border border-blue-200 rounded-xl shadow-lg p-4 w-full sm:w-80 max-w-sm">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-sm font-semibold text-blue-900">Please wait</p>
                    <p id="uploadTimerMessage" class="text-xs text-blue-700 mt-1">
                        Currently System is mapping your inventory. Please wait 5 minutes before starting a new upload. You can continue browsing other screens.
                    </p>
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

    <!-- ══════════════════════════════════════════
        STATS PILL TABS  (top row — matches screenshot)
    ══════════════════════════════════════════ -->
    <div class="flex flex-wrap gap-2 mb-6">
        <button onclick="openImportManagement('')"
                class="stat-pill flex items-center gap-2 px-4 py-2 md:h-15 cursor-pointer rounded-sm  text-md font-semibold transition-colors bg-blue-50 border-blue-200 text-blue-700 hover:bg-blue-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Total Uploads
            <span id="totalBatches" class=" text-blue-800 text-xs font-bold rounded-full px-1.5 py-0.5 ">(0)</span>
        </button>

        <button onclick="openImportManagement('processing')"
                class="stat-pill flex items-center gap-2 px-4 py-2 md:h-15 cursor-pointer rounded-sm  text-md font-semibold transition-colors bg-orange-50 border-orange-200 text-orange-700 hover:bg-orange-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Processing
            <span id="processingBatches" class="bg-orange-200 text-orange-800 text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
        </button>

        <button onclick="openImportManagement('approved')"
                class="stat-pill flex items-center gap-2 px-4 py-2 md:h-15 cursor-pointer rounded-sm text-md font-semibold transition-colors bg-green-50 border-green-200 text-green-700 hover:bg-green-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Completed
            <span id="approvedBatches" class="bg-green-200 text-green-800 text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
        </button>

         <button onclick="openImportManagement('completed')"
                class="stat-pill flex items-center gap-2 px-4 py-2 md:h-15 cursor-pointer rounded-sm text-md font-semibold transition-colors bg-gray-50 border-gray-200 text-gray-700 hover:bg-gray-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Draft
            <span id="completedBatches" class="bg-gray-200 text-gray-800 text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
        </button>

        <button onclick="openImportManagement('failed')"
                class="stat-pill flex items-center gap-2 px-4 py-2 md:h-15 cursor-pointer rounded-sm  text-md font-semibold transition-colors bg-red-50 border-red-200 text-red-700 hover:bg-red-100">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            Failed
            <span id="failedBatches" class="bg-red-200 text-red-800 text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
        </button>
    </div>

    <!-- ══════════════════════════════════════════
        MAIN UPLOAD CARD
    ══════════════════════════════════════════ -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-5 sm:p-7 max-w-7xl">

        <form id="uploadForm" class="space-y-5" autocomplete="off">
            @csrf

            <!-- ── Hoarding Type ── -->
            <div>
                <p class="text-sm font-semibold text-gray-800 mb-3">Select Hoarding Type</p>
                <div class="flex items-center gap-6">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="media_type" value="ooh"
                            class="w-4 h-4 accent-green-600"
                            onchange="onHoardingTypeChange(this.value)">
                        <span class="text-sm text-gray-700">OOH (Out of Home)</span>
                    </label>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="media_type" value="dooh"
                            class="w-4 h-4 accent-green-600"
                            onchange="onHoardingTypeChange(this.value)">
                        <span class="text-sm text-gray-700">DOOH (Digital Out of Home)</span>
                    </label>
                </div>
            </div>

            <!-- ── Dynamic upload heading (hidden until type selected) ── -->
            <div id="uploadHeadingWrap" class="hidden">
                <p id="uploadHeading" class="text-sm font-bold text-blue-600"></p>
            </div>

            <!-- ── File upload zones (side-by-side, hidden until type selected) ── -->
            <div id="uploadZonesWrap" class="hidden">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">

                    <!-- Excel -->
                    <div>
                        <p class="text-xs font-semibold text-gray-600 mb-2">Excel File (.xlsx)</p>
                        <div class="relative">
                            <input type="file" id="excelFile" name="excel_file" accept=".xlsx" class="hidden" />
                            <label for="excelFile"
                                id="excelDropZone"
                                class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">
                                <svg class="w-8 h-8 text-blue-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-8m0 0l-3 3m3-3l3 3" />
                                </svg>
                                <span class="text-xs text-gray-500 text-center px-2">Select your file or drag &amp; drop</span>
                                <span class="text-[11px] text-gray-400 mt-0.5">Max 20 MB .xlsx</span>
                                <span class="mt-3 px-4 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg group-hover:bg-green-700 transition-colors">Browse</span>
                            </label>
                        </div>
                        <p id="excelFileName" class="mt-1.5 text-xs text-green-700 font-medium hidden flex items-center gap-1">
                            <span class="w-5 h-5 flex items-center justify-center rounded-full bg-green-100 text-green-700 mr-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </span>
                            <span id="excelFileNameText"></span>
                        </p>
                    </div>

                    <!-- PowerPoint -->
                    <div>
                        <p class="text-xs font-semibold text-gray-600 mb-2">PowerPoint File (.pptx)</p>
                        <div class="relative">
                            <input type="file" id="pptFile" name="ppt_file" accept=".pptx,.ppt" class="hidden" />
                            <label for="pptFile"
                                id="pptDropZone"
                                class="flex flex-col items-center justify-center w-full h-44 border-2 border-dashed border-gray-300 rounded-xl cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-all duration-200 group">
                                <svg class="w-8 h-8 text-blue-400 mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <circle cx="12" cy="12" r="10" stroke="currentColor" stroke-width="2" fill="none" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 16v-8m0 0l-3 3m3-3l3 3" />
                                </svg>
                                <span class="text-xs text-gray-500 text-center px-2">Select your file or drag &amp; drop</span>
                                <span class="text-[11px] text-gray-400 mt-0.5">Max 40 MB .pptx</span>
                                <span class="mt-3 px-4 py-1.5 bg-green-600 text-white text-xs font-semibold rounded-lg group-hover:bg-green-700 transition-colors">Browse</span>
                            </label>
                        </div>
                        <p id="pptFileName" class="mt-1.5 text-xs text-green-700 font-medium hidden flex items-center gap-1">
                            <span class="w-5 h-5 flex items-center justify-center rounded-full bg-green-100 text-green-700 mr-1">
                                <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            </span>
                            <span id="pptFileNameText"></span>
                        </p>
                    </div>

                </div>
            </div>

            <!-- ── Upload button (hidden until type selected) ── -->
            <div id="submitWrap" class="hidden">
                <button type="submit" id="submitBtn"
                        class="flex items-center gap-2 min-h-[44px] bg-blue-600 hover:bg-blue-700 cursor-pointer text-white font-semibold py-2.5 px-6 rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                    <svg id="submitIcon" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    <span id="submitText">Upload Files</span>
                </button>
            </div>
             <!-- ── Upload Progress Bar (shown during active upload) ── -->
            <div id="uploadProgressWrap" class="hidden mt-4">
                <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-semibold text-blue-900 flex items-center gap-2">
                            <svg class="w-4 h-4 animate-spin text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                            Uploading — please don't close or leave this tab
                        </span>
                        <span id="uploadProgressPercent" class="text-sm font-bold text-blue-700">0%</span>
                    </div>
                    <div class="w-full bg-blue-100 rounded-full h-3 overflow-hidden">
                        <div id="uploadProgressBar"
                             class="bg-blue-600 h-3 rounded-full transition-all duration-300"
                             style="width: 0%">
                        </div>
                    </div>
                    <p id="uploadProgressLabel" class="text-xs text-blue-600 mt-1.5">Connecting to server...</p>
                </div>
            </div>

            <!-- Error Messages -->
            <div id="errorMessages" class="hidden space-y-2"></div>

        </form>

        <!-- ── Upload Guidance ── -->
        <div class="mt-6 bg-blue-50 border border-blue-100 rounded-xl p-4">
            <p class="text-sm font-semibold text-blue-900 mb-2">Upload Guidance</p>
            <ul class="text-xs text-blue-800 space-y-1 mb-4">
                <li>✓ Excel file up to 20MB</li>
                <li>✓ PowerPoint file up to 40MB</li>
                <li>✓ Use sample template columns exactly for smooth import</li>
                <li>✓ For DOOH, include additional pricing fields</li>
            </ul>
            <div class="flex flex-wrap gap-2">
            <a href="{{ route('vendor.import.sample-template', ['mediaType' => 'ooh', 'format' => 'xlsx']) }}"
                class="text-xs px-3 py-2 bg-white border border-blue-200 text-blue-800 rounded-lg font-medium hover:bg-blue-100 transition-colors flex items-center gap-1.5">
                    
                    Download OOH Sample (Excel)
                </a>
                <a href="{{ route('vendor.import.sample-template', ['mediaType' => 'dooh', 'format' => 'xlsx']) }}"
                class="text-xs px-3 py-2 bg-white border border-blue-200 text-blue-800 rounded-lg font-medium hover:bg-blue-100 transition-colors flex items-center gap-1.5">
                    Download DOOH Sample (Excel)
                </a>
            </div>
        </div>
    </div>

    <!-- ══════════════════════════════════════════
        BATCH LIST (hidden — shown via openImportManagement)
    ══════════════════════════════════════════ -->
    <div class="bg-white rounded-xl shadow-lg overflow-hidden hidden mt-8">
        <div class="px-4 py-5 sm:px-6 sm:py-6 border-b border-gray-200">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <h2 class="text-2xl font-bold text-gray-900">Import History</h2>
                <div class="w-full sm:w-auto flex items-center">
                    <input type="text" id="searchInput" placeholder="Search batches..."
                        class="w-full sm:w-72 min-h-[44px] px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                </div>
            </div>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px]">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">SN</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Hoarding Type</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Total</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Valid</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Invalid</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Status</th>
                        <th class="px-6 py-4 text-left text-sm font-semibold text-gray-900">Date</th>
                        <th class="px-6 py-4 text-center text-sm font-semibold text-gray-900">Actions</th>
                    </tr>
                </thead>
                <tbody id="batchesTableBody" class="divide-y divide-gray-200">
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-lg font-medium">No uploads yet</p>
                                <p class="text-sm">Upload a file to get started</p>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-4 sm:px-6 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <p id="historyPageInfo" class="text-sm text-gray-600">Showing 0-0 of 0</p>
            <div class="w-full sm:w-auto flex items-center justify-between sm:justify-start space-x-2">
                <button id="historyPrevBtn" class="min-h-[44px] px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Previous</button>
                <span id="historyPageLabel" class="text-sm text-gray-600">Page 1 / 1</span>
                <button id="historyNextBtn" class="min-h-[44px] px-3 py-1.5 bg-gray-100 rounded-lg text-sm hover:bg-gray-200 disabled:opacity-50 disabled:cursor-not-allowed">Next</button>
            </div>
        </div>
    </div>

    <!-- Approve Modal (unchanged) -->
    <div id="approveModal" class="fixed inset-0  bg-opacity-50 z-40 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full">
            <div class="p-6 border-b border-gray-200">
                <h3 class="text-lg font-bold text-gray-900">Confirm Approval</h3>
            </div>
            <div class="p-6">
                <p class="text-gray-600 mb-4">Are you sure you want to approve this inventory? This will create all hoardings from valid records.</p>
                <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
                    <p class="text-sm text-blue-800"><strong>Invendtory ID:</strong> <span id="approveBatchId" class="font-mono">N/A</span></p>
                    <p class="text-sm text-blue-800 mt-2"><strong>Valid Records:</strong> <span id="approveBatchValid">0</span></p>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-3">
                <button onclick="closeApproveModal()" class="w-full sm:w-auto min-h-[44px] px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Cancel</button>
                <button onclick="confirmApprove()" id="confirmApproveBtn" class="w-full sm:w-auto min-h-[44px] px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg font-medium disabled:opacity-50">Approve</button>
            </div>
        </div>
    </div>

    <!-- Error Details Modal (unchanged) -->
    <div id="errorModal" class="fixed inset-0  bg-opacity-50 z-40 hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-2xl w-full flex flex-col max-h-screen">
            <div class="p-6 border-b border-gray-200">
                <div class="flex items-center justify-between">
                    <h3 class="text-lg font-bold text-gray-900">Error Details</h3>
                    <button onclick="closeErrorModal()" class="text-gray-400 hover:text-gray-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </div>
            <div class="flex-1 overflow-y-auto p-6">
                <div id="errorTableContainer" class="overflow-x-auto"></div>
            </div>
            <div class="px-6 py-4 border-t border-gray-200 flex justify-end">
                <button onclick="closeErrorModal()" class="px-4 py-2 text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg font-medium">Close</button>
            </div>
        </div>
    </div>


</div>

{{-- ══════════════════════════════════════════
     JAVASCRIPT — only UI wiring changed.
     All API calls, lock logic, polling,
     batch rendering are 100% unchanged.
══════════════════════════════════════════ --}}
<script>
    // ── UI: show/hide zones when hoarding type is selected ──────────
    function onHoardingTypeChange(value) {
        const heading    = document.getElementById('uploadHeading');
        const headingWrap= document.getElementById('uploadHeadingWrap');
        const zonesWrap  = document.getElementById('uploadZonesWrap');
        const submitWrap = document.getElementById('submitWrap');

        const label = value === 'ooh' ? 'Upload OOH Inventory' : 'Upload DOOH Inventory';
        heading.textContent = label;

        headingWrap.classList.remove('hidden');
        zonesWrap.classList.remove('hidden');
        submitWrap.classList.remove('hidden');
    }

    // ── Everything below is IDENTICAL to original — no logic changes ──

    const API_BASE = '/api/import';
    const DETAILS_BASE = @json(route('vendor.import.enhanced'));
    const IMPORT_MANAGEMENT_URL = @json(route('vendor.import.enhanced'));
    const UPLOAD_LOCK_KEY = 'import_upload_lock_state_v1';
    const UPLOAD_LOCK_DURATION_SECONDS = 300;
    const historyState = { page: 1, per_page: 10, search: '' };
    const historyPagination = { total: 0, current_page: 1, last_page: 1, from: 0, to: 0 };
    let uploadTimerInterval = null;
    let uploadStatusPollInterval = null;

    function createHttpClient() {
        if (window.axios) return window.axios;
        const request = async (url, options = {}) => {
            const response = await fetch(url, { credentials: 'same-origin', ...options });
            const text = await response.text();
            let data = {};
            try { data = text ? JSON.parse(text) : {}; } catch(e) { data = { message: text || 'Request failed' }; }
            if (!response.ok) {
                const error = new Error(data?.message || `HTTP ${response.status}`);
                error.response = { status: response.status, data };
                throw error;
            }
            return { data, status: response.status };
        };
        return {
            defaults: { headers: { common: {} } },
            get(url, config = {}) { return request(url, { method: 'GET', headers: config.headers || {} }); },
            post(url, body = {}, config = {}) {
                const headers = { ...(config.headers || {}) };
                let payload = body;
                if (!(body instanceof FormData)) {
                    headers['Content-Type'] = headers['Content-Type'] || 'application/json';
                    payload = JSON.stringify(body);
                }
                return request(url, { method: 'POST', headers, body: payload });
            },
        };
    }

    const http = createHttpClient();
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    http.defaults.headers.common['X-CSRF-TOKEN'] = csrfToken;
    http.defaults.headers.common['Accept'] = 'application/json';

    // document.addEventListener('DOMContentLoaded', () => {
    //     setupFileInputs();
    //     setupFormSubmit();
    //     setupUploadLockTimerUi();
    //     restoreUploadLockState();
    //     loadBatches();
    //     setupHistoryPagination();
    //     setupHistorySearch();
    //     setInterval(() => loadBatches(historyState.page), 15000);
    // });
        document.addEventListener('DOMContentLoaded', () => {
            // ── Delay ensures browser form-restore runs first, then we override it ──
            setTimeout(() => {
                document.querySelectorAll('input[name="media_type"]').forEach(r => r.checked = false);
                document.getElementById('uploadHeadingWrap')?.classList.add('hidden');
                document.getElementById('uploadZonesWrap')?.classList.add('hidden');
                document.getElementById('submitWrap')?.classList.add('hidden');
            }, 0);

            setupFileInputs();
            setupFormSubmit();
            setupUploadLockTimerUi();
            restoreUploadLockState();
            checkInterruptedUpload();
            loadBatches();
            setupHistoryPagination();
            setupHistorySearch();
            setInterval(() => loadBatches(historyState.page), 15000);
        });
    function setupHistoryPagination() {
        document.getElementById('historyPrevBtn')?.addEventListener('click', () => {
            if (historyPagination.current_page > 1) loadBatches(historyPagination.current_page - 1);
        });
        document.getElementById('historyNextBtn')?.addEventListener('click', () => {
            if (historyPagination.current_page < historyPagination.last_page) loadBatches(historyPagination.current_page + 1);
        });
    }

    function setupHistorySearch() {
        const searchInput = document.getElementById('searchInput');
        if (!searchInput) return;
        let timer;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(timer);
            timer = setTimeout(() => { historyState.search = (e.target.value || '').trim(); loadBatches(1); }, 350);
        });
    }

    function setupFileInputs() {
        const excelInput = document.getElementById('excelFile');
        const pptInput   = document.getElementById('pptFile');

        function updateAllFileDisplays() {
            const excelName = excelInput.files[0]?.name || '';
            const pptName = pptInput.files[0]?.name || '';
            updateFileDisplay('excelFileName', 'excelFileNameText', excelName);
            updateFileDisplay('pptFileName', 'pptFileNameText', pptName);
        }

        excelInput.addEventListener('change', updateAllFileDisplays);
        pptInput.addEventListener('change', updateAllFileDisplays);

        setupDragDrop(excelInput, 'excelDropZone');
        setupDragDrop(pptInput,   'pptDropZone');
    }

    function setupDragDrop(input, zoneId) {
        const zone = document.getElementById(zoneId);
        if (!zone) return;
        ['dragenter','dragover','dragleave','drop'].forEach(ev => zone.addEventListener(ev, e => { e.preventDefault(); e.stopPropagation(); }));
        ['dragenter','dragover'].forEach(ev => zone.addEventListener(ev, () => zone.classList.add('bg-blue-50','border-blue-400')));
        ['dragleave','drop'].forEach(ev => zone.addEventListener(ev, () => zone.classList.remove('bg-blue-50','border-blue-400')));
        zone.addEventListener('drop', (e) => {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        });
    }

    function updateFileDisplay(wrapperId, textId, fileName) {
        const wrapper = document.getElementById(wrapperId);
        const text    = document.getElementById(textId);
        if (fileName) {
            if (text) text.textContent = fileName;
            wrapper.classList.remove('hidden');
        } else {
            wrapper.classList.add('hidden');
        }
    }

    function setupFormSubmit() {
        document.getElementById('uploadForm').addEventListener('submit', submitUpload);
    }

    function getUploadLockState() {
        try { const raw = localStorage.getItem(UPLOAD_LOCK_KEY); return raw ? JSON.parse(raw) : null; }
        catch(e) { return null; }
    }
    function setUploadLockState(state)  { localStorage.setItem(UPLOAD_LOCK_KEY, JSON.stringify(state)); }
    function clearUploadLockState()     { localStorage.removeItem(UPLOAD_LOCK_KEY); }
    function formatRemaining(seconds) {
        const safe = Math.max(0, Number(seconds || 0));
        return `${String(Math.floor(safe / 60)).padStart(2,'0')}:${String(safe % 60).padStart(2,'0')}`;
    }

    function setUploadFormLocked(locked) {
        const uploadForm = document.getElementById('uploadForm');
        if (!uploadForm) return;
        uploadForm.querySelectorAll('input, button').forEach(el => {
            if (el.id === 'minimizeUploadTimer') return;
            el.disabled = locked;
        });
        const submitBtn  = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        if (submitBtn && submitText) {
            submitBtn.disabled   = locked;
            submitText.textContent = locked ? 'Please wait...' : 'Upload Files';
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
        const widget    = document.getElementById('uploadTimerWidget');
        const expanded  = document.getElementById('uploadTimerExpanded');
        const minimized = document.getElementById('uploadTimerMinimized');
        const countdown = document.getElementById('uploadTimerCountdown');
        const message   = document.getElementById('uploadTimerMessage');
        const batchLabel= document.getElementById('uploadTimerBatch');
        if (!widget || !expanded || !minimized || !countdown || !message || !batchLabel) return;

        if (!lockState) {
            widget.classList.add('hidden');
            expanded.classList.remove('hidden');
            minimized.classList.add('hidden');
            return;
        }
        widget.classList.remove('hidden');
        const remaining = Math.max(0, Math.floor((Number(lockState.expiresAt || 0) - Date.now()) / 1000));
        const timeText  = formatRemaining(remaining);
        countdown.textContent  = timeText;
        minimized.textContent  = `Processing ${timeText}`;
        batchLabel.textContent = `Inventory #${lockState.batchId || '-'} is processing`;

        const statusText = (lockState.status || 'processing').toLowerCase();
        if      (statusText === 'failed')    message.textContent = 'Processing failed. Please re-upload after checking content.';
        else if (statusText === 'completed') message.textContent = 'Processing completed successfully.';
        else                                 message.textContent = 'System is mapping inventory. You can continue browsing other screens.';

        if (lockState.minimized) { expanded.classList.add('hidden');    minimized.classList.remove('hidden'); }
        else                     { expanded.classList.remove('hidden'); minimized.classList.add('hidden'); }
    }

    function stopUploadLockIntervals() {
        if (uploadTimerInterval)      { clearInterval(uploadTimerInterval);      uploadTimerInterval      = null; }
        if (uploadStatusPollInterval) { clearInterval(uploadStatusPollInterval); uploadStatusPollInterval = null; }
    }

    function releaseUploadLock(finalStatus, withMessage = true) {
        stopUploadLockIntervals();
        const lock = getUploadLockState();
        setUploadFormLocked(false);
        clearUploadLockState();
        renderUploadLock(null);
        if (!withMessage) return;
        if (finalStatus === 'completed') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'bg-green-500 text-white px-4 py-3 sm:px-6 sm:py-4 rounded-lg shadow-lg flex flex-col gap-2 w-full sm:w-auto max-w-md break-words';
            toast.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="font-bold text-lg leading-tight">✓</span>
                    <div>
                        <p class="font-semibold text-sm leading-snug">Import completed successfully.</p>
                        <p class="text-xs mt-0.5 text-green-100">You can Review, verify and send for admin approval.</p>
                    </div>
                </div>
                <a href="${DETAILS_BASE}"
                class="inline-block text-center bg-white text-green-700 font-semibold text-xs px-4 py-2 rounded-lg hover:bg-green-50 transition-colors mt-1">
                    Import Preview
                </a>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toast.remove(), 500);
            }, 8000);
        }
       else if (finalStatus === 'failed') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'bg-red-500 text-white px-4 py-3 sm:px-6 sm:py-4 rounded-lg shadow-lg flex flex-col gap-2 w-full sm:w-auto max-w-md break-words';
            toast.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="font-bold text-lg leading-tight">✕</span>
                    <div>
                        <p class="font-semibold text-sm leading-snug">Import Failed</p>
                        <p class="text-xs mt-0.5 text-red-100">Something went wrong while processing your files. Please check your Excel &amp; PowerPoint content and try uploading again.</p>
                    </div>
                </div>
                <div class="bg-red-600 rounded-lg px-3 py-2 text-xs text-red-100 leading-relaxed">
                    💡 <strong class="text-white">Tips:</strong> Ensure your file follows the sample template format, all required columns are present, and file size is within limits.
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toast.remove(), 500);
            }, 10000);
        }
        else if (finalStatus === 'timeout') {
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'bg-orange-500 text-white px-4 py-3 sm:px-6 sm:py-4 rounded-lg shadow-lg flex flex-col gap-2 w-full sm:w-auto max-w-md break-words';
            toast.innerHTML = `
                <div class="flex items-start gap-2">
                    <span class="font-bold text-lg leading-tight">⏱</span>
                    <div>
                        <p class="font-semibold text-sm leading-snug">Processing Timed Out</p>
                        <p class="text-xs mt-0.5 text-orange-100">Your import is taking longer than expected (5 minutes). The file may still be processing in the background.</p>
                    </div>
                </div>
                <div class="bg-orange-600 rounded-lg px-3 py-2 text-xs text-orange-100 leading-relaxed">
                    💡 <strong class="text-white">What to do:</strong> Check the Import History below to see the latest status. If it still shows processing, please wait a few more minutes before re-uploading.
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toast.remove(), 500);
            }, 10000);
        }
        else if (lock?.status) {
            const statusLabel = lock.status.charAt(0).toUpperCase() + lock.status.slice(1);
            const container = document.getElementById('toastContainer');
            const toast = document.createElement('div');
            toast.className = 'bg-blue-500 text-white px-4 py-3 sm:px-6 sm:py-4 rounded-lg shadow-lg flex items-start gap-3 w-full sm:w-auto max-w-md break-words';
            toast.innerHTML = `
                <span class="font-bold text-lg leading-tight mt-0.5">ℹ</span>
                <div>
                    <p class="font-semibold text-sm leading-snug">Status Update</p>
                    <p class="text-xs mt-0.5 text-blue-100">Your import status has been updated to <strong class="text-white">${statusLabel}</strong>. You can check the Import History for more details.</p>
                </div>
            `;
            container.appendChild(toast);
            setTimeout(() => {
                toast.classList.add('opacity-0', 'transition-opacity', 'duration-500');
                setTimeout(() => toast.remove(), 500);
            }, 7000);
        }
    }

    async function pollUploadStatus() {
        const lock = getUploadLockState();
        if (!lock?.batchId) return;
        try {
            const response = await http.get(`${API_BASE}/${lock.batchId}/status`, { headers: { 'X-CSRF-TOKEN': csrfToken } });
            const status = (response?.data?.data?.status || '').toLowerCase();
            if (!status) return;
            lock.status = status;
            setUploadLockState(lock);
            renderUploadLock(lock);
            if (status === 'completed' || status === 'failed') releaseUploadLock(status, true);
        } catch(error) { console.error('Status polling failed:', error); }
    }

    function startUploadLock(batchId) {
        const now  = Date.now();
        const lock = { batchId, startedAt: now, expiresAt: now + (UPLOAD_LOCK_DURATION_SECONDS * 1000), minimized: false, status: 'processing' };
        setUploadLockState(lock);
        setUploadFormLocked(true);
        renderUploadLock(lock);
        stopUploadLockIntervals();
        uploadTimerInterval = setInterval(() => {
            const state = getUploadLockState();
            if (!state) { stopUploadLockIntervals(); return; }
            const remaining = Math.floor((Number(state.expiresAt || 0) - Date.now()) / 1000);
            if (remaining <= 0) { releaseUploadLock('timeout', true); return; }
            renderUploadLock(state);
        }, 1000);
        pollUploadStatus();
        uploadStatusPollInterval = setInterval(pollUploadStatus, 10000);
    }

    function restoreUploadLockState() {
        const lock = getUploadLockState();
        if (!lock) { setUploadFormLocked(false); renderUploadLock(null); return; }
        const remaining = Math.floor((Number(lock.expiresAt || 0) - Date.now()) / 1000);
        if (remaining <= 0) { releaseUploadLock('timeout', false); return; }
        setUploadFormLocked(true);
        renderUploadLock(lock);
        stopUploadLockIntervals();
        uploadTimerInterval = setInterval(() => {
            const state = getUploadLockState();
            if (!state) { stopUploadLockIntervals(); return; }
            const left = Math.floor((Number(state.expiresAt || 0) - Date.now()) / 1000);
            if (left <= 0) { releaseUploadLock('timeout', true); return; }
            renderUploadLock(state);
        }, 1000);
        pollUploadStatus();
        uploadStatusPollInterval = setInterval(pollUploadStatus, 3000);
    }

    // async function submitUpload(e) {
    //     e.preventDefault();
    //     const existingLock = getUploadLockState();
    //     if (existingLock) { showError('Please wait until current import completes or fails.'); return; }

    //     const excelFile        = document.getElementById('excelFile').files[0];
    //     const pptFile          = document.getElementById('pptFile').files[0];
    //     const mediaTypeElement = document.querySelector('input[name="media_type"]:checked');

    //     if (!excelFile || !pptFile) { showError('Please select both Excel and PowerPoint files'); return; }
    //     if (!mediaTypeElement) {
    //         if (window.Swal) { Swal.fire({ icon:'error', title:'Hoarding Type Required', text:'Please select a Hoarding Type before uploading.', confirmButtonText:'OK', confirmButtonColor:'#d33' }); }
    //         else showToast('Please select a Hoarding Type before uploading.', 'error');
    //         return;
    //     }

    //     if (pptFile && pptFile.size > 40 * 1024 * 1024) {
    //         showError('PowerPoint file size must be 40MB or less.');
    //         return;
    //     }

    //     const mediaType = mediaTypeElement.value;

    //     // WITH THIS:
    //     if (window.Swal) {
    //         const result = await Swal.fire({
    //             html: `
    //                 <div style="text-align:center; padding: 0 4px;">
    //                     <div style="position:absolute;top:0;left:0;right:0;background:#f3f4f6;border-radius:12px 12px 0 0;padding:12px 16px;display:flex;justify-content:flex-end;">
    //                         <button onclick="document.querySelector('.swal2-cancel').click()" style="background:none;border:none;font-size:20px;color:#374151;cursor:pointer;line-height:1;padding:0;">&#x2715;</button>
    //                     </div>
    //                     <div style="height:48px;"></div>
    //                     <h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:8px;">Inventory Files verification Required</h2>
    //                     <p style="font-size:0.875rem;color:#6b7280;margin-bottom:18px;line-height:1.5;">You are about to upload inventory files<br>for the selected hoarding type</p>
    //                     <p style="font-size:0.875rem;color:#b45309;font-weight:500;margin-bottom:18px;line-height:1.6;">Please ensure that the Excel &amp; Powerpoint file correspond to the same hoarding type selected above</p>
    //                     <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;margin-bottom:14px;text-align:left;">
    //                         <p style="margin:0 0 8px 0;font-size:0.875rem;color:#374151;"><strong style="color:#111827;">OOH</strong> <span style="color:#16a34a;"> - Out of home (Billboards, Unipoles etc)</span></p>
    //                         <p style="margin:0;font-size:0.875rem;color:#374151;"><strong style="color:#111827;">DOOH</strong> <span style="color:#16a34a;"> - Digital Out of home (LED Screens, Digital Displays etc)</span></p>
    //                     </div>
    //                     <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 18px;margin-bottom:18px;text-align:center;">
    //                         <p style="margin:0;font-size:0.875rem;color:#92400e;line-height:1.6;">The system will process and categorize the uploaded inventory based on the hoarding type you selected</p>
    //                     </div>
    //                     <p style="font-size:0.875rem;color:#374151;line-height:1.6;margin-bottom:0;"><strong>Please verify that the</strong> Excel file and Powerpoint file belong to the same hoarding type before proceeding</p>
    //                 </div>
    //             `,
    //             showCancelButton: true,
    //             confirmButtonText: 'Proceed',
    //             cancelButtonText: 'Cancel',
    //             confirmButtonColor: '#16a34a',
    //             cancelButtonColor: 'transparent',
    //             customClass: {
    //                 popup: 'swal-inventory-popup',
    //                 confirmButton: 'swal-inventory-confirm',
    //                 cancelButton: 'swal-inventory-cancel',
    //                 actions: 'swal-inventory-actions',
    //                 htmlContainer: 'swal-inventory-html',
    //             },
    //             showCloseButton: false,
    //             width: 'min(480px, 95vw)',
    //             padding: '28px 20px 24px',
    //             didOpen: () => {
    //                 // Inject scoped styles once
    //                 if (!document.getElementById('swal-inventory-styles')) {
    //                     const style = document.createElement('style');
    //                     style.id = 'swal-inventory-styles';
    //                     style.textContent = `
    //                         .swal-inventory-popup { border-radius: 18px !important; position: relative !important; }
    //                         .swal-inventory-html { padding: 0 !important; margin: 0 !important; }
    //                         .swal-inventory-actions { border-top: 1px solid #e5e7eb !important; margin-top: 20px !important; padding-top: 16px !important; width: 100% !important; display: flex !important; gap: 12px !important; justify-content: center !important; }
    //                         .swal-inventory-confirm { border-radius: 10px !important; font-size: 0.95rem !important; font-weight: 600 !important; padding: 12px 0 !important; flex: 1 !important; max-width: 220px !important; box-shadow: none !important; }
    //                         .swal-inventory-cancel { border-radius: 10px !important; font-size: 0.95rem !important; font-weight: 500 !important; padding: 12px 0 !important; flex: 1 !important; max-width: 160px !important; color: #374151 !important; box-shadow: none !important; border: none !important; background: transparent !important; }
    //                         .swal-inventory-cancel:hover { background: #f3f4f6 !important; }
    //                     `;
    //                     document.head.appendChild(style);
    //                 }
    //             }
    //         });
    //         if (!result.isConfirmed) return;
    //     }

    //     const submitBtn  = document.getElementById('submitBtn');
    //     const submitText = document.getElementById('submitText');
    //     const submitIcon = document.getElementById('submitIcon');
    //     submitBtn.disabled     = true;
    //     submitText.textContent = 'Uploading...';
    //     submitIcon.classList.add('animate-spin');
    //     document.getElementById('errorMessages').classList.add('hidden');

    //     try {
    //         const formData = new FormData();
    //         formData.append('excel',      excelFile);
    //         formData.append('ppt',        pptFile);
    //         formData.append('media_type', mediaType);

    //         const response = await http.post(API_BASE + '/upload', formData, { headers: { 'X-CSRF-TOKEN': csrfToken } });
    //         showToast('Upload successful!', 'success');

    //         document.getElementById('uploadForm').reset();
    //         // Reset UI state after form reset
    //         document.getElementById('excelFileName').classList.add('hidden');
    //         document.getElementById('pptFileName').classList.add('hidden');
    //         document.getElementById('uploadHeadingWrap').classList.add('hidden');
    //         document.getElementById('uploadZonesWrap').classList.add('hidden');
    //         document.getElementById('submitWrap').classList.add('hidden');

    //         const uploadedBatchId = response?.data?.batch_id || response?.data?.data?.batch_id;
    //         if (uploadedBatchId) {
    //             startUploadLock(uploadedBatchId);
    //             showToast('Please wait. Processing started and upload is locked for up to 5 minutes.', 'info');
    //         }
    //         await loadBatches();
    //     } catch (error) {
    //         const errors = error.response?.data?.errors || {};
    //         if (Object.keys(errors).length > 0) displayErrors(errors);
    //         else showToast(error.response?.data?.message || 'Upload failed', 'error');
    //     } finally {
    //         submitBtn.disabled     = false;
    //         submitText.textContent = 'Upload Files';
    //         submitIcon.classList.remove('animate-spin');
    //     }
    // }
    // ── Upload guard: warn if user tries to leave during upload ──
    let _uploadInProgress = false;

    function setUploadInProgress(active) {
        _uploadInProgress = active;
        if (active) {
            window.addEventListener('beforeunload', uploadBeforeUnloadGuard);
        } else {
            window.removeEventListener('beforeunload', uploadBeforeUnloadGuard);
        }
    }
    function uploadBeforeUnloadGuard(e) {
        if (!_uploadInProgress) return;
        e.preventDefault();
        e.returnValue = 'Your file upload is in progress. If you leave now, the upload will be cancelled.';
        return e.returnValue;
    }

    // ── Save upload intent so we can detect interrupted uploads on return ──
    function saveUploadIntent(excelName, pptName, mediaType) {
        localStorage.setItem('pending_upload_intent', JSON.stringify({
            excelName,
            pptName,
            mediaType,
            startedAt: Date.now(),
            status: 'uploading'
        }));
    }

    // ── Update the progress bar UI ──
    function updateUploadProgress(percent, label) {
        const bar  = document.getElementById('uploadProgressBar');
        const pct  = document.getElementById('uploadProgressPercent');
        const lbl  = document.getElementById('uploadProgressLabel');
        const wrap = document.getElementById('uploadProgressWrap');
        if (!wrap) return;
        wrap.classList.remove('hidden');
        if (bar) bar.style.width = percent + '%';
        if (pct) pct.textContent = percent + '%';
        if (lbl) lbl.textContent = label || ('Uploading... ' + percent + '%');
    }

    function hideUploadProgress() {
        const wrap = document.getElementById('uploadProgressWrap');
        if (wrap) wrap.classList.add('hidden');
        const bar = document.getElementById('uploadProgressBar');
        if (bar) bar.style.width = '0%';
    }

    // ── Check if the user left during an upload last time ──
    function checkInterruptedUpload() {
        try {
            const raw = localStorage.getItem('pending_upload_intent');
            if (!raw) return;

            const intent = JSON.parse(raw);
            if (!intent) return;

            const ageMinutes = (Date.now() - (intent.startedAt || 0)) / 60000;

            if (intent.status === 'uploading' && ageMinutes < 15) {
                showInterruptedPopup(intent.excelName);
            }

            localStorage.removeItem('pending_upload_intent');
        } catch(e) { /* ignore */ }
    }
    async function submitUpload(e) {
        e.preventDefault();

        const existingLock = getUploadLockState();
        if (existingLock) { showError('Please wait until current import completes or fails.'); return; }

        const excelFile        = document.getElementById('excelFile').files[0];
        const pptFile          = document.getElementById('pptFile').files[0];
        const mediaTypeElement = document.querySelector('input[name="media_type"]:checked');

        if (!excelFile || !pptFile) { showError('Please select both Excel and PowerPoint files'); return; }
        if (!mediaTypeElement) {
            if (window.Swal) { Swal.fire({ icon:'error', title:'Hoarding Type Required', text:'Please select a Hoarding Type before uploading.', confirmButtonText:'OK', confirmButtonColor:'#d33' }); }
            else showToast('Please select a Hoarding Type before uploading.', 'error');
            return;
        }

        if (pptFile && pptFile.size > 40 * 1024 * 1024) {
            showError('PowerPoint file size must be 40MB or less.');
            return;
        }

        const mediaType = mediaTypeElement.value;

        if (window.Swal) {
            const result = await Swal.fire({
                html: `
                    <div style="text-align:center; padding: 0 4px;">
                        <div style="position:absolute;top:0;left:0;right:0;background:#f3f4f6;border-radius:12px 12px 0 0;padding:12px 16px;display:flex;justify-content:flex-end;">
                            <button onclick="document.querySelector('.swal2-cancel').click()" style="background:none;border:none;font-size:20px;color:#374151;cursor:pointer;line-height:1;padding:0;">&#x2715;</button>
                        </div>
                        <div style="height:48px;"></div>
                        <h2 style="font-size:1.25rem;font-weight:700;color:#111827;margin-bottom:8px;">Inventory Files verification Required</h2>
                        <p style="font-size:0.875rem;color:#6b7280;margin-bottom:18px;line-height:1.5;">You are about to upload inventory files<br>for the selected hoarding type</p>
                        <p style="font-size:0.875rem;color:#b45309;font-weight:500;margin-bottom:18px;line-height:1.6;">Please ensure that the Excel &amp; Powerpoint file correspond to the same hoarding type selected above</p>
                        <div style="background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;padding:14px 18px;margin-bottom:14px;text-align:left;">
                            <p style="margin:0 0 8px 0;font-size:0.875rem;color:#374151;"><strong style="color:#111827;">OOH</strong> <span style="color:#16a34a;"> - Out of home (Billboards, Unipoles etc)</span></p>
                            <p style="margin:0;font-size:0.875rem;color:#374151;"><strong style="color:#111827;">DOOH</strong> <span style="color:#16a34a;"> - Digital Out of home (LED Screens, Digital Displays etc)</span></p>
                        </div>
                        <div style="background:#fff7ed;border:1px solid #fed7aa;border-radius:10px;padding:14px 18px;margin-bottom:18px;text-align:center;">
                            <p style="margin:0;font-size:0.875rem;color:#92400e;line-height:1.6;">The system will process and categorize the uploaded inventory based on the hoarding type you selected</p>
                        </div>
                        <p style="font-size:0.875rem;color:#374151;line-height:1.6;margin-bottom:0;"><strong>Please verify that the</strong> Excel file and Powerpoint file belong to the same hoarding type before proceeding</p>
                    </div>
                `,
                showCancelButton: true,
                confirmButtonText: 'Proceed',
                cancelButtonText: 'Cancel',
                confirmButtonColor: '#16a34a',
                cancelButtonColor: 'transparent',
                customClass: {
                    popup: 'swal-inventory-popup',
                    confirmButton: 'swal-inventory-confirm',
                    cancelButton: 'swal-inventory-cancel',
                    actions: 'swal-inventory-actions',
                    htmlContainer: 'swal-inventory-html',
                },
                showCloseButton: false,
                width: 'min(480px, 95vw)',
                padding: '28px 20px 24px',
                didOpen: () => {
                    if (!document.getElementById('swal-inventory-styles')) {
                        const style = document.createElement('style');
                        style.id = 'swal-inventory-styles';
                        style.textContent = `
                            .swal-inventory-popup { border-radius: 18px !important; position: relative !important; }
                            .swal-inventory-html { padding: 0 !important; margin: 0 !important; }
                            .swal-inventory-actions { border-top: 1px solid #e5e7eb !important; margin-top: 20px !important; padding-top: 16px !important; width: 100% !important; display: flex !important; gap: 12px !important; justify-content: center !important; }
                            .swal-inventory-confirm { border-radius: 10px !important; font-size: 0.95rem !important; font-weight: 600 !important; padding: 12px 0 !important; flex: 1 !important; max-width: 220px !important; box-shadow: none !important; }
                            .swal-inventory-cancel { border-radius: 10px !important; font-size: 0.95rem !important; font-weight: 500 !important; padding: 12px 0 !important; flex: 1 !important; max-width: 160px !important; color: #374151 !important; box-shadow: none !important; border: none !important; background: transparent !important; }
                            .swal-inventory-cancel:hover { background: #f3f4f6 !important; }
                        `;
                        document.head.appendChild(style);
                    }
                }
            });
            if (!result.isConfirmed) return;
            document.querySelectorAll('input[name="media_type"]').forEach(r => {
                r.disabled = true;
                r.closest('label')?.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
            });
        }

        const submitBtn  = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const submitIcon = document.getElementById('submitIcon');
        submitBtn.disabled     = true;
        submitIcon.classList.add('animate-spin');
        document.getElementById('errorMessages').classList.add('hidden');

        // ── Save intent + activate navigation guard ──
        saveUploadIntent(excelFile.name, pptFile.name, mediaType);
        setUploadInProgress(true);
        updateUploadProgress(0, 'Connecting to server...');

        const formData = new FormData();
        formData.append('excel',      excelFile);
        formData.append('ppt',        pptFile);
        formData.append('media_type', mediaType);
        formData.append('_token',     csrfToken);

        // ── Use XHR so we get upload progress events ──
        try {
            const responseData = await new Promise((resolve, reject) => {
                const xhr = new XMLHttpRequest();
                window._activeUploadXHR = xhr;

                xhr.open('POST', API_BASE + '/upload', true);
                xhr.setRequestHeader('Accept', 'application/json');
                xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

                xhr.upload.addEventListener('progress', (event) => {
                    if (event.lengthComputable) {
                        const percent = Math.round((event.loaded / event.total) * 100);
                        updateUploadProgress(percent, percent < 100 ? 'Uploading files... ' + percent + '%' : 'Processing on server...');
                        submitText.textContent = 'Uploading ' + percent + '%';
                    }
                });

                xhr.onload = function() {
                    window._activeUploadXHR = null;
                    let data = {};
                    try { data = JSON.parse(xhr.responseText); } catch(e) { data = { message: xhr.responseText || 'Request failed' }; }
                    if (xhr.status >= 200 && xhr.status < 300) {
                        resolve(data);
                    } else {
                        reject({ response: { status: xhr.status, data } });
                    }
                };

                xhr.onerror = function() {
                    window._activeUploadXHR = null;
                    reject({ response: { status: 0, data: { message: 'Network error. Please check your connection.' } } });
                };

                xhr.onabort = function() {
                    window._activeUploadXHR = null;
                    reject({ response: { status: 0, data: { message: 'Upload was cancelled.' } } });
                };

                xhr.send(formData);
            });

            // ── Success ──
            localStorage.removeItem('pending_upload_intent');
            showToast('Upload successful!', 'success');

            document.getElementById('uploadForm').reset();
            document.getElementById('excelFileName').classList.add('hidden');
            document.getElementById('pptFileName').classList.add('hidden');
            document.getElementById('uploadHeadingWrap').classList.add('hidden');
            document.getElementById('uploadZonesWrap').classList.add('hidden');
            document.getElementById('submitWrap').classList.add('hidden');
            hideUploadProgress();

            const uploadedBatchId = responseData?.batch_id || responseData?.data?.batch_id;
            if (uploadedBatchId) {
                startUploadLock(uploadedBatchId);
                showToast('Please wait. Processing started and upload is locked for up to 5 minutes.', 'info');
            }
            await loadBatches();

        } catch (error) {
            localStorage.removeItem('pending_upload_intent');
            hideUploadProgress();
            const errors = error.response?.data?.errors || {};
            if (Object.keys(errors).length > 0) displayErrors(errors);
            else showToast(error.response?.data?.message || 'Upload failed', 'error');
        } finally {
            setUploadInProgress(false);
            submitBtn.disabled     = false;
            submitText.textContent = 'Upload Files';
            submitIcon.classList.remove('animate-spin');
             // ── Re-enable radio buttons only on failure ──
            if (!getUploadLockState()) {
                document.querySelectorAll('input[name="media_type"]').forEach(r => {
                    r.disabled = false;
                    r.closest('label')?.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                });
            }
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

    async function loadBatches(page = historyState.page) {
        try {
            historyState.page = Math.max(1, Number(page || 1));
            const params = new URLSearchParams({ page: String(historyState.page), per_page: String(historyState.per_page) });
            if (historyState.search) params.append('search', historyState.search);

            const response = await http.get(`${API_BASE}?${params.toString()}`, { headers: { 'X-CSRF-TOKEN': csrfToken } });
            const batches    = response.data.data || [];
            const pagination = response.data.pagination || {};
            historyPagination.total        = pagination.total        || 0;
            historyPagination.current_page = pagination.current_page || 1;
            historyPagination.last_page    = pagination.last_page    || 1;
            historyPagination.from         = pagination.from         || 0;
            historyPagination.to           = pagination.to           || 0;

            updateStats(batches, response.data.summary || null);

            const tableContainer = document.getElementById('batchesTableBody');
            if (tableContainer && !tableContainer.closest('.hidden')) {
                renderBatches(batches);
                renderHistoryPagination();
            }
        } catch(error) { console.error('Failed to load batches:', error); }
    }

    function renderHistoryPagination() {
        const info    = document.getElementById('historyPageInfo');
        const label   = document.getElementById('historyPageLabel');
        const prevBtn = document.getElementById('historyPrevBtn');
        const nextBtn = document.getElementById('historyNextBtn');
        if (info)    info.textContent   = `Showing ${historyPagination.from}-${historyPagination.to} of ${historyPagination.total}`;
        if (label)   label.textContent  = `Page ${historyPagination.current_page} / ${historyPagination.last_page}`;
        if (prevBtn) prevBtn.disabled   = historyPagination.current_page <= 1;
        if (nextBtn) nextBtn.disabled   = historyPagination.current_page >= historyPagination.last_page;
    }

    function renderBatches(batches) {
        const tbody = document.getElementById('batchesTableBody');
        if (batches.length === 0) {
            tbody.innerHTML = `<tr><td colspan="8" class="px-6 py-12 text-center text-gray-500"><div class="flex flex-col items-center"><svg class="w-12 h-12 text-gray-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><p class="text-lg font-medium">No batches yet</p><p class="text-sm">Upload a file to get started</p></div></td></tr>`;
            return;
        }
        tbody.innerHTML = batches.map((batch, idx) => `
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-6 py-4 text-sm font-mono text-blue-600">${idx + 1}</td>
                <td class="px-6 py-4 text-sm"><span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${batch.media_type === 'ooh' ? 'bg-purple-100 text-purple-800' : 'bg-indigo-100 text-indigo-800'}">${batch.media_type?.toUpperCase() || 'DOOH'}</span></td>
                <td class="px-6 py-4 text-sm text-gray-600">${batch.total_rows}</td>
                <td class="px-6 py-4 text-sm"><span class="text-green-600 font-medium">${batch.valid_rows}</span></td>
                <td class="px-6 py-4 text-sm"><span class="text-red-600 font-medium">${batch.invalid_rows}</span></td>
                <td class="px-6 py-4 text-sm">${getStatusBadge(batch.status)}</td>
                <td class="px-6 py-4 text-sm text-gray-600">${formatDate(batch.created_at)}</td>
                <td class="px-6 py-4 text-sm"><button onclick="loadBatchDetails(${batch.batch_id})" class="px-3 py-1 bg-blue-100 hover:bg-blue-200 text-blue-700 rounded-lg transition-colors text-xs font-medium cursor-pointer">View</button></td>
            </tr>
        `).join('');
    }

    function updateStats(batches, summary = null) {
        const total      = summary?.total      ?? (historyPagination.total || batches.length);
        const processing = summary?.processing ?? batches.filter(b => b.status === 'processing').length;
        const completed  = summary?.completed  ?? batches.filter(b => b.status === 'completed').length;
        const failed     = summary?.failed     ?? batches.filter(b => b.status === 'failed').length;
        const approved     = summary?.approved     ?? batches.filter(b => b.status === 'approved').length;
        document.getElementById('totalBatches').textContent      = total;
        document.getElementById('processingBatches').textContent = processing;
        document.getElementById('completedBatches').textContent  = completed;
        document.getElementById('failedBatches').textContent     = failed;
        document.getElementById('approvedBatches').textContent     = approved;

    }

    function getStatusBadge(status) {
        const c = { uploaded:{bg:'bg-gray-100',text:'text-gray-800',icon:'📤'}, processing:{bg:'bg-yellow-100',text:'text-yellow-800',icon:'⏳'}, processed:{bg:'bg-blue-100',text:'text-blue-800',icon:'✓'}, completed:{bg:'bg-green-100',text:'text-green-800',icon:'✓✓'}, cancelled:{bg:'bg-gray-100',text:'text-gray-800',icon:'✕'}, failed:{bg:'bg-red-100',text:'text-red-800',icon:'✕'} };
        const cfg = c[status] || c['uploaded'];
        return `<span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium ${cfg.bg} ${cfg.text}">${cfg.icon} ${status.charAt(0).toUpperCase()+status.slice(1)}</span>`;
    }

    function formatDate(dateString) {
        if (!dateString) return 'N/A';
        return new Intl.DateTimeFormat('en-US', { year:'numeric', month:'short', day:'numeric', hour:'2-digit', minute:'2-digit' }).format(new Date(dateString));
    }

    function openApproveModal(batchId, validRows) {
        document.getElementById('approveBatchId').textContent   = `#${batchId}`;
        document.getElementById('approveBatchValid').textContent = validRows;
        document.getElementById('approveModal').classList.remove('hidden');
        document.getElementById('approveModal').dataset.batchId = batchId;
    }
    function closeApproveModal() { document.getElementById('approveModal').classList.add('hidden'); }

    async function confirmApprove() {
        const batchId = document.getElementById('approveModal').dataset.batchId;
        const btn     = document.getElementById('confirmApproveBtn');
        btn.disabled  = true;
        try {
            await http.post(`${API_BASE}/${batchId}/approve`, {}, { headers: { 'X-CSRF-TOKEN': csrfToken } });
            showToast('Inventory approved successfully!', 'success');
            closeApproveModal();
            await loadBatches();
        } catch(error) { showToast(error.response?.data?.message || 'Approval failed', 'error'); }
        finally { btn.disabled = false; }
    }

    async function openErrorModal(batchId) {
        try {
            const response = await http.get(`${API_BASE}/${batchId}/details`, { headers: { 'Authorization': `Bearer ${getAuthToken()}` } });
            renderErrorTable(response.data.data?.invalid_records || []);
            document.getElementById('errorModal').classList.remove('hidden');
        } catch(error) { showToast('Failed to load error details', 'error'); }
    }

    function renderErrorTable(records) {
        const container = document.getElementById('errorTableContainer');
        if (records.length === 0) { container.innerHTML = '<p class="text-gray-600">No errors found</p>'; return; }
        container.innerHTML = `<table class="w-full text-sm"><thead><tr class="bg-gray-50 border-b"><th class="px-4 py-3 text-left font-semibold text-gray-900">Row</th><th class="px-4 py-3 text-left font-semibold text-gray-900">Code</th><th class="px-4 py-3 text-left font-semibold text-gray-900">Error Message</th></tr></thead><tbody class="divide-y">${records.map((r,i) => `<tr class="hover:bg-gray-50"><td class="px-4 py-3 text-gray-600">${i+1}</td><td class="px-4 py-3 font-mono text-gray-700">${r.code||'N/A'}</td><td class="px-4 py-3 text-red-600">${r.error_message||'Unknown error'}</td></tr>`).join('')}</tbody></table>`;
    }

    function closeErrorModal() { document.getElementById('errorModal').classList.add('hidden'); }

    function showToast(message, type = 'info') {
        const container = document.getElementById('toastContainer');
        const toast = document.createElement('div');
        const cfg = { success:{bg:'bg-green-500',icon:'✓'}, error:{bg:'bg-red-500',icon:'✕'}, info:{bg:'bg-blue-500',icon:'ℹ'} };
        const c = cfg[type] || cfg['info'];
        toast.className = `${c.bg} text-white px-4 py-3 sm:px-6 sm:py-4 rounded-lg shadow-lg flex items-center space-x-3 animate-pulse w-full sm:w-auto max-w-md break-words`;
        toast.innerHTML = `<span class="font-bold">${c.icon}</span><span>${message}</span>`;
        container.appendChild(toast);
        setTimeout(() => { toast.classList.add('opacity-0','transition-opacity','duration-500'); setTimeout(() => toast.remove(), 500); }, 4000);
    }

    function getAuthToken() {
        return document.querySelector('[data-auth-token]')?.dataset.authToken ||
               document.querySelector('meta[name="auth-token"]')?.content || 'test-token';
    }

    function refreshBatches() { loadBatches(); showToast('Inventories refreshed', 'info'); }

    function filterByStatus(status) { openImportManagement(status); }

    function openImportManagement(status = '') {
        const url = new URL(IMPORT_MANAGEMENT_URL, window.location.origin);
        url.searchParams.set('tab', 'batches');
        if (status) url.searchParams.set('status', status);
        window.location.href = url.toString();
    }

    async function loadBatchDetails(batchId) {
        window.location.href = `${DETAILS_BASE}/batches/${batchId}`;
    }

    
    
</script>

<style>
    @keyframes spin    { to { transform: rotate(360deg); } }
    @keyframes pulse   { 0%,100% { opacity:1; } 50% { opacity:.8; } }
    .animate-spin      { animation: spin  1s linear infinite; }
    .animate-pulse     { animation: pulse 2s cubic-bezier(.4,0,.6,1) infinite; }

    @keyframes fadeIn {
        from { opacity: 0; transform: translateY(10px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }
    .animate-fadeIn {
        animation: fadeIn 0.25s ease-out;
    }
</style>

<script>
    function showInterruptedPopup(fileName) {
    const existing = document.getElementById('interruptModal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'interruptModal';
    modal.className = 'fixed inset-0 z-[9999] flex items-center justify-center bg-black/40 backdrop-blur-sm';

    modal.innerHTML = `
        <div class="bg-white rounded-2xl shadow-2xl w-[90%] max-w-md p-6 animate-fadeIn">

            <!-- Icon -->
            <div class="flex justify-center mb-4">
                <div class="w-14 h-14 flex items-center justify-center rounded-full bg-red-100 text-red-600">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z"/>
                    </svg>
                </div>
            </div>

            <!-- Title -->
            <h2 class="text-lg font-bold text-gray-900 text-center">
                Upload Cancelled
            </h2>

            <!-- Message -->
            <p class="text-sm text-gray-600 text-center mt-2 leading-relaxed">
                You left while uploading <strong>"${fileName || 'your file'}"</strong>. 
                <br/>The upload was cancelled.
            </p>

            <!-- Hint -->
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 text-xs rounded-lg p-3 mt-4 text-center">
                Please re-upload your files .
            </div>

            <!-- Actions -->
            <div class="mt-5 flex justify-center">
                <button onclick="closeInterruptedPopup()"
                    class="bg-gray-600 hover:bg-gray-700 text-white text-sm font-semibold px-6 py-2 rounded-lg transition">
                    Okay
                </button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
}

function closeInterruptedPopup() {
    const modal = document.getElementById('interruptModal');
    if (modal) modal.remove();
}
</script>
@endsection