@extends($posLayout ?? 'layouts.vendor')

@section('title', 'Create Booking')
@section('content')
<div class="sm:py-2 bg-gray-50">

    {{-- ══════════════════════════════════════════════════════════════
         SELECTION SCREEN
    ══════════════════════════════════════════════════════════════ --}}
    <div id="selection-screen" class="flex flex-col lg:flex-row gap-4 sm:gap-5 lg:gap-6">

        {{-- ══════════════════════════════════════════
             MOBILE: Customer + Hoardings stacked in natural order
             DESKTOP: Right panel (hoardings) becomes sticky sidebar
        ══════════════════════════════════════════ --}}

        {{-- ── LEFT: Booking builder (shows SECOND on mobile, FIRST on desktop) ── --}}
        <div class="order-2 lg:order-1 w-full lg:w-[56%] min-w-0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">

                {{-- Header --}}
                <div class="px-4 sm:px-6 py-4 border-b border-gray-100 bg-white">
                    <h2 class=" text-lg  md:text-xl font-bold text-gray-800">Create New POS Booking</h2>
                    <p class="text-xs text-gray-400 mt-0.5">Select a customer and choose hoardings to create a booking.</p>
                </div>

                <div class="p-4 sm:p-5 lg:p-6">

                    {{-- ── Customer Select ── --}}
                    <div class="mb-6 sm:mb-8">
                        <label class="block text-xs font-bold text-gray-500 uppercase tracking-wider">Select Customer</label>
                        <p class="block text-xs text-gray-400 tracking-wider mb-2">Search an existing customer or add a new customer to proceed with booking.</p>

                        <div id="search-container" class="flex flex-col sm:flex-row gap-2">
                            <div class="relative flex-1 border border-gray-300 rounded">
                                <input type="text" id="customer-search" autocomplete="off"
                                    placeholder="Search customer by name, email, or mobile…"
                                    class="w-full border-0 focus:ring-green-500 text-sm py-2.5 px-3 min-h-[44px] rounded">
                                <div id="customer-suggestions"
                                    class="absolute z-50 w-full bg-white border rounded-md shadow-lg mt-1 hidden max-h-60 overflow-y-auto"></div>
                            </div>
                            <button type="button" id="new-customer-btn" onclick="openCustomerModal()"
                                class="w-full sm:w-auto min-h-[44px] bg-green-600 text-white px-4 rounded hover:bg-green-700 transition flex items-center justify-center gap-1.5">
                                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                                <span class="text-sm font-semibold whitespace-nowrap">Add New Customer</span>
                            </button>
                        </div>

                        <div id="customer-selected-card"
                            class="hidden mt-3 flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-green-50 border border-green-200 rounded-lg p-3 sm:p-4 animate-fade-in">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 sm:w-10 sm:h-10 bg-[#2D5A43] rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                                    id="cust-initials">--</div>
                                <div class="min-w-0">
                                    <h4 id="cust-name" class="font-bold text-gray-800 text-sm leading-tight truncate">Customer Name</h4>
                                    <p id="cust-details" class="text-xs text-gray-500 mt-0.5 truncate">Contact Details</p>
                                </div>
                            </div>
                            <button id="change-customer-btn" onclick="clearSelectedCustomer()"
                                class="w-full sm:w-auto flex-shrink-0 text-xs font-bold text-red-500 hover:text-red-700 px-3 py-2 border border-red-200 rounded-md bg-white transition">
                                Change Customer
                            </button>
                        </div>
                    </div>

                    {{-- ── Availability Alert ── --}}
                    <div id="availability-alert" class="hidden mb-5 rounded-lg border border-red-200 bg-red-50 p-4">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
                            </svg>
                            <div class="flex-1 min-w-0">
                                <h4 class="text-sm font-bold text-red-700 mb-2">Availability Conflicts Found</h4>
                                <div id="availability-alert-body" class="text-xs text-red-600 space-y-1"></div>
                            </div>
                            <button onclick="document.getElementById('availability-alert').classList.add('hidden')"
                                class="text-red-400 hover:text-red-600 flex-shrink-0 ml-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- ── OOH Table ── --}}
                    <div class="space-y-5 sm:space-y-6">
                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-1">
                                <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 flex-shrink-0"></span> OOH (Static)
                            </h4>
                            <p class="text-xs text-gray-400 mb-2 pl-4">Select traditional billboard hoardings for long-term display.</p>
                            <div class="overflow-x-auto border border-gray-100 rounded -mx-0">
                                <table class="min-w-[600px] w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Hoarding Name</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold hidden sm:table-cell">Monthly Rental</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-center">Booking Duration</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Total Cost</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody id="ooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- ── DOOH Table ── --}}
                        <div class="selection-group">
                            <h4 class="text-xs font-bold text-gray-400 uppercase tracking-widest flex items-center mb-1">
                                <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 flex-shrink-0"></span> Digital Hoardings (DOOH)
                            </h4>
                            <p class="text-xs text-gray-400 mb-2 pl-4">Select digital screens and configure slot bookings.</p>
                            <div class="overflow-x-auto border border-gray-100 rounded -mx-0">
                                <table class="min-w-[620px] w-full divide-y divide-gray-200 text-left text-xs sm:text-sm">
                                    <thead class="bg-gray-50 text-gray-500">
                                        <tr>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Screen Location</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold hidden sm:table-cell">Slot Price</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-center hidden sm:table-cell">Slots/Day</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-center">Booking Duration</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold">Total Cost</th>
                                            <th class="px-3 sm:px-4 py-3 font-semibold text-right">Remove</th>
                                        </tr>
                                    </thead>
                                    <tbody id="dooh-selected-list" class="divide-y divide-gray-50 bg-white">
                                        <tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ── Bottom Actions ── --}}
                    <div class="flex flex-col sm:flex-row gap-3 mt-8 sm:mt-12 pt-5 sm:pt-6 border-t border-gray-100">
                        <button type="button" onclick="location.reload()"
                            class="w-full sm:flex-1 min-h-[44px] py-3 bg-[#7A9C89] border border-gray-200 font-bold text-white transition cursor-pointer rounded text-sm">
                            Cancel Booking
                        </button>
                        <button id="submit-btn"
                            class="w-full sm:flex-1 min-h-[44px] py-3 bg-[#2E5B42] text-white font-bold shadow-lg shadow-green-900/20 hover:bg-opacity-90 active:scale-[0.98] transition cursor-pointer rounded text-sm">
                            Preview &amp; Confirm (<span id="btn-count">0</span>)
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════
             RIGHT PANEL — Available Hoardings
             Shows FIRST on mobile (order-1), sidebar on desktop (order-2)
        ══════════════════════════════════════════ --}}
        <div class="order-1 lg:order-2 w-full lg:w-[44%] min-w-0">
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 lg:sticky">

                <div class="px-4 sm:px-5 pt-4 sm:pt-5 flex items-center gap-3">
                    <h3 class="font-bold text-gray-800 text-sm">Select Hoardings for Booking</h3>
                    <span class="bg-gray-100 text-gray-600 px-2.5 py-0.5 rounded-full text-xs font-bold flex-shrink-0" id="available-count">0</span>
                </div>
                <p class="px-4 sm:px-5 text-xs text-gray-400 mt-0.5 mb-0">Browse and select hoardings to add them to the booking.</p>

                <div class="p-3 sm:p-4 lg:p-5">

                    {{-- Search + Filter --}}
                    <div class="flex flex-col sm:flex-row sm:items-center gap-2 mb-3">
                        <div class="relative flex-1">
                            <input type="text" id="hoarding-search"
                                placeholder="Search by name, location or size…"
                                class="w-full pl-9 pr-3 border border-gray-300 text-xs focus:ring-green-500 rounded h-[38px]">
                            <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-gray-400 pointer-events-none">
                                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <circle cx="11" cy="11" r="8"/><path d="M21 21l-3.5-3.5"/>
                                </svg>
                            </span>
                        </div>
                        <button type="button"
                            class="w-full sm:w-auto flex-shrink-0 border border-gray-300 bg-white px-3 text-gray-700 text-xs font-medium hover:bg-gray-100 transition rounded h-[38px]"
                            onclick="openFilterModal()">
                            Advance Filters
                        </button>
                        @include('vendor.pos.filter_modal')
                    </div>

                    {{-- Grid/List toggle + Unselect all --}}
                    <div class="flex items-center justify-between gap-2 mb-2">
                        <button id="unselect-all-btn" onclick="unselectAllHoardings()"
                            class="hidden text-[10px] font-bold text-red-600 border border-red-200 bg-red-50 hover:bg-red-100 px-2.5 py-1 rounded transition whitespace-nowrap">
                            ✕ Clear All
                        </button>
                        <div class="ml-auto flex border border-gray-200 rounded overflow-hidden">
                            <button onclick="setViewMode('grid')" id="view-grid-btn"
                                class="px-2 py-1.5 bg-gray-800 text-white" title="Grid View">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                                    <path d="M1 2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V2zM1 7a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1V7zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1V7zM1 12a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H2a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1H7a1 1 0 01-1-1v-2zm5 0a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 01-1 1h-2a1 1 0 01-1-1v-2z"/>
                                </svg>
                            </button>
                            <button onclick="setViewMode('list')" id="view-list-btn"
                                class="px-2 py-1.5 bg-white text-gray-600 hover:bg-gray-100" title="List View">
                                <svg width="13" height="13" fill="currentColor" viewBox="0 0 16 16">
                                    <path fill-rule="evenodd" d="M2.5 12a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5zm0-4a.5.5 0 01.5-.5h10a.5.5 0 010 1H3a.5.5 0 01-.5-.5z"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    {{-- Active Filter Tags --}}
                    <div id="active-filter-tags" class="hidden mb-2">
                        <div class="flex flex-wrap items-center gap-1.5">
                            <span class="text-[10px] text-gray-400 font-semibold">Filters:</span>
                            <div id="filter-tags-list" class="flex flex-wrap gap-1 flex-1"></div>
                            <button onclick="clearAllFilters()"
                                class="text-[10px] text-red-500 font-semibold hover:underline whitespace-nowrap">
                                Clear all
                            </button>
                        </div>
                    </div>

                    {{-- Hoardings Grid / List --}}
                    <div id="hoardings-grid"
                        class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-2 xl:grid-cols-4 gap-2 max-h-[420px] sm:max-h-[520px] lg:max-h-[calc(100vh-320px)] overflow-y-auto pr-1 custom-scrollbar">
                    </div>

                    {{-- Pagination --}}
                    <div id="hoardings-pagination" class="flex justify-center items-center gap-1.5 mt-3 flex-wrap"></div>
                </div>
            </div>
        </div>

    </div>{{-- /selection-screen --}}

    {{-- ══════════════════════════════════════════════════════════════
         PREVIEW SCREEN
    ══════════════════════════════════════════════════════════════ --}}
    <div id="preview-screen" class="hidden animate-fade-in">
        @include('vendor.pos.preview-screen')
    </div>

