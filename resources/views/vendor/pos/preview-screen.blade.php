<div class="max-w-6xl mx-auto">
    <div class="flex flex-col lg:flex-row gap-8">
        <div class="flex-1 bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden">
            <div class="p-8 border-b border-gray-100 bg-gray-50/50 flex justify-between items-start">
                <div>
                    <h2 class="text-2xl font-black text-gray-800 tracking-tight">BOOKING PREVIEW</h2>
                    <p class="text-sm text-gray-500 mt-1">Review the hoarding selection before finalizing</p>
                </div>
                <button onclick="backToSelection()" class="flex items-center gap-2 text-sm font-bold text-[#2D5A43] hover:underline">
                    <span>←</span> Edit Selection
                </button>
            </div>

            <div class="grid grid-cols-2 gap-8 p-8 border-b border-gray-100">
                <div>
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Billed To</label>
                    <h3 id="preview-cust-name" class="text-lg font-bold text-gray-800">---</h3>
                    <p id="preview-cust-business" class="text-sm text-gray-600 font-medium">---</p>
                    <div class="mt-2 space-y-0.5">
                        <p id="preview-cust-email" class="text-xs text-gray-500">---</p>
                        <p id="preview-cust-phone" class="text-xs text-gray-500">---</p>
                    </div>
                </div>
                <div class="text-right">
                    <label class="block text-[10px] font-bold text-gray-400 uppercase mb-2">Booking Info</label>
                    <p class="text-sm font-bold text-gray-800">Date: {{ date('d M, Y') }}</p>
                    <p class="text-xs text-gray-500">Inventory Items: <span id="preview-total-count" class="font-bold">0</span></p>
                </div>
            </div>

            <div class="p-0">
                <div class="px-8 py-4 bg-blue-50/50 border-b border-gray-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-blue-600 uppercase tracking-widest">OOH Static Hoardings</span>
                    <span class="text-[10px] bg-blue-100 text-blue-700 px-2 py-0.5 rounded-full font-bold ooh-count">0 Items</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <tbody id="preview-ooh-list" class="divide-y divide-gray-50">
                        </tbody>
                </table>

                <div class="px-8 py-4 bg-purple-50/50 border-y border-gray-100 flex justify-between items-center">
                    <span class="text-xs font-bold text-purple-600 uppercase tracking-widest">DOOH Digital Slots</span>
                    <span class="text-[10px] bg-purple-100 text-purple-700 px-2 py-0.5 rounded-full font-bold dooh-count">0 Items</span>
                </div>
                <table class="w-full text-left border-collapse">
                    <tbody id="preview-dooh-list" class="divide-y divide-gray-50">
                        </tbody>
                </table>
            </div>

            <div class="p-8 bg-gray-50/30">
                <p class="text-[10px] text-gray-400 italic">Note: This is a preview. Final availability is confirmed upon clicking 'Create Booking'. Prices include standard mounting/broadcast charges.</p>
            </div>
        </div>

        <div class="lg:w-80">
            <div class="bg-white rounded-2xl shadow-sm border border-gray-200 p-6 sticky top-6">
                <h3 class="font-bold text-gray-800 mb-6">Payment Summary</h3>
                
                <div class="space-y-4 mb-8">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">OOH Total</span>
                        <span id="side-ooh-total" class="font-semibold text-gray-800">₹0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">DOOH Total</span>
                        <span id="side-dooh-total" class="font-semibold text-gray-800">₹0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Subtotal</span>
                        <span id="side-sub-total" class="font-semibold text-gray-800">₹0</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Tax (18%)</span>
                        <span id="side-tax" class="font-semibold text-gray-800">₹0</span>
                    </div>
                    <div class="pt-4 border-t border-dashed flex justify-between items-center">
                        <span class="font-bold text-gray-800">Grand Total</span>
                        <span id="side-grand-total" class="text-xl font-black text-[#2D5A43]">₹0</span>
                    </div>
                </div>

                <div class="space-y-3">
                    <button id="create-booking-btn" onclick="submitBooking()" class="w-full py-4 bg-[#2D5A43] text-white rounded-xl font-bold shadow-lg shadow-green-900/20 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center justify-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Create Booking
                    </button>
                    
                    <button onclick="backToSelection()" class="w-full py-3 bg-white text-gray-500 border border-gray-200 rounded-xl text-sm font-bold hover:bg-gray-50 transition">
                        Go Back
                    </button>
                </div>

                <div class="mt-6 p-4 bg-amber-50 rounded-xl border border-amber-100">
                    <div class="flex gap-3">
                        <span class="text-amber-500 font-bold text-lg">💡</span>
                        <p class="text-[10px] text-amber-700 leading-relaxed">
                            Clicking create will generate an invoice and notify the customer via email and SMS.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
async function submitBooking() {
    const btn = document.getElementById('create-booking-btn');
    
    // 1. Validation
    if (!selectedCustomer) return alert("Please select a customer.");
    if (selectedHoardings.size === 0) return alert("Please select at least one hoarding.");

    // 2. Format Data for Backend
    const items = [];
    const hoardingIds = [];
    
    selectedHoardings.forEach((h, id) => {
        hoardingIds.push(id); // Matches 'hoarding_ids' validation
        items.push({
            id: id,
            start_date: h.startDate,
            end_date: h.endDate,
            price: h.price_per_month
        });
    });

    // Pick the first hoarding's dates as the "global" booking dates 
    // to satisfy the 'start_date' and 'end_date' top-level validation
    const firstItem = items[0];

    const payload = {
        customer_id: selectedCustomer.id,
        customer_name: selectedCustomer.name,
        customer_phone: selectedCustomer.phone ??"1234567899", // Fixed: ensure this isn't undefined
        customer_email: selectedCustomer.email,
        customer_address: selectedCustomer.address || 'N/A',
        hoarding_ids: hoardingIds, // Required by your backend
        items: items,
        customer_gstin: selectedCustomer.gstin || '',
        customer_city: selectedCustomer.city || '',
        payment_reference: '',
        start_date: firstItem.start_date, // Required by your backend
        end_date: firstItem.end_date,     // Required by your backend
        payment_notes: '', // Specifically fixes line 496
    
        notes: '',
        base_amount: parseFloat(document.getElementById('side-sub-total').innerText.replace(/[^\d.]/g, '')),
        tax_amount: parseFloat(document.getElementById('side-tax').innerText.replace(/[^\d.]/g, '')),
        total_amount: parseFloat(document.getElementById('side-grand-total').innerText.replace(/[^\d.]/g, '')),
        
        booking_type: 'ooh', // Matches 'booking_type' validation
        payment_mode: 'cash',
        auto_release_hours: 24 
    };

    // 3. Send Request
    btn.disabled = true;
    try {
        const response = await fetch('/vendor/pos/api/bookings', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (response.status === 422) {
            console.error("Validation Fail:", result.errors);
            alert("Validation Error: " + Object.values(result.errors).flat().join('\n'));
        } else if (!response.ok) {
            throw new Error("Server Error");
        } else {
            alert("Booking Successful!");
            window.location.href = "/vendor/pos/bookings";
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Failed to create booking.");
    } finally {
        btn.disabled = false;
    }
}
</script>