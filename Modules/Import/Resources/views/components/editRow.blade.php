<!-- Edit Row Modal -->
<div id="editRowModal" class="fixed inset-0 z-50 hidden flex items-center justify-center p-4 bg-black bg-opacity-50">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] flex flex-col">
        <div class="py-2 px-5 border-b border-gray-200 flex items-center justify-between">
            <div>
                <h3 class="text-lg font-bold text-gray-900">Edit Hoarding Data</h3>
                <p class="text-sm text-gray-500 mt-0.5">Update the details for this hoarding record</p>
            </div>
            <button onclick="closeEditModal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-5">
            <form id="rowForm" class="space-y-4">
                <input type="hidden" id="rowId">
                <div id="dynamicFieldsGrid" class="grid grid-cols-1 sm:grid-cols-2 gap-4"></div>

                <div id="imageUploadSection">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Hoarding Image</label>
                    <input id="rowImageFile" type="file" accept="image/*"
                        class="w-full min-h-[44px] border border-gray-300 rounded-lg px-3 py-2 text-sm text-gray-600 file:mr-3 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                    <div class="mt-3 flex items-center gap-3">
                        <img id="rowImagePreview" src="" alt="Preview" class="h-16 w-24 object-cover rounded-lg border border-gray-200 hidden" />
                        <span id="rowImagePreviewText" class="text-sm text-gray-400 italic">No image selected</span>
                    </div>
                </div>
            </form>
        </div>

        <div class="p-5 border-t border-gray-200 flex flex-col-reverse sm:flex-row justify-end gap-3">
            <button type="button" onclick="closeEditModal()"
                    class="w-full sm:w-auto min-h-[44px] px-5 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 font-medium transition-colors">
                Cancel
            </button>
            <button type="button" id="saveRowBtn" onclick="document.getElementById('rowForm').requestSubmit()"
                    class="w-full sm:w-auto min-h-[44px] px-5 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Save Changes
            </button>
        </div>
    </div>
</div>