</div>

@include('vendor.pos.customer-modal')

{{-- ══════════════════════════════════════════════════════════════
     DATE PICKER MODAL
══════════════════════════════════════════════════════════════ --}}
<div id="datePickerModal" class="fixed inset-0 flex items-center justify-center z-[60] hidden">
    <div class="bg-black/60 backdrop-blur-sm absolute inset-0" onclick="closeDatePickerModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-2xl p-4 sm:p-5 w-[95vw] sm:w-full sm:max-w-[760px] z-10 flex flex-col max-h-[90vh] overflow-y-auto lg:max-h-none lg:overflow-y-visible">

        {{-- Header --}}
        <div class="flex justify-between items-start mb-3 gap-3">
            <div class="min-w-0">
                <h3 id="datePickerTitle" class="font-black text-gray-800 text-sm sm:text-base truncate">Select Booking Dates</h3>
                <p class="text-[11px] text-gray-400 mt-0.5">Duration auto-rounds up to nearest 30-day multiple. Minimum 30 days.</p>
            </div>
            <button onclick="closeDatePickerModal()"
                class="flex-shrink-0 w-8 h-8 flex items-center justify-center rounded-full text-gray-400 hover:bg-gray-100 hover:text-gray-700 transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Duration / cost summary bar --}}
        <div class="grid grid-cols-3 gap-2 sm:gap-3 bg-emerald-50 border border-emerald-200 rounded-xl px-3 sm:px-4 py-3 mb-3">
            <div>
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Period</p>
                <p id="dp-range-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900 leading-tight">— Pick a date</p>
            </div>
            <div class="text-center">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Duration</p>
                <p id="dp-months-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900">—</p>
            </div>
            <div class="text-right">
                <p class="text-[9px] text-emerald-600 font-bold uppercase tracking-wider mb-0.5">Est. Cost</p>
                <p id="dp-cost-label" class="text-[10px] sm:text-[11px] font-black text-emerald-900">—</p>
            </div>
        </div>

        {{-- Legend --}}
        <div class="flex flex-wrap items-center gap-x-3 gap-y-1 px-1 mb-3">
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                <span class="w-3 h-3 rounded-sm bg-green-100 border border-green-300 flex-shrink-0"></span>Available
            </span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                <span class="w-3 h-3 rounded-sm bg-red-100 border border-red-300 flex-shrink-0"></span>Booked
            </span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                <span class="w-3 h-3 rounded-sm bg-gray-200 border border-gray-300 flex-shrink-0"></span>Blocked
            </span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                <span class="w-3 h-3 rounded-sm bg-amber-100 border border-amber-300 flex-shrink-0"></span>On Hold
            </span>
            <span class="flex items-center gap-1.5 text-[10px] font-semibold text-gray-500">
                <span class="w-3 h-3 rounded-sm bg-orange-100 border border-orange-300 flex-shrink-0"></span>Partial
            </span>
        </div>

        {{-- Flatpickr mount --}}
        <input id="date-picker-input" type="text" class="hidden">
        <div id="date-picker-inline" class="w-full overflow-x-auto calander-picker-styling"></div>

        {{-- Quick-select chips --}}
        <div class="flex flex-wrap items-center gap-2 mt-3">
            <span class="text-[10px] text-gray-400 font-semibold">Quick:</span>
            <button onclick="quickSelectMonths(1)"  data-months="1"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">1 Month</button>
            <button onclick="quickSelectMonths(2)"  data-months="2"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">2 Months</button>
            <button onclick="quickSelectMonths(3)"  data-months="3"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">3 Months</button>
            <button onclick="quickSelectMonths(6)"  data-months="6"  class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">6 Months</button>
            <button onclick="quickSelectMonths(12)" data-months="12" class="dp-quick-chip px-2.5 sm:px-3 py-1 text-[11px] font-bold border border-gray-300 rounded-full hover:border-emerald-500 hover:text-emerald-700 hover:bg-emerald-50 transition">12 Months</button>
        </div>

        {{-- Action buttons --}}
        <div class="flex flex-col-reverse sm:flex-row justify-end gap-2 sm:gap-3 mt-4 pt-4 border-t border-gray-100">
            <button onclick="closeDatePickerModal()"
                class="w-full sm:w-auto min-h-[44px] px-5 py-2 border border-gray-200 rounded-xl text-sm font-bold text-gray-600 hover:bg-gray-50 transition">
                Cancel
            </button>
            <button onclick="confirmDateSelection()"
                class="w-full sm:w-auto min-h-[44px] px-7 py-2 bg-[#2D5A43] text-white rounded-xl text-sm font-bold hover:bg-opacity-90 transition">
                ✓ Confirm Dates
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     CSS
══════════════════════════════════════════════════════════════ --}}
<style>
/* --- base --- */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 10px; }
.animate-fade-in { animation: fadeIn .35s ease-out forwards; }
@keyframes fadeIn { from { opacity:0; transform:translateY(5px); } to { opacity:1; transform:none; } }

