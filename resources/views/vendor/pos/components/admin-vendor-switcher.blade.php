@if(($posLayout ?? null) === 'layouts.admin' && isset($posVendors) && isset($selectedPosVendorId))
    <div class="mb-4 rounded-lg border border-gray-200 bg-white py-3 px-6 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-600">POS Vendor Context</div>
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2" id="admin-pos-context-form">
            <label for="admin-pos-booking-scope" class="text-sm text-gray-700">Scope</label>
            <select id="admin-pos-booking-scope" name="booking_scope" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="overall" {{ ($selectedPosBookingScope ?? 'vendor') === 'overall' ? 'selected' : '' }}>Overall Bookings</option>
                <option value="mine"    {{ ($selectedPosBookingScope ?? 'vendor') === 'mine'    ? 'selected' : '' }}>My Bookings</option>
                <option value="vendor"  {{ ($selectedPosBookingScope ?? 'vendor') === 'vendor'  ? 'selected' : '' }}>Select Vendor</option>
            </select>

            <div id="admin-pos-vendor-wrap"
                class="flex items-center gap-2 {{ in_array(($selectedPosBookingScope ?? 'vendor'), ['overall', 'mine']) ? 'hidden' : '' }}">

                <label for="admin-pos-vendor-search" class="text-sm text-gray-700">Vendor</label>

                {{-- Hidden field carries the real vendor_id on submit --}}
                <input type="hidden" id="admin-pos-vendor-id" name="vendor_id" value="{{ $selectedPosVendorId }}">

                <div class="relative w-72">
                    <input
                        type="text"
                        id="admin-pos-vendor-search"
                        placeholder="Search vendor..."
                        autocomplete="off"
                        class="border border-gray-300 rounded-md px-3 py-2 text-sm w-full pr-8"
                    >
                    {{-- Chevron icon --}}
                    <span class="pointer-events-none absolute inset-y-0 right-2 flex items-center text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </span>

                    {{-- Custom dropdown --}}
                    <ul
                        id="admin-pos-vendor-dropdown"
                        class="absolute z-50 w-full bg-white border border-gray-300 rounded-md shadow-lg mt-1 max-h-56 overflow-y-auto hidden"
                        role="listbox"
                    ></ul>
                </div>
            </div>

            <noscript>
                <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded-md text-sm">Apply</button>
            </noscript>
        </form>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function () {

        const scopeSelect  = document.getElementById('admin-pos-booking-scope');
        const vendorWrap   = document.getElementById('admin-pos-vendor-wrap');
        const vendorHidden = document.getElementById('admin-pos-vendor-id');
        const vendorSearch = document.getElementById('admin-pos-vendor-search');
        const dropdown     = document.getElementById('admin-pos-vendor-dropdown');
        const form         = document.getElementById('admin-pos-context-form');

        if (!scopeSelect || !vendorWrap || !vendorHidden || !vendorSearch || !dropdown) return;

        // Vendor data injected from Blade
        const vendors    = @json($posVendors->map(fn($v) => ['id' => $v->id, 'label' => $v->name . ' (' . $v->email . ')']));
        const selectedId = {{ (int) $selectedPosVendorId }};

        // Only pre-fill if a vendor was previously selected (restores label after page reload)
        if (selectedId) {
            const preSelected = vendors.find(v => v.id === selectedId);
            if (preSelected) vendorSearch.value = preSelected.label;
        }

        // ── Render dropdown ──────────────────────────────────────────────
        function renderDropdown(query) {
            const q        = query.trim().toLowerCase();
            const filtered = q === ''
                ? vendors
                : vendors.filter(v => v.label.toLowerCase().includes(q));

            dropdown.innerHTML = '';

            if (filtered.length === 0) {
                const empty       = document.createElement('li');
                empty.textContent = 'No vendors found';
                empty.className   = 'px-3 py-2 text-sm text-gray-400 select-none';
                dropdown.appendChild(empty);
            } else {
                filtered.forEach(v => {
                    const li       = document.createElement('li');
                    li.textContent = v.label;
                    li.dataset.id  = v.id;
                    li.setAttribute('role', 'option');
                    li.className   = 'px-3 py-2 text-sm cursor-pointer hover:bg-blue-50 hover:text-blue-700' +
                                     (v.id === selectedId ? ' bg-gray-50 font-medium' : '');

                    // mousedown fires before blur — prevents dropdown closing before selection
                    li.addEventListener('mousedown', function (e) {
                        e.preventDefault();
                        selectVendor(v);
                    });

                    dropdown.appendChild(li);
                });
            }

            dropdown.classList.remove('hidden');
        }

        function selectVendor(v) {
            vendorSearch.value = v.label;
            vendorHidden.value = v.id;
            closeDropdown();
            form.submit();
        }

        function closeDropdown() {
            dropdown.classList.add('hidden');
        }

        // ── Events ───────────────────────────────────────────────────────
        vendorSearch.addEventListener('focus', () => renderDropdown(vendorSearch.value));
        vendorSearch.addEventListener('input', () => renderDropdown(vendorSearch.value));
        vendorSearch.addEventListener('blur',  () => setTimeout(closeDropdown, 150));

        // Keyboard navigation
        vendorSearch.addEventListener('keydown', function (e) {
            const items  = Array.from(dropdown.querySelectorAll('li[data-id]'));
            const active = dropdown.querySelector('li.bg-blue-100');
            let idx      = items.indexOf(active);

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                if (active) active.classList.remove('bg-blue-100');
                idx = (idx + 1) % items.length;
                items[idx]?.classList.add('bg-blue-100');
                items[idx]?.scrollIntoView({ block: 'nearest' });

            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                if (active) active.classList.remove('bg-blue-100');
                idx = (idx - 1 + items.length) % items.length;
                items[idx]?.classList.add('bg-blue-100');
                items[idx]?.scrollIntoView({ block: 'nearest' });

            } else if (e.key === 'Enter') {
                e.preventDefault();
                const highlighted = dropdown.querySelector('li.bg-blue-100');
                if (highlighted) selectVendor({ id: +highlighted.dataset.id, label: highlighted.textContent });

            } else if (e.key === 'Escape') {
                closeDropdown();
            }
        });

        // ── Scope change ─────────────────────────────────────────────────
        function applyVisibility() {
            const isVendor = scopeSelect.value === 'vendor';
            vendorWrap.classList.toggle('hidden', !isVendor);
            vendorSearch.disabled = !isVendor;
        }

        scopeSelect.addEventListener('change', function () {
            applyVisibility();
            form.submit();
        });

        applyVisibility();
    });
    </script>
@endif