<div 
    x-show="showModal" 
    x-transition.opacity 
    class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center px-4" 
    @click="if($event.target === $event.currentTarget) closeModal()"
>
    <div class="bg-white w-full max-w-[1280px] max-h-[92vh] rounded shadow-xl flex flex-col" @click.stop>

        {{-- ===== HEADER SECTION ===== --}}
        <div class="flex items-center justify-between px-6 py-4 border-b bg-white">
            <div>
                <h2 class="text-base font-semibold text-gray-900">
                    Enquiry ID
                    <span class="text-green-600" x-text="'SV' + String(enquiryData?.id).padStart(6, '0')"></span>
                </h2>
                <p class="text-xs text-gray-500">Details of enquiry</p>
            </div>

            <div class="flex items-center gap-2">
                {{-- Chat Button --}}
                <!-- <button class="px-4 py-2 text-sm bg-green-50 border border-green-200 text-green-700 rounded">
                    💬 Chat
                </button> -->

                {{-- Create Offer Button --}}
                <!-- <button class="px-4 py-2 text-sm bg-green-700 text-white rounded">
                    + Create Offer
                </button> -->

                {{-- Close Modal Button --}}
                <button @click="closeModal()" class="text-xl text-gray-400 hover:text-gray-600">×</button>
            </div>
        </div>

        {{-- ===== MAIN CONTENT ===== --}}
        <div class="flex-1 overflow-y-auto px-6 py-5 space-y-6">

            {{-- ===== TOP INFO SECTION: 3 Columns ===== --}}
            <div class="grid grid-cols-12 gap-8">

                {{-- Column 1: Customer Details --}}
                <div class="col-span-4">
                    <h3 class="text-sm font-semibold mb-4">Customer Details</h3>
                    <div class="space-y-3 text-xs">
                        <div>Name : <span class="font-medium" x-text="enquiryData?.customer?.name"></span></div>
                        <div>Business Name : <span x-text="enquiryData?.customer?.company_name || 'N/A'"></span></div>
                        <div>GSTIN : <span x-text="enquiryData?.customer?.gstin || 'N/A'"></span></div>
                        <div>Mobile : <span x-text="enquiryData?.customer?.phone"></span></div>
                        <div>Address : <span x-text="enquiryData?.customer?.address"></span></div>
                    </div>
                </div>

                {{-- Column 2: Enquiry Details --}}
                <div class="col-span-4">
                    <h3 class="text-sm font-semibold mb-4">Enquiry Details</h3>
                    <div class="space-y-3 text-xs">
                        <div>Enquiry ID : <span class="font-medium" x-text="'SV' + String(enquiryData?.id).padStart(6, '0')"></span></div>
                        <div>Requested for Month : <span x-text="enquiryData?.budget_period"></span></div>
                        <div>Months Duration : <span x-text="enquiryData?.months_duration + ' Months'"></span></div>
                        <div>Requested Hoardings : <span x-text="enquiryData?.items?.length"></span></div>
                        <template x-if="enquiryData?.customer_note">
                            <div>Requirement : <span class="italic text-gray-700" x-text="enquiryData.customer_note"></span></div>
                        </template>
                    </div>
                </div>

                {{-- Column 3: Received Date --}}
                <div class="col-span-4">
                    <h3 class="text-sm font-semibold mb-4">Received On</h3>
                    <span class="text-lg font-semibold leading-none" x-text="new Date(enquiryData.created_at).getDate()"></span>
                    <span class="text-sm text-gray-500" x-text="new Date(enquiryData.created_at).toLocaleDateString('en-US',{month:'short',year:'2-digit'})"></span>
                </div>
            </div>

            {{-- ===== HOARDINGS SECTION ===== --}}
            <div>
                <h3 class="text-sm font-semibold mb-4">
                    Total Hoardings (<span x-text="enquiryData?.items?.length"></span>)
                </h3>

                {{-- Loop through grouped items by type (OOH, DOOH, etc) --}}
                <template x-for="group in groupItemsByType(enquiryData.items)">
                    <div class="mb-8">
                        
                        {{-- Group Header --}}
                        <div class="flex items-center justify-between bg-gray-100 px-4 py-2 rounded text-sm font-semibold border border-gray-300 mb-3">
                            <div class="flex items-center gap-2">
                                <span class="text-gray-900" x-text="group.type"></span>
                                <span class="text-gray-500 font-normal text-xs" x-text="'(' + group.items.length + ' items)'"></span>
                            </div>
                            <span class="text-xs text-gray-500 font-normal" x-text="group.description"></span>
                        </div>

                        {{-- OOH TABLE LAYOUT --}}
                        <template x-if="group.type === 'OOH'">
                            <div class="overflow-x-auto border border-gray-200 rounded">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="hidden md:table-cell px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Hoarding</th>
                                            <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Selected Package</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Rental</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, i) in group.items" :key="i">
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                {{-- Serial --}}
                                                <td class="px-4 py-3 text-gray-600 font-medium">
                                                    <span x-text="i + 1"></span>
                                                </td>
                                                
                                                {{-- Image --}}
                                                <td class="px-4 py-3 flex gap-1">
                                                    <div class="w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                                        <img
                                                            :src="item.image_url"
                                                            class="w-full h-full object-cover"
                                                            alt="Hoarding"
                                                            onerror="this.style.display='none'"
                                                        >
                                                        <div
                                                            x-show="!item.image_url"
                                                            class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500"
                                                        >
                                                            No Image
                                                        </div>
                                                    </div>                                              
                                                    <div>
                                                        <p class="font-medium text-gray-900" x-text="item?.hoarding?.title || 'N/A'"></p>
                                                        <p class="text-gray-500" x-text="item?.hoarding?.locality || 'N/A'"></p>
                                                        <p class="text-gray-500" x-text="item?.hoarding?.size || ''"></p>
                                                    </div>
                                                </td>
                                                <td class="hidden lg:table-cell px-4 py-3">
                                                    <div class="space-y-1">
                                                        <p class="font-medium text-gray-900">
                                                            <span x-text="'₹' + (item?.price || '0')"></span> 
                                                            <span class="text-gray-500 font-normal" x-text="'for ' + (item?.expected_duration || '-') + ' months'"></span>
                                                            <span class="text-xs text-red-600 font-semibold">Save 30%</span>
                                                        </p>
                                                    </div>
                                                </td>
                                                
                                                {{-- Price --}}
                                                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                    <span x-text="'₹' + (item?.price || '0')"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                        {{-- DOOH/DIGITAL TABLE LAYOUT --}}
                        <template x-if="group.type !== 'OOH'">
                            <div class="overflow-x-auto border border-gray-200 rounded">
                                <table class="w-full text-xs">
                                    <thead class="bg-gray-50 border-b border-gray-200">
                                        <tr>
                                            <th class="hidden md:table-cell px-4 py-3 text-left font-semibold text-gray-700">Sn.</th>
                                            <th class="px-4 py-3 text-left font-semibold text-gray-700">Screen</th>
                                            <th class="hidden lg:table-cell px-4 py-3 text-left font-semibold text-gray-700">Selected Package</th>
                                            <th class="px-4 py-3 text-right font-semibold text-gray-700">Rental</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <template x-for="(item, i) in group.items" :key="i">
                                            <tr class="border-b border-gray-200 hover:bg-gray-50">
                                                {{-- Serial --}}
                                                <td class="hidden md:table-cell px-4 py-3 text-gray-600 font-medium">
                                                    <span x-text="i + 1"></span>
                                                </td>
                                                
                                                {{-- Image --}}
                                                <td class="px-4 py-3 flex gap-1">
                                                    <div class="w-12 h-12 bg-gray-200 rounded overflow-hidden">
                                                        <img
                                                            :src="item.image_url"
                                                            class="w-full h-full object-cover"
                                                            alt="Screen"
                                                            onerror="this.style.display='none'"
                                                        >
                                                        <div
                                                            x-show="!item.image_url"
                                                            class="w-full h-full bg-gray-300 flex items-center justify-center text-[9px] text-gray-500"
                                                        >
                                                            No Image
                                                        </div>
                                                    </div>                                              
                                                    <div>
                                                        <p class="font-medium text-gray-900" x-text="item?.hoarding?.title || 'N/A'"></p>
                                                        <p class="text-gray-500" x-text="item?.hoarding?.locality || 'N/A'"></p>
                                                        <p class="text-gray-500" x-text="item?.hoarding?.size || ''"></p>
                                                    </div>
                                                </td>
                                                <td class="hidden lg:table-cell px-4 py-3">
                                                    <div class="space-y-1">
                                                        <p class="font-medium text-gray-900">
                                                            <span x-text="'₹' + (item?.price || '0')"></span> 
                                                            <span class="text-gray-500 font-normal" x-text="'for ' + (item?.expected_duration || '-') + ' months'"></span>
                                                            <span class="text-xs text-red-600 font-semibold">Save 30%</span>
                                                        </p>
                                                    </div>
                                                </td>
                                                
                                                {{-- Price --}}
                                                <td class="px-4 py-3 text-right font-semibold text-gray-900">
                                                    <span x-text="'₹' + (item?.price || '0')"></span>
                                                </td>
                                            </tr>
                                        </template>
                                    </tbody>
                                </table>
                            </div>
                        </template>

                    </div>
                </template>
            </div>

        </div>

        {{-- ===== FOOTER SECTION ===== --}}
        <div class="border-t bg-gray-50 px-6 py-4 flex justify-end">
            <button 
                @click="closeModal()" 
                class="px-5 py-2 bg-gray-200 text-sm rounded hover:bg-gray-300"
            >
                Close
            </button>
        </div>

    </div>
