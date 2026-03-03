@if(($posLayout ?? null) === 'layouts.admin' && isset($posVendors) && isset($selectedPosVendorId))
    <div class="mb-4 rounded-lg border border-gray-200 bg-white p-3 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div class="text-sm text-gray-600">POS Vendor Context</div>
        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-2" id="admin-pos-context-form">
            <label for="admin-pos-booking-scope" class="text-sm text-gray-700">Scope</label>
            <select id="admin-pos-booking-scope" name="booking_scope" class="border border-gray-300 rounded-md px-3 py-2 text-sm">
                <option value="overall" {{ ($selectedPosBookingScope ?? 'vendor') === 'overall' ? 'selected' : '' }}>Overall Bookings</option>
                <option value="mine" {{ ($selectedPosBookingScope ?? 'vendor') === 'mine' ? 'selected' : '' }}>My Bookings</option>
                <option value="vendor" {{ ($selectedPosBookingScope ?? 'vendor') === 'vendor' ? 'selected' : '' }}>Select Vendor</option>
            </select>

            <div id="admin-pos-vendor-wrap" class="flex items-center gap-2 {{ in_array(($selectedPosBookingScope ?? 'vendor'), ['overall', 'mine']) ? 'hidden' : '' }}">
                <label for="admin-pos-vendor" class="text-sm text-gray-700">Vendor</label>
                <input
                    type="text"
                    id="admin-pos-vendor-search"
                    placeholder="Search vendor..."
                    class="border border-gray-300 rounded-md px-3 py-2 text-sm w-56"
                    autocomplete="off"
                >
                <select id="admin-pos-vendor" name="vendor_id" class="border border-gray-300 rounded-md px-3 py-2 text-sm" {{ in_array(($selectedPosBookingScope ?? 'vendor'), ['overall', 'mine']) ? 'disabled' : '' }}>
                    <option value="" {{ empty($selectedPosVendorId) ? 'selected' : '' }}>Select vendor</option>
                    @foreach($posVendors as $vendor)
                        <option value="{{ $vendor->id }}" {{ (int) $selectedPosVendorId === (int) $vendor->id ? 'selected' : '' }}>
                            {{ $vendor->name }} ({{ $vendor->email }})
                        </option>
                    @endforeach
                </select>
            </div>
            <noscript>
                <button type="submit" class="px-3 py-2 bg-gray-900 text-white rounded-md text-sm">Apply</button>
            </noscript>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const scopeSelect = document.getElementById('admin-pos-booking-scope');
            const vendorWrap = document.getElementById('admin-pos-vendor-wrap');
            const vendorSelect = document.getElementById('admin-pos-vendor');
            const vendorSearch = document.getElementById('admin-pos-vendor-search');

            if (!scopeSelect || !vendorWrap || !vendorSelect || !vendorSearch) {
                return;
            }

            const allVendorOptions = Array.from(vendorSelect.options)
                .filter((option) => option.value !== '')
                .map((option) => ({
                value: option.value,
                label: option.text,
                selected: option.selected,
            }));

            const form = document.getElementById('admin-pos-context-form');
            const submitForm = () => {
                if (form) {
                    form.submit();
                }
            };

            const rebuildVendorOptions = () => {
                const query = (vendorSearch.value || '').trim().toLowerCase();
                const currentValue = vendorSelect.value || allVendorOptions.find((option) => option.selected)?.value;

                const filtered = allVendorOptions.filter((option) => {
                    if (!query) {
                        return true;
                    }

                    return option.label.toLowerCase().includes(query);
                });

                vendorSelect.innerHTML = '';

                const placeholderOption = document.createElement('option');
                placeholderOption.value = '';
                placeholderOption.text = 'Select vendor';
                placeholderOption.selected = !currentValue;
                vendorSelect.appendChild(placeholderOption);

                if (!filtered.length) {
                    const emptyOption = document.createElement('option');
                    emptyOption.value = '';
                    emptyOption.text = 'No vendors found';
                    emptyOption.disabled = true;
                    emptyOption.selected = true;
                    vendorSelect.appendChild(emptyOption);
                    return;
                }

                filtered.forEach((optionData) => {
                    const option = document.createElement('option');
                    option.value = optionData.value;
                    option.text = optionData.label;
                    option.selected = String(optionData.value) === String(currentValue);
                    vendorSelect.appendChild(option);
                });

                if (!vendorSelect.value) {
                    vendorSelect.selectedIndex = 0;
                }
            };

            const applyVisibility = () => {
                const isHidden = scopeSelect.value === 'overall' || scopeSelect.value === 'mine';
                vendorWrap.classList.toggle('hidden', isHidden);
                vendorSelect.disabled = isHidden;
                vendorSearch.disabled = isHidden;
            };

            vendorSearch.addEventListener('input', rebuildVendorOptions);
            scopeSelect.addEventListener('change', () => {
                applyVisibility();

                if (scopeSelect.value === 'vendor') {
                    if (vendorSelect.value) {
                        submitForm();
                    }
                    return;
                }

                submitForm();
            });

            vendorSelect.addEventListener('change', () => {
                if (scopeSelect.value === 'vendor' && vendorSelect.value) {
                    submitForm();
                }
            });

            applyVisibility();
            rebuildVendorOptions();

            if (scopeSelect.value !== 'vendor') {
                vendorSelect.value = '';
            }
        });
    </script>
@endif
