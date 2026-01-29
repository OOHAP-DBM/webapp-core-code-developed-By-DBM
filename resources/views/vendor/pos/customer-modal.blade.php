<div id="customerModal" class="fixed inset-0 z-50 hidden">
    <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm" onclick="closeCustomerModal()"></div>
    <div class="relative flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl overflow-hidden animate-slide-up">
            <div class="px-8 py-6 border-b flex justify-between items-center bg-gray-50">
                <div>
                    <h3 class="text-xl font-bold text-gray-800">Create Customer Profile</h3>
                    <p class="text-xs text-gray-500 mt-1">Details will be saved to your vendor database</p>
                </div>
                <button type="button" onclick="closeCustomerModal()" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
            
            <form id="new-customer-form" class="p-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Full Name *</label>
                        <input type="text" name="name" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Business Name</label>
                        <input type="text" name="business_name" class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Email Address *</label>
                        <input type="email" name="email" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Mobile Number *</label>
                        <input type="tel" name="phone" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Password *</label>
                        <input type="password" name="password" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                    <div class="space-y-1">
                        <label class="text-xs font-bold text-gray-600 uppercase">Confirm Password *</label>
                        <input type="password" name="password_confirmation" required class="w-full border-gray-200 rounded-lg text-sm focus:ring-green-500">
                    </div>
                </div>
                <div class="flex justify-end gap-3 border-t pt-6">
                    <button type="button" onclick="closeCustomerModal()" class="px-6 py-2.5 text-sm font-bold text-gray-500">Cancel</button>
                    <button type="submit" class="bg-[#2D5A43] text-white px-10 py-2.5 rounded-lg font-bold shadow-md hover:bg-opacity-90 transition">Create & Select</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
/**
 * Handles the submission of the new customer form inside the modal
 */
document.getElementById('new-customer-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    const submitBtn = e.target.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerText;
    
    try {
        submitBtn.disabled = true;
        submitBtn.innerText = 'Creating...';
        
        const formData = new FormData(e.target);
        const res = await fetchJSON(`${API_URL}/customers`, { 
            method: 'POST', 
            body: JSON.stringify(Object.fromEntries(formData)) 
        });
        
        // selectCustomer is defined in create.blade.php
        selectCustomer(res.data || res); 
        closeCustomerModal();
        e.target.reset();
    } catch (err) {
        console.error(err);
        alert(err.data?.message || "Failed to create customer. Please check if email/phone already exists.");
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});
</script>