</div>

<script>

/**
 * Groups hoarding items by their type (OOH, DOOH, etc)
 * 
 * @param {Array} items - Array of enquiry items
 * @returns {Array} Array of grouped items with type and description
 * 
 * Example:
 * groupItemsByType([item1, item2, item3])
 * Returns: [{ type: 'OOH', items: [item1], description: '...' }, ...]
 */
function groupItemsByType(items) {
    if (!items || items.length === 0) return [];
    
    const grouped = {};
    
    items.forEach(item => {
        const type = item?.hoarding_type || 'OOH';
        const fullType = type.toUpperCase();
        
        if (!grouped[fullType]) {
            grouped[fullType] = {
                type: fullType,
                description: getGroupDescription(fullType),
                items: []
            };
        }
        grouped[fullType].items.push(item);
    });
    
    return Object.values(grouped);
}

/**
 * Returns a friendly description for hoarding types
 * 
 * @param {String} type - Type code (OOH, DOOH, etc)
 * @returns {String} Human-readable description
 */
function getGroupDescription(type) {
    const descriptions = {
        'OOH': 'Selected basic hoardings for the offer',
        'DOOH': 'Selected Digital Screens for the offer',
        'DIGITAL-DOOH': 'Selected Digital Screens for the offer',
        'HOARDINGS': 'Selected hoardings for the offer'
    };
    return descriptions[type] || 'Selected hoardings for the offer';
}

