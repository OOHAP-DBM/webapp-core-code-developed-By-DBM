<div id="addressModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('addressModal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Registered Business Address</h3>
            <button type="button" onclick="closeModal('addressModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{route('admin.profile.address.update')}}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Country <span class="text-red-500">*</span></label>
                        <input type="text" id="country" name="country" value="{{ $profile->country ?? '' }}"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">Pincode <span class="text-red-500">*</span></label>
                        <input type="text" id="pincode" name="pincode" value="{{ $profile->pincode ?? '' }}" placeholder="Enter Pincode"
                            maxlength="6" oninput="handlePincodeInput(this)"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">State <span class="text-red-500">*</span></label>
                        <input type="text" id="state" name="state" value="{{ $profile->state ?? '' }}"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">City <span class="text-red-500">*</span></label>
                        <input type="text" id="city" name="city" value="{{ $profile->city ?? '' }}"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Business Address <span class="text-red-500">*</span></label>
                    <input type="text" name="business_address" value="{{ $profile->business_address ?? '' }}" placeholder="Enter Business Address"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('addressModal')"
                    class="flex-1 px-4 py-2 text-sm font-medium text-gray-700 border border-gray-300 rounded hover:bg-gray-50 cursor-pointer">
                    Cancel
                </button>
                <button type="submit"
                    class="flex-1 px-4 py-2 text-sm font-medium text-white bg-green-500 rounded hover:bg-green-600 cursor-pointer">
                    Save
                </button>
            </div>
        </form>

    </div>
</div>

<script>
async function handlePincodeInput(input) {
    input.value = input.value.replace(/\D/g, '').slice(0, 6);

    const pincode = input.value;

    if (pincode.length < 6) {
        return;
    }

    try {
        const response = await fetch(`https://api.postalpincode.in/pincode/${pincode}`);
        const data = await response.json();

        if (
            data[0] &&
            data[0].Status === 'Success' &&
            data[0].PostOffice &&
            data[0].PostOffice.length > 0
        ) {
            const postOffice = data[0].PostOffice[0];

            document.getElementById('city').value = postOffice.District || '';
            document.getElementById('state').value = postOffice.State || '';
            document.getElementById('country').value = postOffice.Country || 'India';
        } else {
            document.getElementById('city').value = '';
            document.getElementById('state').value = '';
            document.getElementById('country').value = '';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Invalid pincode',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        console.error('Pincode fetch error:', error);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Unable to fetch pincode details',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}
</script>