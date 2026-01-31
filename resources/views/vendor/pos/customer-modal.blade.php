<div id="customerModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <div class="fixed inset-0 bg-black/20 backdrop-blur-sm" onclick="closeCustomerModal()"></div>
    
    <div class="flex min-h-full items-center justify-center p-4">
        <div class="relative w-full max-w-5xl rounded-lg bg-white shadow-xl animate-fade-in">
            
            <button onclick="closeCustomerModal()" class="absolute top-4 right-4 text-gray-400 hover:text-black">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>

            <div class="px-8 pt-8 pb-4">
                <h2 class="text-2xl font-semibold text-gray-900 leading-tight">Create Customer Profile</h2>
                <p class="text-gray-500 mt-1">Add new customer details to proceed with offer creation.</p>
            </div>

            <form id="new-customer-form" class="px-8 pb-8">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-3 gap-x-6 gap-y-5">
                    
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Full Name</label>
                        <input type="text" name="name" placeholder="Enter full name" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Business Name</label>
                        <input type="text" name="business_name" placeholder="Enter business name" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">GSTIN<span class="text-red-500">*</span></label>
                        <input type="text" name="gstin" placeholder="Enter email address" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                    </div>

                    <div class="space-y-2 relative">
                        <label class="block text-sm font-medium text-gray-700">Email<span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="email" name="email" placeholder="Enter email address" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                            <!-- <button type="button" class="absolute right-3 top-1/2 -translate-y-1/2 text-blue-400 text-sm hover:underline">Verify</button> -->
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Mobile Number<span class="text-red-500">*</span></label>
                        <div class="flex items-center border border-gray-300 rounded-md overflow-hidden focus-within:border-gray-400">
                            <div class="flex items-center gap-1 px-3 bg-white border-r border-gray-200">
                                <img src="https://flagcdn.com/w20/in.png" srcset="https://flagcdn.com/w40/in.png 2x" width="20" alt="India">
                                <span class="text-sm font-medium text-gray-700">+91</span>
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M19 9l-7 7-7-7"/></svg>
                            </div>
                            <input type="tel" name="phone" placeholder="Enter mobile number" class="w-full px-4 py-3 border-none focus:ring-0 placeholder-gray-400 text-sm">
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Pincode<span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="text" id="pincode" name="pincode" placeholder="Enter pincode" maxlength="6" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                            <div class="absolute right-3 top-1/2 -translate-y-1/2">
                                <div class="w-5 h-5 border border-gray-300 rounded-full flex items-center justify-center text-[10px] text-gray-400 font-serif">i</div>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">City</label>
                        <input type="text" id="city" name="city" placeholder="City" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">State</label>
                        <input type="text" id="state" name="state" placeholder="State" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400">
                    </div>
                    <div class="space-y-2">
                        <label class="block text-sm font-medium text-gray-700">Country</label>
                        <input type="text" id="country" name="country" value="India" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400">
                    </div>
                </div>

                <div class="mt-8">
                    <h4 class="text-lg font-semibold text-gray-900 mb-4">Create Password</h4>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" name="password" placeholder="Enter password" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                        </div>
                        <div class="space-y-2">
                            <label class="block text-sm font-medium text-gray-700">Confirm Password</label>
                            <input type="password" name="password_confirmation" placeholder="Confirm password" class="w-full px-4 py-3 border border-gray-300 rounded-md focus:ring-0 focus:border-gray-400 placeholder-gray-400">
                        </div>
                    </div>
                </div>

                <div class="mt-10 flex justify-end">
                    <button type="submit" class="bg-[#345745] hover:bg-[#2a4537] text-white px-12 py-3 rounded-sm font-medium transition-all shadow-sm">
                        Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<script>

    document.getElementById('pincode').addEventListener('input', async function(e) {
    const pincode = e.target.value;
    const cityInput = document.getElementById('city');
    const stateInput = document.getElementById('state');

    if (pincode.length === 6) {
        try {
            cityInput.placeholder = "Fetching...";
            stateInput.placeholder = "Fetching...";

            const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
            const data = await response.json();

            if (data[0].Status === "Success") {
                const details = data[0].PostOffice[0];
                cityInput.value = details.District;
                stateInput.value = details.State;
            } else {
                console.warn("Pincode not found");
                cityInput.placeholder = "City";
                stateInput.placeholder = "State";
            }
        } catch (error) {
            console.error("Error fetching pincode data:", error);
        }
    } else if (pincode.length < 6) {
        clearAddressFields();
    }
});
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
        let message = err;
        if (err.data) {
            if (err.data.errors) {
                // Laravel validation errors
                message = Object.values(err.data.errors).flat().join('\n');
            } else if (err.data.message) {
                message = err.data.message;
            }
        }
        alert(message);
    } finally {
        submitBtn.disabled = false;
        submitBtn.innerText = originalText;
    }
});
</script>