/* --- hoarding cards --- */
.hoarding-card { transition: border-color .15s, box-shadow .15s; cursor: pointer; }
.hoarding-card:hover:not(.is-selected) { border-color:#6ee7b7; background:#f9fafb; }
.hoarding-card.is-selected { border-color:#16a34a !important; box-shadow:0 0 0 2px #bbf7d055; background:#f0fdf4 !important; }

/* --- list-view --- */
#hoardings-grid.list-view { grid-template-columns:1fr !important; }
#hoardings-grid.list-view .hoarding-card { display:flex !important; flex-direction:row !important; align-items:center; }
#hoardings-grid.list-view .hoarding-card .hc-img { width:60px; height:54px; flex-shrink:0; }
#hoardings-grid.list-view .hoarding-card .hc-body { flex:1; }

/* --- table conflict row --- */
.availability-conflict td { background:#fff5f5 !important; }
.availability-conflict td:first-child { border-left:3px solid #ef4444; }

/* --- flatpickr day colours --- */
.flatpickr-day.avail-day       { background:#dcfce7!important; border-color:#86efac!important; color:#14532d!important; }
.flatpickr-day.avail-day.flatpickr-disabled {
    background:#f3f4f6!important; border-color:#e5e7eb!important; color:#9ca3af!important; cursor:not-allowed!important;
}
.flatpickr-day.day-booked,
.flatpickr-day.day-partial  { background:#fee2e2!important; color:#991b1b!important; border-color:#fca5a5!important; cursor:not-allowed!important; text-decoration:line-through; pointer-events:none; }
.flatpickr-day.day-blocked  { background:#f3f4f6!important; color:#9ca3af!important; border-color:#e5e7eb!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.day-hold     { background:#fef9c3!important; color:#78350f!important; border-color:#fde047!important; cursor:not-allowed!important; pointer-events:none; }
.flatpickr-day.selected,
.flatpickr-day.startRange,
.flatpickr-day.endRange     { background:#2D5A43!important; border-color:#2D5A43!important; color:#fff!important; }
.flatpickr-day.inRange      { background:#e5e7eb!important; border-color:#d1d5db!important; color:#1f2937!important; box-shadow:none!important; }
.flatpickr-day.today:not(.selected):not(.startRange):not(.endRange) { border-bottom:2px solid #2D5A43!important; font-weight:700; }

/* --- quick-chip active state --- */
.dp-quick-chip.chip-active { border-color:#059669; color:#059669; background:#ecfdf5; }

/* --- duration cell in table --- */
.dur-btn { font-size:11px; font-weight:700; color:#2D5A43; text-align:left; line-height:1.5; }
.dur-btn:hover { color:#1d4ed8; }
.dur-btn .dur-sub { font-size:9px; color:#059669; font-weight:600; }

/* ── Date picker calendar sizing ── */
#date-picker-inline .flatpickr-calendar.inline {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
    box-shadow: none !important;
}
#date-picker-inline .flatpickr-months,
#date-picker-inline .flatpickr-innerContainer,
#date-picker-inline .flatpickr-rContainer {
    width: 100% !important;
    max-width: 100% !important;
    box-sizing: border-box;
}
#date-picker-inline .flatpickr-days {
    width: 100% !important;
    max-width: 100% !important;
}

/* Mobile: single month full-width */
@media (max-width: 767px) {
    #datePickerModal > .relative {
        max-height: 90vh !important;
        overflow-y: auto !important;
    }
    #date-picker-inline {
        overflow-x: hidden !important;
    }
    #date-picker-inline .flatpickr-weekdaycontainer,
    #date-picker-inline .dayContainer {
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }
    #date-picker-inline .flatpickr-days {
        flex-wrap: wrap;
    }
    .flatpickr-weekdays .flatpickr-weekdaycontainer:last-child {
        display: none;
    }
}

/* Desktop: two months side-by-side */
@media (min-width: 768px) {
    #datePickerModal > .relative {
        max-height: none !important;
        overflow-y: visible !important;
    }
    #date-picker-inline {
        overflow-x: visible !important;
    }
    #date-picker-inline .flatpickr-weekdaycontainer,
    #date-picker-inline .dayContainer {
        width: 50% !important;
        min-width: 0 !important;
        max-width: none !important;
        flex: 1 1 50%;
    }
}

/* ── Responsive selection screen override ── */
/* On mobile the flex column stacks: hoardings (order-1) on top, builder (order-2) below */
/* On desktop order reverses to left/right split handled by lg:order classes above */
@media (max-width: 1023.98px) {
    #selection-screen {
        flex-direction: column !important;
    }
    #selection-screen > div {
        width: 100% !important;
    }
    /* Hoardings panel: limit height on tablet so user can scroll to builder below */
    #selection-screen > div.order-1 .custom-scrollbar {
        max-height: 340px !important;
    }
}

/* ── Tablet 2-col grid for hoarding cards ── */
@media (min-width: 640px) and (max-width: 1023px) {
    #hoardings-grid:not(.list-view) {
        grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
}

/* Mobile: 2 cols for hoarding cards */
@media (max-width: 639.98px) {
    #hoardings-grid:not(.list-view) {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
}
</style>

{{-- ══════════════════════════════════════════════════════════════
     JAVASCRIPT  — 100% identical functionality, zero changes
══════════════════════════════════════════════════════════════ --}}
<script>
/* ================================================================
   CONFIG
================================================================ */
window.POS_GST_RATE = Number(@json((float) ($posGstRate ?? 18)));

function showToast(message, type = 'info') {
    if (window.Swal) {
        Swal.fire({ toast:true, position:'top-end', showConfirmButton:false, timer:3500, icon:type, title:message });
    } else {
        alert(message);
    }
}

const API_URL = '/vendor/pos/api';

/* ================================================================
   STATE
================================================================ */
let hoardings           = [];
let selectedHoardings   = new Map();
let selectedCustomer    = null;
let currentPage         = 1;
let totalPages          = 1;
let perPage             = 10;
let currentViewMode     = 'grid';
let activeFilters       = {};
let availabilityIssues  = {};

// date-picker
let currentFlatpickr         = null;
let currentHeatmapMap        = {};
let currentEditingHoardingId = null;
let dpCurrentStart           = null;

/* ================================================================
   FORMATTERS / PURE HELPERS
================================================================ */
const formatINR = v =>
    new Intl.NumberFormat('en-IN', { style:'currency', currency:'INR', maximumFractionDigits:0 }).format(v);

function toLocalYMD(date) {
    const d = new Date(date);
    return `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;
}
window.toYMD = toLocalYMD;

function getDurationMonths(startISO, endISO) {
    if (!startISO || !endISO) return 1;
    const diffDays = Math.ceil((new Date(endISO) - new Date(startISO)) / 86400000) + 1;
    return Math.max(1, Math.ceil(diffDays / 30));
}

function snapToMonths(startISO, rawEndISO) {
    const months  = getDurationMonths(startISO, rawEndISO);
    const snapped = new Date(startISO);
    snapped.setDate(snapped.getDate() + months * 30 - 1);
    return { endISO: toLocalYMD(snapped), months };
}

function endForMonths(startISO, n) {
    const d = new Date(startISO);
    d.setDate(d.getDate() + n * 30 - 1);
    return toLocalYMD(d);
}

function calcPrice(ppm, startISO, endISO) {
    return ppm * getDurationMonths(startISO, endISO);
}

function friendlyRange(startISO, endISO) {
    const opts = { day:'2-digit', month:'short', year:'numeric' };
    const s    = new Date(startISO).toLocaleDateString('en-IN', opts);
    const e    = new Date(endISO).toLocaleDateString('en-IN', opts);
    const m    = getDurationMonths(startISO, endISO);
    const lbl  = m === 1 ? '1 Month' : `${m} Months`;
    return { s, e, m, lbl, full: `${s} – ${e}`, badge: lbl };
}

function enumerateDates(startISO, endISO) {
    const dates = [];
    const cur   = new Date(startISO);
    const last  = new Date(endISO);
    while (cur <= last) {
        dates.push(toLocalYMD(cur));
        cur.setDate(cur.getDate() + 1);
    }
    return dates;
}
window.enumerateDatesBetween = enumerateDates;

function safe(val, fallback = '---') {
    return (val !== undefined && val !== null && val !== '') ? val : fallback;
}

function debounce(fn, t) {
    let timer;
    return (...args) => { clearTimeout(timer); timer = setTimeout(() => fn.apply(this, args), t); };
}

/* ================================================================
   FETCH HELPER
================================================================ */
const fetchJSON = async (url, opts = {}) => {
    const res = await fetch(url, {
        headers: {
            'Accept':           'application/json',
            'Content-Type':     'application/json',
            'X-CSRF-TOKEN':     '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest',
        },
        ...opts,
    });
    return res.json();
};

/* ================================================================
   INIT
================================================================ */
document.addEventListener('DOMContentLoaded', async () => {
    await loadHoardings();

    const params     = new URLSearchParams(window.location.search);
    const customerId = params.get('customer_id');
    if (customerId) {
        document.getElementById('new-customer-btn').style.display = 'none';
        try {
            let found = null;
            const r1  = await fetchJSON(`${API_URL}/customers/${customerId}`);
            found = r1.data || r1.customer || null;

            if (!found?.id) {
                const r2   = await fetchJSON(`${API_URL}/customers?search=${customerId}`);
                const list = r2.data?.data || r2.data || [];
                found      = list.find(c => String(c.id) === String(customerId)) || null;
            }
            if (found?.id) {
                selectCustomer(found);
                const si = document.getElementById('customer-search');
                if (si) si.value = found.name;
                const cb = document.getElementById('change-customer-btn');
                if (cb) cb.style.display = 'none';
            } else {
                _showCustomerError(customerId);
            }
        } catch (e) {
            console.error(e);
            _showCustomerError(customerId);
        }
    }

    document.getElementById('customer-search')
        .addEventListener('input', debounce(handleCustomerSearch, 300));
    document.getElementById('hoarding-search')
        .addEventListener('input', debounce(handleHoardingSearch, 220));
    document.getElementById('submit-btn')
        .addEventListener('click', handleSubmit);
});

function _showCustomerError(id) {
    document.getElementById('search-container')?.classList.add('hidden');
    document.getElementById('customer-selected-card')?.classList.remove('hidden');
    const s = (eid, v) => { const el = document.getElementById(eid); if (el) el.innerText = v; };
    s('cust-name',    'Customer not found');
    s('cust-details', `ID: ${id}`);
    s('cust-initials','?');
}

/* ================================================================
   VIEW MODE
================================================================ */
function setViewMode(mode) {
    currentViewMode = mode;
    const grid = document.getElementById('hoardings-grid');
    const btnG = document.getElementById('view-grid-btn');
    const btnL = document.getElementById('view-list-btn');

    if (mode === 'grid') {
        grid.classList.remove('list-view');
        btnG.className = btnG.className.replace('bg-white text-gray-600','').trim() + ' bg-gray-800 text-white';
        btnL.className = btnL.className.replace('bg-gray-800 text-white','').trim() + ' bg-white text-gray-600';
    } else {
        grid.classList.add('list-view');
        btnL.className = btnL.className.replace('bg-white text-gray-600','').trim() + ' bg-gray-800 text-white';
        btnG.className = btnG.className.replace('bg-gray-800 text-white','').trim() + ' bg-white text-gray-600';
    }
    renderHoardings(hoardings);
}

/* ================================================================
   FILTER TAGS
================================================================ */
const _filterLabelMap = {
    type:         v => (v && v !== 'ALL') ? v.toUpperCase() : null,
    category:     v => v ? v.split(',').map(c => c.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase())).join(', ') : null,
    availability: v => v ? v.split(',').map(a => a==='available'?'Available':'Booked').join(', ') : null,
    surroundings: v => v ? v.split(',').map(s => s.replace(/_/g,' ').replace(/\b\w/g,l=>l.toUpperCase())).join(', ') : null,
};

function renderFilterTags(params) {
    const wrap = document.getElementById('active-filter-tags');
    const list = document.getElementById('filter-tags-list');
    list.innerHTML = '';
    const tags = [];

    const skip = new Set(['page','per_page','search','hoarding_size_min','hoarding_size_max','screen_size_min','screen_size_max']);
    Object.entries(params).forEach(([k, v]) => {
        if (!v || v === '' || v === 'ALL' || skip.has(k)) return;
        const fn    = _filterLabelMap[k];
        const label = fn ? fn(v) : v;
        if (label) tags.push({ key:k, label });
    });

    const sMin = params.screen_size_min,  sMax = params.screen_size_max;
    if ((sMin && sMin!=='0') || (sMax && sMax!=='1000'))
        tags.push({ key:'screen_size', label:`Screen: ${sMin??0}–${sMax??1000} sq.ft` });
    const hMin = params.hoarding_size_min, hMax = params.hoarding_size_max;
    if ((hMin && hMin!=='0') || (hMax && hMax!=='1000'))
        tags.push({ key:'hoarding_size', label:`Hoarding: ${hMin??0}–${hMax??1000} sq.ft` });
    if (params.price_min || params.price_max)
        tags.push({ key:'price', label:`₹${params.price_min??0}–₹${params.price_max??'∞'}` });
    if (params.city) tags.push({ key:'city', label:params.city });

    tags.forEach(({ key, label }) => {
        const tag = document.createElement('span');
        tag.className = 'inline-flex items-center gap-1 bg-gray-100 border border-gray-200 text-gray-600 text-[10px] font-medium px-2 py-0.5 rounded';
        tag.innerHTML = `${label} <button onclick="removeFilterTag('${key}')" class="text-gray-400 hover:text-red-500 font-bold leading-none">✕</button>`;
        list.appendChild(tag);
    });

    wrap.classList.toggle('hidden', tags.length === 0);
    _syncUnselectBtn();
}

function removeFilterTag(key) {
    const pairs = {
        screen_size:   ['screen_size_min','screen_size_max'],
        hoarding_size: ['hoarding_size_min','hoarding_size_max'],
        price:         ['price_min','price_max'],
    };
    if (pairs[key]) pairs[key].forEach(k => delete activeFilters[k]);
    else delete activeFilters[key];
    currentPage = 1;
    loadHoardings(activeFilters);
}

function clearAllFilters() {
    activeFilters = {};
    currentPage   = 1;
    loadHoardings({});
    if (typeof resetFilters === 'function') resetFilters(false);
}

function _syncUnselectBtn() {
    const btn = document.getElementById('unselect-all-btn');
    if (btn) btn.classList.toggle('hidden', selectedHoardings.size === 0);
}

function unselectAllHoardings() {
    selectedHoardings.clear();
    availabilityIssues = {};
    updateSummary();
    _syncUnselectBtn();
}

/* ================================================================
   CUSTOMER
================================================================ */
function openCustomerModal()  { document.getElementById('customerModal').classList.remove('hidden'); }
function closeCustomerModal() { document.getElementById('customerModal').classList.add('hidden'); }

async function handleCustomerSearch(e) {
    const q   = e.target.value.trim();
    const box = document.getElementById('customer-suggestions');
    if (q.length < 2) { box.classList.add('hidden'); return; }
    try {
        const res  = await fetchJSON(`${API_URL}/customers?search=${encodeURIComponent(q)}`);
        const list = res.data?.data || res.data || [];
        box.innerHTML = list.length
            ? list.map(c => `
                <div class="px-4 py-3 hover:bg-green-50 cursor-pointer border-b last:border-0"
                    onclick='selectCustomer(${JSON.stringify(c).replace(/'/g,"&apos;")})'>
                    <div class="text-sm font-bold text-gray-800">${c.name}</div>
                    <div class="text-[10px] text-gray-500">${[c.phone,c.email].filter(Boolean).join(' · ')}</div>
                </div>`).join('')
            : '<div class="p-4 text-xs text-gray-400 text-center">No customers found</div>';
        box.classList.remove('hidden');
    } catch(e) { console.error(e); }
}

function selectCustomer(c) {
    if (!c?.name) { console.warn('Invalid customer', c); return; }
    selectedCustomer = c;
    document.getElementById('search-container')?.classList.add('hidden');
    const card = document.getElementById('customer-selected-card');
    card?.classList.remove('hidden');
    card?.classList.add('flex');
    document.getElementById('customer-suggestions')?.classList.add('hidden');
    const s = (eid,v) => { const el = document.getElementById(eid); if(el) el.innerText = v; };
    s('cust-name',     c.name);
    s('cust-details',  [c.email,c.phone].filter(Boolean).join(' · '));
    s('cust-initials', c.name.slice(0,2).toUpperCase());
}

function clearSelectedCustomer() {
    selectedCustomer = null;
    document.getElementById('search-container').classList.remove('hidden');
    const card = document.getElementById('customer-selected-card');
    card?.classList.add('hidden');
    card?.classList.remove('flex');
    const si = document.getElementById('customer-search');
    if (si) { si.value=''; si.focus(); }
}

/* ================================================================
   LOAD HOARDINGS
================================================================ */
async function loadHoardings(filters = {}) {
    activeFilters = { ...filters };
    const p       = { ...filters, page:currentPage, per_page:perPage };
    const query   = new URLSearchParams(p).toString();
    const res     = await fetchJSON(`${API_URL}/hoardings${query ? '?'+query : ''}`);

    if ('last_page' in res) {
        hoardings   = res.data          || [];
        totalPages  = res.last_page     || 1;
        currentPage = res.current_page  || 1;
    } else if (res.data && 'last_page' in res.data) {
        hoardings  = res.data.data   || [];
        totalPages = res.data.last_page || 1;
    } else {
        hoardings  = Array.isArray(res.data) ? res.data : (res.data?.data || []);
        totalPages = 1;
    }

    renderHoardings(hoardings);
    renderPagination();
    renderFilterTags(activeFilters);
}
window.loadHoardings = loadHoardings;

/* ================================================================
   RENDER HOARDINGS
================================================================ */
function renderHoardings(list) {
    const grid = document.getElementById('hoardings-grid');

    // renderHoardings function mein ye replace karo
    if (!list?.length) {
        grid.innerHTML = `
            <div class="col-span-full w-full flex flex-col items-center justify-center py-14 text-center">
                <svg class="w-12 h-12 text-gray-200 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                        d="M20 21l-4.35-4.35m0 0A7.5 7.5 0 103.5 3.5a7.5 7.5 0 0013.15 13.15z"/>
                </svg>
                <p class="text-sm font-bold text-gray-500 mb-1">No hoardings found</p>
                <p class="text-xs text-gray-400">Try adjusting your filters</p>
            </div>`;
        document.getElementById('available-count').innerText = 0;
        return;
    }

    const sorted = [
        ...list.filter(h => selectedHoardings.has(h.id)),
        ...list.filter(h => !selectedHoardings.has(h.id)),
    ];

    grid.innerHTML = sorted.map(h => _buildCard(h)).join('');
    document.getElementById('available-count').innerText = list.length;
}

function _buildCard(h) {
    const isSel  = selectedHoardings.has(h.id);
    const isDooh = h.type?.toUpperCase() === 'DOOH';
    const selH   = isSel ? selectedHoardings.get(h.id) : null;
    const loc    = h.display_location || h.location_address || h.city || '';

    const selCls     = isSel ? 'is-selected' : '';
    const checkBadge = isSel  ? `<span class="absolute top-1 left-1 bg-green-600 text-white text-[9px] font-black px-1.5 py-0.5 rounded leading-tight z-10">✓</span>` : '';
    const doohBadge  = isDooh ? `<span class="absolute top-1 right-1 bg-purple-600 text-white text-[8px] font-bold px-1.5 py-0.5 rounded z-10">DOOH</span>` : '';
    const dateSnip   = isSel && selH && selH.startDate && selH.endDate
        ? (() => { const r = friendlyRange(selH.startDate, selH.endDate);
                   return `<p class="text-[9px] font-semibold text-emerald-700 leading-tight mt-0.5 truncate">${r.full} <span class="text-emerald-500 font-bold">(${r.badge})</span></p>`; })()
        : (isSel ? `<p class="text-[9px] font-semibold text-orange-500 leading-tight mt-0.5">Tap to select dates</p>` : '');

    if (currentViewMode === 'list') {
        return `
        <div class="hoarding-card ${selCls} border rounded-lg overflow-hidden bg-white" onclick="toggleHoarding(${h.id})">
            <div class="flex items-center gap-2 p-2">
                <div class="relative flex-shrink-0">
                    <img src="${h.image_url||'/placeholder.png'}" class="hc-img w-[60px] h-[54px] object-cover rounded">
                    ${checkBadge}
                    ${isDooh ? `<span class="absolute bottom-0 right-0 bg-purple-600 text-white text-[8px] font-bold px-1 rounded">DOOH</span>` : ''}
                </div>
                <div class="hc-body flex-1 min-w-0 py-0.5">
                    <p class="text-[11px] font-bold text-gray-800 truncate">${h.title}</p>
                    ${loc ? `<p class="text-[9px] text-gray-400 truncate">${loc}</p>` : ''}
                    <p class="text-[10px] font-bold text-gray-700 mt-0.5">${formatINR(h.price_per_month)}<span class="font-normal text-gray-400">/mo</span></p>
                    ${isDooh ? `<p class="text-[9px] text-purple-600">${h.total_slots_per_day??300} slots/day</p>` : ''}
                    ${dateSnip}
                </div>
                ${isSel ? `
                <button onclick="event.stopPropagation(); toggleHoarding(${h.id})"
                    class="flex-shrink-0 text-[10px] font-bold text-red-500 border border-red-200 bg-red-50 hover:bg-red-100 px-2 py-1 rounded transition whitespace-nowrap">
                    Remove
                </button>` : ''}
            </div>
        </div>`;
    }

    // grid card
    return `
    <div class="hoarding-card ${selCls} border rounded-lg border-gray-200 overflow-hidden bg-white rounded" onclick="toggleHoarding(${h.id})">
        <div class="relative">
            <img src="${h.image_url||'/placeholder.png'}" class="w-full object-cover" style="height:72px;">
            ${checkBadge}${doohBadge}
        </div>
        <div class="p-2">
            <p class="text-[10px] font-bold text-gray-800 truncate leading-tight" title="${h.title}">${h.title}</p>
            ${loc ? `<p class="text-[9px] text-gray-400 truncate">${loc}</p>` : ''}
            <p class="text-[10px] font-bold text-gray-700 mt-0.5">${formatINR(h.price_per_month)}<span class="font-normal text-gray-400">/mo</span></p>
            ${isDooh ? `<p class="text-[9px] text-purple-600">${h.total_slots_per_day??300} slots/day</p>` : ''}
            ${dateSnip}
        </div>
    </div>`;
}

/* ================================================================
   PAGINATION
================================================================ */
function renderPagination() {
    const el = document.getElementById('hoardings-pagination');
    if (!el) return;
    if (totalPages <= 1) { el.innerHTML=''; return; }

    const mkBtn = (lbl, page, disabled, active) =>
        `<button onclick="changePage(${page})" ${disabled?'disabled':''}
            class="px-2.5 py-1 border rounded text-xs font-medium transition
                ${active ? 'bg-green-600 text-white border-green-600' :
                  disabled ? 'bg-gray-100 text-gray-400 cursor-not-allowed' : 'bg-white hover:bg-gray-50'}">${lbl}</button>`;

    let html = mkBtn('‹ Prev', currentPage-1, currentPage===1, false);
    const start = Math.max(1, currentPage-2), end = Math.min(totalPages, start+4);
    for (let i = start; i <= end; i++) html += mkBtn(i, i, false, i===currentPage);
    html += mkBtn('Next ›', currentPage+1, currentPage===totalPages, false);
    el.innerHTML = html;
}