/**
 * Gets the image URL for a hoarding item
 * Fallback logic: image_url → hoarding.image → placeholder
 * 
 * @param {Object} item - Enquiry item object
 * @returns {String} Image URL or placeholder
 */
function getHoardingImage(item) {
    if (item?.image_url) return item.image_url;
    if (item?.hoarding?.image) return item.hoarding.image;
    const type = item?.hoarding_type?.toLowerCase() || 'ooh';
    return '/images/placeholder-' + type + '.jpg';
}

/**
 * Returns CSS classes for status badges based on item status
 * Used to color-code status indicators
 * 
 * @param {String} status - Item status (new, offer_send, rejected, etc)
 * @returns {String} Tailwind CSS classes for styling
 */
function getStatusClass(status) {
    const statusMap = {
        'new': 'bg-blue-100 text-blue-600',
        'offer_send': 'bg-orange-100 text-orange-600',
        'offer_reject': 'bg-red-100 text-red-600',
        'offer_accept': 'bg-green-100 text-green-600',
        'quotation_send': 'bg-purple-100 text-purple-600',
        'quotation_reject': 'bg-red-100 text-red-600',
        'quotation_accepted': 'bg-green-100 text-green-600',
        'rejected': 'bg-red-100 text-red-600',
        'accepted': 'bg-green-100 text-green-600'
    };
    return statusMap[status] || 'bg-gray-100 text-gray-600';
}

</script>