<script>

    const ROW_FIELD_DEFINITIONS = [
    // Core
    { key: 'code',                   label: 'Media Code',              type: 'text',     placeholder: 'e.g. OOH001',            required: false  },
    { key: 'status',                 label: 'Status',                  type: 'select',   options: ['valid', 'invalid'],          required: true  },

    // Address
    { key: 'city',                   label: 'City',                    type: 'text',     placeholder: 'e.g. Delhi',             required: true },
    { key: 'state',                  label: 'State',                   type: 'text',     placeholder: 'e.g. Delhi',             required: true },
    { key: 'locality',               label: 'Locality',                type: 'text',     placeholder: 'e.g. Connaught Place',   required: true },
    { key: 'landmark',               label: 'Landmark',                type: 'text',     placeholder: 'e.g. Near Central Park', required: false },
    { key: 'address',                label: 'Full Address',            type: 'text',     placeholder: 'Full address',           required: false, fullWidth: true },
    { key: 'pincode',                label: 'Pincode',                 type: 'text',     placeholder: 'e.g. 110001',            required: true },

    // Classification
    { key: 'category',               label: 'Media Type / Category',   type: 'text',     placeholder: 'e.g. Billboard',         required: false },
    { key: 'lighting_type',          label: 'Illumination',            type: 'text',     placeholder: 'e.g. Front Lit',         required: false },
    { key: 'screen_type',            label: 'Screen Type',             type: 'text',     placeholder: 'e.g. LED',               required: false },

    // Dimensions
    { key: 'width',                  label: 'Width',                   type: 'number',   placeholder: 'e.g. 20',                required: true },
    { key: 'height',                 label: 'Height',                  type: 'number',   placeholder: 'e.g. 10',                required: true },
    { key: 'measurement_unit',       label: 'Unit',                    type: 'text',     placeholder: 'e.g. ft',                required: false },

    // Geo
    { key: 'latitude',               label: 'Latitude',                type: 'number',   placeholder: 'e.g. 28.6315',           required: true },
    { key: 'longitude',              label: 'Longitude',               type: 'number',   placeholder: 'e.g. 77.2167',           required: true },

    // Pricing
    { key: 'base_monthly_price',     label: 'DCPM / Base Price',       type: 'number',   placeholder: 'e.g. 120000',            required: true },
    { key: 'monthly_price',          label: 'Monthly Sale Price',      type: 'number',   placeholder: 'e.g. 45000',             required: false },
    { key: 'weekly_price_1',         label: 'Weekly Price 1',          type: 'number',   placeholder: 'e.g. 12000',             required: false },
    { key: 'weekly_price_2',         label: 'Weekly Price 2',          type: 'number',   placeholder: 'e.g. 11000',             required: false },
    { key: 'weekly_price_3',         label: 'Weekly Price 3',          type: 'number',   placeholder: 'e.g. 10000',             required: false },
    { key: 'price_per_slot',         label: 'Price Per Spot (₹)',      type: 'number',   placeholder: 'e.g. 250',               required: false },

    // DOOH timing
    { key: 'slot_duration_seconds',  label: 'Ad Duration (Sec)',       type: 'number',   placeholder: 'e.g. 10',                required: true },
    { key: 'screen_run_time',        label: 'Screen Run Time',         type: 'text',     placeholder: 'e.g. 06:00–23:00',       required: true },
    { key: 'total_slots_per_day',    label: 'Spots Per Day',           type: 'number',   placeholder: 'e.g. 120',               required: true },
    { key: 'min_slots_per_day',      label: 'Min Slots Per Day',       type: 'number',   placeholder: 'e.g. 10',                required: true },
    { key: 'daily_play_hours',       label: 'Daily Play Hours',        type: 'text',     placeholder: 'e.g. 18 Hrs',            required: true },

    // Booking
    { key: 'min_booking_duration',   label: 'Minimum Duration (Days)', type: 'number',   placeholder: 'e.g. 30',                required: false },
    { key: 'minimum_booking_amount', label: 'Minimum Booking Amount',  type: 'number',   placeholder: 'e.g. 5000',              required: false },
    { key: 'availability',           label: 'Availability',            type: 'text',     placeholder: 'e.g. Available',         required: false },
    { key: 'commission_percent',     label: 'Commission (%)',          type: 'number',   placeholder: 'e.g. 10',                required: false },

    // Charges
    { key: 'graphics_charge',        label: 'Designing Charge',        type: 'number',   placeholder: 'e.g. 1500',              required: false },
    { key: 'printing_charge',        label: 'Printing Charge',         type: 'number',   placeholder: 'e.g. 3000',              required: false },
    { key: 'mounting_charge',        label: 'Mounting Charge',         type: 'number',   placeholder: 'e.g. 2000',              required: false },
    { key: 'remounting_charge',      label: 'Remounting Charge',       type: 'number',   placeholder: 'e.g. 1500',              required: false },
    { key: 'survey_charge',          label: 'Survey Charge',           type: 'number',   placeholder: 'e.g. 500',               required: false },
    { key: 'lighting_charge',        label: 'Lighting Charge',         type: 'number',   placeholder: 'e.g. 800',               required: false },

    // Discount
    { key: 'discount_type',          label: 'Discount Type',           type: 'text',     placeholder: 'e.g. fixed',             required: false },
    { key: 'discount_value',         label: 'Discount Value',          type: 'number',   placeholder: 'e.g. 5000',              required: false },

    // Other
    { key: 'currency',               label: 'Currency',                type: 'text',     placeholder: 'e.g. INR',               required: false },
    { key: 'available_from',         label: 'Available From',          type: 'date',     placeholder: '',                       required: false },
    { key: 'available_to',           label: 'Available To',            type: 'date',     placeholder: '',                       required: false },

    // Always last
    { key: 'error_message',          label: 'Error Message',           type: 'text',     placeholder: 'Optional error note',    required: false, fullWidth: true },
];


function editRow(rowId) {
    if (currentBatchStatus === 'approved') {
        notify('Approved batch is read-only', 'error');
        return;
    }

    const row = currentRowsById[rowId];
    if (!row) return;

    document.getElementById('rowId').value = row.id;

    const grid = document.getElementById('dynamicFieldsGrid');
    grid.innerHTML = '';

    ROW_FIELD_DEFINITIONS.forEach(fieldDef => {
        const value = row[fieldDef.key];
        const isEmpty = value === null || value === undefined || value === '';

        // code and status always show — everything else only if it has data
        if (!['code', 'status'].includes(fieldDef.key) && isEmpty) {
            return;
        }

        grid.insertAdjacentHTML('beforeend', buildFieldHtml(fieldDef, value));
    });

    // Image
    document.getElementById('rowImageFile').value = '';
    if (row.image_name) {
        const preview = document.getElementById('rowImagePreview');
        preview.src   = imageUrl(row.image_name);
        preview.classList.remove('hidden');
        document.getElementById('rowImagePreviewText').textContent = `Current: ${row.image_name}`;
    } else {
        clearImagePreview();
    }

    openEditModal();
}
</script>