function changePage(page) {
    if (page < 1 || page > totalPages || page === currentPage) return;
    currentPage = page;
    loadHoardings({ ...activeFilters });
    document.getElementById('hoardings-grid')?.scrollTo({ top:0, behavior:'smooth' });
}

function handleHoardingSearch() {
    const q = document.getElementById('hoarding-search').value.trim();
    currentPage = 1;
    loadHoardings(q ? { ...activeFilters, search:q } : { ...activeFilters });
}

/* ================================================================
   TOGGLE HOARDING
================================================================ */
function toggleHoarding(id) {
    let shouldOpenDatePicker = false;

    if (selectedHoardings.has(id)) {
        selectedHoardings.delete(id);
        delete availabilityIssues[id];
    } else {
        const h = hoardings.find(i => i.id === id);
        if (!h) return;
        selectedHoardings.set(id, { ...h, startDate: null, endDate: null });
        shouldOpenDatePicker = true;
    }
    updateSummary();
    _syncUnselectBtn();

    if (shouldOpenDatePicker) {
        setTimeout(() => openDatePickerForHoarding(id), 120);
    }
}

/* ================================================================
   SUMMARY TABLES (left panel)
================================================================ */
function updateSummary() {
    renderHoardings(hoardings);
    _syncUnselectBtn();

    const oohBody  = document.getElementById('ooh-selected-list');
    const doohBody = document.getElementById('dooh-selected-list');

    if (selectedHoardings.size === 0) {
        oohBody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>`;
        doohBody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>`;
        document.getElementById('btn-count').innerText = 0;
        return;
    }

    let hasOoh = false, hasDooh = false;
    oohBody.innerHTML = ''; doohBody.innerHTML = '';

    selectedHoardings.forEach((h, id) => {
        const isDooh      = h.type?.toUpperCase() === 'DOOH';
        const hasDates    = Boolean(h.startDate && h.endDate);
        const totalPrice  = hasDates ? calcPrice(h.price_per_month, h.startDate, h.endDate) : null;
        const issue       = availabilityIssues[id];
        const conflictCls = issue ? 'availability-conflict' : '';
        const conflictBadge = issue
            ? `<span class="block mt-1 text-[9px] font-bold text-red-600 bg-red-100 px-1.5 py-0.5 rounded w-fit">${issue.label}</span>` : '';
        const loc   = h.display_location || h.location_address || h.city || '';
        const rng   = hasDates ? friendlyRange(h.startDate, h.endDate) : null;

        const durCell = hasDates
            ? `<button class="dur-btn" onclick="openDatePickerForHoarding(${h.id})">
                    <span class="block">${rng.full}</span>
                    <span class="dur-sub">${rng.badge} · tap to change</span>
               </button>`
            : `<button class="dur-btn" onclick="openDatePickerForHoarding(${h.id})">
                    <span class="block text-orange-600 font-semibold">Select dates</span>
                    <span class="dur-sub">Tap to open calendar</span>
               </button>`;

        const rmBtn = `
            <button onclick="toggleHoarding(${h.id})" class="text-red-400 hover:text-red-600 transition ml-auto block" title="Remove">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </button>`;

        if (isDooh) {
            hasDooh = true;
            doohBody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b border-gray-100 ${conflictCls}">
                <td class="px-4 py-3">
                    <p class="text-xs font-bold text-gray-800">${h.title}</p>
                    ${loc ? `<p class="text-[9px] text-gray-400 truncate max-w-[150px]">${loc}</p>` : ''}
                    ${conflictBadge}
                </td>
                <td class="px-3 py-3 text-xs text-gray-500 hidden sm:table-cell">${formatINR(h.price_per_month)}</td>
                <td class="px-3 py-3 text-center hidden sm:table-cell">
                    <span class="inline-flex items-center gap-1 bg-purple-50 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full">${h.total_slots_per_day??300}</span>
                </td>
                <td class="px-4 py-3">${durCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-green-700 whitespace-nowrap">${totalPrice !== null ? formatINR(totalPrice) : '—'}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`;
        } else {
            hasOoh = true;
            oohBody.innerHTML += `
            <tr class="hover:bg-gray-50 border-b border-gray-100 ${conflictCls}">
                <td class="px-4 py-3">
                    <p class="text-xs font-bold text-gray-800">${h.title}</p>
                    ${loc ? `<p class="text-[9px] text-gray-400 truncate max-w-[150px]">${loc}</p>` : ''}
                    ${conflictBadge}
                </td>
                <td class="px-3 py-3 text-xs text-gray-500 hidden sm:table-cell">${formatINR(h.price_per_month)}</td>
                <td class="px-4 py-3">${durCell}</td>
                <td class="px-3 py-3 text-xs font-bold text-green-700 whitespace-nowrap">${totalPrice !== null ? formatINR(totalPrice) : '—'}</td>
                <td class="px-3 py-3 text-right">${rmBtn}</td>
            </tr>`;
        }
    });

    if (!hasOoh)  oohBody.innerHTML  = `<tr><td colspan="5" class="px-4 py-8 text-center text-gray-400 italic text-xs">No static hoardings selected</td></tr>`;
    if (!hasDooh) doohBody.innerHTML = `<tr><td colspan="6" class="px-4 py-8 text-center text-gray-400 italic text-xs">No digital slots selected</td></tr>`;
    document.getElementById('btn-count').innerText = selectedHoardings.size;
}

/* ================================================================
   AVAILABILITY CHECK
================================================================ */
function _statusLabel(s) {
    return { booked:'Already Booked', blocked:'Blocked/Maintenance', hold:'On Hold', partial:'Partially Unavailable' }[s] || s;
}

async function checkAllAvailability() {
    availabilityIssues = {};
    let allClear = true;

    await Promise.all(Array.from(selectedHoardings.entries()).map(async ([id, h]) => {
        if (!h.startDate || !h.endDate) {
            allClear = false;
            availabilityIssues[id] = { title:h.title, label:'Dates not selected', conflicts:[] };
            return;
        }

        try {
            const dates = enumerateDates(h.startDate, h.endDate);
            const res   = await fetch(`/api/v1/hoardings/${id}/availability/check-dates`, {
                method: 'POST', credentials:'same-origin',
                headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
                body: JSON.stringify({ dates }),
            });
            if (!res.ok) return;
            const data      = await res.json();
            const conflicts = (data.data?.results || []).filter(r => r.status !== 'available');
            if (conflicts.length) {
                allClear = false;
                const statuses = [...new Set(conflicts.map(c => c.status))];
                availabilityIssues[id] = { title:h.title, label:statuses.map(_statusLabel).join(', '), conflicts };
            }
        } catch(e) { console.error('Avail check', id, e); }
    }));

    return allClear;
}
window.finalCheckAvailability = async () => checkAllAvailability();

function showAvailabilityAlert(issues) {
    const alert = document.getElementById('availability-alert');
    const body  = document.getElementById('availability-alert-body');
    const rows  = Object.entries(issues);
    if (!rows.length) { alert.classList.add('hidden'); return; }

    body.innerHTML = rows.map(([id, i]) => `
        <div class="flex items-start gap-2 py-0.5">
            <span class="font-bold text-red-700 flex-shrink-0">${i.title}:</span>
            <span class="flex-1">${i.label} —
                <button onclick="openDatePickerForHoarding(${id})"
                    class="underline font-bold text-red-700 hover:text-red-900 transition">Fix Dates →</button>
            </span>
        </div>`).join('');
    alert.classList.remove('hidden');
    alert.scrollIntoView({ behavior:'smooth', block:'center' });
}

/* ================================================================
   DATE PICKER MODAL
================================================================ */
function _updateDpBar(startISO, endISO, ppm) {
    const rangeEl  = document.getElementById('dp-range-label');
    const monthsEl = document.getElementById('dp-months-label');
    const costEl   = document.getElementById('dp-cost-label');

    if (!startISO) {
        if (rangeEl)  rangeEl.innerText  = '— Pick a date';
        if (monthsEl) monthsEl.innerText = '—';
        if (costEl)   costEl.innerText   = '—';
        _setActiveChip(null);
        return;
    }

    const r = friendlyRange(startISO, endISO || startISO);
    if (rangeEl)  rangeEl.innerHTML = `${r.s}&nbsp;–&nbsp;${endISO ? r.e : '…'}`;
    if (monthsEl) monthsEl.innerText = endISO ? r.badge : '—';
    if (costEl)   costEl.innerText   = (endISO && ppm) ? formatINR(calcPrice(ppm, startISO, endISO)) : '—';
    _setActiveChip(endISO ? r.m : null);
}

function _setActiveChip(months) {
    document.querySelectorAll('.dp-quick-chip').forEach(btn => {
        btn.classList.toggle('chip-active', months !== null && parseInt(btn.dataset.months) === months);
    });
}

function quickSelectMonths(n) {
    if (!currentFlatpickr) return;
    const start = dpCurrentStart || toLocalYMD(new Date());
    const end   = endForMonths(start, n);
    currentFlatpickr.setDate([start, end], false);
    dpCurrentStart = start;
    const h = selectedHoardings.get(currentEditingHoardingId);
    _updateDpBar(start, end, h?.price_per_month);
}

async function openDatePickerForHoarding(id) {
    if (typeof flatpickr === 'undefined') { showToast('Calendar library not loaded.', 'error'); return; }

    currentEditingHoardingId = id;
    const h = selectedHoardings.get(id);
    if (!h) { showToast('Please select the hoarding first.', 'warning'); return; }

    dpCurrentStart = h.startDate || null;
    const titleEl = document.getElementById('datePickerTitle');
    const fullTitle = (h.title || 'Select Booking Dates').toString();
    if (titleEl) {
        titleEl.innerText = fullTitle.length > 40 ? `${fullTitle.slice(0, 40).trimEnd()}...` : fullTitle;
        titleEl.title = fullTitle;
    }
    document.getElementById('datePickerModal').classList.remove('hidden');
    document.getElementById('date-picker-inline').innerHTML =
        '<div class="text-center py-8 text-sm text-gray-400 animate-pulse">Loading calendar…</div>';
    _updateDpBar(h.startDate || null, h.endDate || null, h.price_per_month);

    const defaultDate = h.startDate ? (h.endDate ? [h.startDate, h.endDate] : [h.startDate]) : [];
    const today     = toLocalYMD(new Date());
    const farFuture = new Date(); farFuture.setDate(farFuture.getDate() + 730);

    try {
        const res = await fetch(
            `/api/v1/hoardings/${id}/availability/heatmap?start_date=${today}&end_date=${toLocalYMD(farFuture)}`,
            { credentials:'same-origin', headers:{ 'Accept':'application/json' } }
        );
        if (!res.ok) throw new Error(`HTTP ${res.status}`);
        const payload = await res.json();
        const heatmap = payload.data?.heatmap || [];

        currentHeatmapMap = {};
        const disabledDates = [];
        heatmap.forEach(d => {
            currentHeatmapMap[d.date] = d.status;
            if (d.status && d.status !== 'available') disabledDates.push(d.date);
        });

        document.getElementById('date-picker-inline').innerHTML = '';
        if (currentFlatpickr) { currentFlatpickr.destroy(); currentFlatpickr = null; }

        currentFlatpickr = flatpickr('#date-picker-input', {
            mode:        'range',
            inline:      true,
            appendTo:    document.getElementById('date-picker-inline'),
            minDate:     today,
            disable:     disabledDates,
            defaultDate,
            showMonths:  window.innerWidth < 668 ? 1 : 2,

            onReady(selectedDates, dateStr, fp) {
                fp.calendarContainer.addEventListener('mousedown', (event) => {
                    const target = event.target;
                    if (!(target instanceof Element)) return;
                    const dayElem = target.closest('.flatpickr-day');
                    if (!dayElem || !dayElem.dateObj) return;
                    if (dayElem.classList.contains('flatpickr-disabled')) return;

                    const selectedCount = fp.selectedDates.length;
                    if (selectedCount === 0) return;

                    const clickedISO      = toLocalYMD(dayElem.dateObj);
                    const currentStartISO = fp.selectedDates[0] ? toLocalYMD(fp.selectedDates[0]) : null;
                    const currentEndISO   = fp.selectedDates[1] ? toLocalYMD(fp.selectedDates[1]) : null;

                    if (!currentStartISO || clickedISO === currentStartISO || clickedISO === currentEndISO) return;

                    let shouldResetToFreshStart = selectedCount >= 2 || dayElem.classList.contains('notAllowed');

                    if (!shouldResetToFreshStart && selectedCount === 1) {
                        const fromISO = clickedISO < currentStartISO ? clickedISO : currentStartISO;
                        const toISO   = clickedISO < currentStartISO ? currentStartISO : clickedISO;
                        shouldResetToFreshStart = enumerateDates(fromISO, toISO).some((dateISO) => {
                            if (dateISO === clickedISO) return false;
                            const status = currentHeatmapMap[dateISO];
                            return status && status !== 'available';
                        });
                    }

                    if (!shouldResetToFreshStart) return;

                    event.preventDefault();
                    if (typeof event.stopImmediatePropagation === 'function') event.stopImmediatePropagation();
                    event.stopPropagation();
                    fp.clear(false);
                    fp.setDate([clickedISO], true);
                }, true);
            },

            onDayCreate(dObj, dStr, fp, dayElem) {
                const date   = toLocalYMD(dayElem.dateObj);
                const status = currentHeatmapMap[date];
                if (!status || status === 'available') { dayElem.classList.add('avail-day'); dayElem.title = 'Available'; }
                else if (status === 'booked')  { dayElem.classList.add('day-booked');  dayElem.title = 'Booked';   }
                else if (status === 'blocked') { dayElem.classList.add('day-blocked'); dayElem.title = 'Blocked';  }
                else if (status === 'hold')    { dayElem.classList.add('day-hold');    dayElem.title = 'On Hold';  }
                else if (status === 'partial') { dayElem.classList.add('day-partial'); dayElem.title = 'Partial';  }
            },

            onChange(selectedDates) {
                if (!selectedDates.length) return;
                const start = toLocalYMD(selectedDates[0]);
                dpCurrentStart = start;

                if (selectedDates.length === 1) {
                    _updateDpBar(start, null, h.price_per_month);
                    return;
                }

                const rawEnd = toLocalYMD(selectedDates[1]);
                const { endISO } = snapToMonths(start, rawEnd === start ? endForMonths(start, 1) : rawEnd);
                _updateDpBar(start, endISO, h.price_per_month);

                if (endISO !== rawEnd) {
                    setTimeout(() => currentFlatpickr?.setDate([start, endISO], false), 0);
                }
            },
        });

    } catch(e) {
        console.error(e);
        showToast('Could not load availability data. Please try again.', 'error');
        closeDatePickerModal();
    }
}

function closeDatePickerModal() {
    document.getElementById('datePickerModal').classList.add('hidden');
    if (currentFlatpickr) { currentFlatpickr.destroy(); currentFlatpickr = null; }
    document.getElementById('date-picker-inline').innerHTML = '';
    currentEditingHoardingId = null;
    dpCurrentStart           = null;
}

async function confirmDateSelection() {
    if (!currentFlatpickr || !currentEditingHoardingId) { closeDatePickerModal(); return; }

    const dates = currentFlatpickr.selectedDates;
    if (!dates?.length) { showToast('Please select a start date first.', 'warning'); return; }

    const startISO = toLocalYMD(dates[0]);
    const rawEnd   = dates.length >= 2 ? toLocalYMD(dates[1]) : startISO;
    const { endISO } = snapToMonths(startISO, rawEnd === startISO ? endForMonths(startISO, 1) : rawEnd);

    const allDates = enumerateDates(startISO, endISO);
    try {
        const res = await fetch(`/api/v1/hoardings/${currentEditingHoardingId}/availability/check-dates`, {
            method: 'POST', credentials:'same-origin',
            headers: { 'Content-Type':'application/json','Accept':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}' },
            body: JSON.stringify({ dates: allDates }),
        });
        if (!res.ok) { showToast('Could not verify availability.', 'error'); return; }
        const data      = await res.json();
        const conflicts = (data.data?.results || []).filter(r => r.status !== 'available');

        if (conflicts.length) {
            showToast('Selected range includes unavailable dates. Please choose a different period.', 'warning');
            return;
        }

        const h = selectedHoardings.get(currentEditingHoardingId);
        if (h) {
            h.startDate = startISO;
            h.endDate   = endISO;
            selectedHoardings.set(currentEditingHoardingId, h);
            delete availabilityIssues[currentEditingHoardingId];
            updateSummary();
        }

        closeDatePickerModal();
        const rng = friendlyRange(startISO, endISO);
        showToast(`Dates confirmed: ${rng.full} (${rng.badge})`, 'success');

    } catch(e) {
        console.error(e);
        showToast('Error checking availability. Please try again.', 'error');
    }
}

/* ================================================================
   SUBMIT → PREVIEW
================================================================ */
async function handleSubmit() {
    if (!selectedCustomer)             { showToast('Please select a customer.', 'warning');    return; }
    if (selectedHoardings.size === 0)  { showToast('Select at least one hoarding.', 'warning'); return; }

    const firstMissingDates = Array.from(selectedHoardings.entries())
        .find(([, h]) => !h.startDate || !h.endDate);
    if (firstMissingDates) {
        const [missingId, missingH] = firstMissingDates;
        showToast(`Please select booking dates for: ${missingH.title}`, 'warning');
        setTimeout(() => openDatePickerForHoarding(missingId), 150);
        return;
    }

    const btn  = document.getElementById('submit-btn');
    const orig = btn.innerHTML;
    btn.disabled  = true;
    btn.innerHTML = `<svg class="w-4 h-4 inline animate-spin mr-2" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/>
    </svg>Checking Availability…`;

    const allClear = await checkAllAvailability();
    btn.disabled   = false;
    btn.innerHTML  = orig;

    if (!allClear) {
        updateSummary();
        showAvailabilityAlert(availabilityIssues);
        showToast('Availability conflicts found. Please review and fix dates.', 'error');
        const firstId = Object.keys(availabilityIssues)[0];
        if (firstId) setTimeout(() => openDatePickerForHoarding(parseInt(firstId)), 500);
        return;
    }

    document.getElementById('availability-alert').classList.add('hidden');
    populatePreview();
    document.getElementById('selection-screen').classList.add('hidden');
    document.getElementById('preview-screen').classList.remove('hidden');
    window.scrollTo({ top:0, behavior:'smooth' });
}

function backToSelection() {
    document.getElementById('preview-screen').classList.add('hidden');
    document.getElementById('selection-screen').classList.remove('hidden');
}

/* ================================================================
   PREVIEW POPULATE
================================================================ */
let globalBaseAmount = 0;

function populatePreview() {
    if (!selectedCustomer) return;

    const oohBody  = document.getElementById('preview-ooh-list');
    const doohBody = document.getElementById('preview-dooh-list');
    if (!oohBody || !doohBody) { console.error('Preview DOM missing!'); return; }

    const s = (id, val) => { const el = document.getElementById(id); if(el) el.innerText = safe(val); };
    s('preview-cust-name',    selectedCustomer.name);
    s('preview-cust-phone',   selectedCustomer.phone);
    s('preview-cust-email',   selectedCustomer.email);
    s('preview-cust-gstin',   selectedCustomer.gstin);
    s('preview-cust-status',  selectedCustomer.status);
    s('preview-cust-role',    selectedCustomer.role);
    s('preview-cust-address',
        [selectedCustomer.billing_address, selectedCustomer.billing_city,
         selectedCustomer.billing_state,   selectedCustomer.billing_pincode]
            .filter(Boolean).join(', ') || '---');
    s('preview-cust-country',  selectedCustomer.country);
    s('preview-total-count',   selectedHoardings.size);

    oohBody.innerHTML = ''; doohBody.innerHTML = '';
    globalBaseAmount  = 0;
    let sn = 1;

    selectedHoardings.forEach(h => {
        const itemTotal  = calcPrice(h.price_per_month, h.startDate, h.endDate);
        globalBaseAmount += itemTotal;

        const isDooh     = h.type?.toUpperCase() === 'DOOH';
        const loc        = h.display_location || h.location_address || h.city || '---';
        const rng        = friendlyRange(h.startDate, h.endDate);
        const typeBadge  = isDooh
            ? `<span class="text-[9px] font-bold bg-purple-100 text-purple-700 px-1.5 py-0.5 rounded">DOOH</span>`
            : `<span class="text-[9px] font-bold bg-blue-100 text-blue-700 px-1.5 py-0.5 rounded">OOH</span>`;
        const slotsSnip  = isDooh
            ? `<p class="text-[9px] text-purple-600 mt-0.5">${h.total_slots_per_day??300} slots/day</p>` : '';

        const row = `
        <tr class="border-b border-gray-100 hover:bg-gray-50">
            <td class="px-4 py-2 text-xs text-gray-400 font-semibold w-8">${sn++}</td>
            <td class="px-4 py-2">
                <div class="flex items-center gap-2">
                    <img src="${h.image_url||'/placeholder.png'}" class="w-9 h-9 rounded object-cover border border-gray-100 flex-shrink-0">
                    <div>
                        <p class="font-bold text-gray-800 text-xs leading-tight">${safe(h.title)}</p>
                        ${slotsSnip}
                    </div>
                </div>
            </td>
            <td class="px-4 py-2 text-xs text-gray-500">${loc}</td>
            <td class="px-4 py-2">${typeBadge}</td>
            <td class="px-4 py-2">
                <p class="text-xs font-semibold text-gray-700">${rng.full}</p>
                <p class="text-[10px] text-emerald-600 font-bold">${rng.badge}</p>
            </td>
            <td class="px-4 py-2 text-right font-bold text-gray-800 text-xs">${formatINR(itemTotal)}</td>
        </tr>`;
        oohBody.innerHTML += row;
    });
    doohBody.innerHTML = '';

    const sub = document.getElementById('side-sub-total');
    if (sub) sub.innerText = formatINR(globalBaseAmount);
    if (typeof calculateFinalTotals === 'function') calculateFinalTotals();
}
</script>

<script>
    function openFilterModal()  { document.getElementById('filterModal').classList.remove('hidden'); }
    function closeFilterModal() { document.getElementById('filterModal').classList.add('hidden'); }
</script>

@endsection