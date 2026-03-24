<div id="bankModal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="absolute inset-0 bg-black/40 modal-overlay" onclick="closeModal('bankModal')"></div>
    <div class="relative bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 p-6">

        {{-- Header --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-base font-semibold text-gray-900">Bank Details</h3>
            <button type="button" onclick="closeModal('bankModal')" class="text-gray-400 hover:text-gray-600 cursor-pointer">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Body --}}
        <form method="POST" action="{{route('admin.profile.bank.update')}}">
            @csrf
            @method('PUT')
            <div class="space-y-4">
                <div>
                    <label class="block text-sm text-gray-600 mb-1">IFSC Code <span class="text-red-500">*</span></label>
                    <div class="flex items-center gap-2">
                        <input
                            type="text"
                            id="ifsc_code"
                            name="ifsc_code"
                            value="{{ $profile->ifsc_code ?? '' }}"
                            placeholder="Enter IFSC"
                            oninput="handleIfscInput(this)"
                            class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm uppercase focus:outline-none focus:ring-1 focus:ring-green-500">
                    </div>
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Bank Name <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        id="bank_name"
                        name="bank_name"
                        value="{{ $profile->bank_name ?? '' }}"
                        placeholder="Bank Name"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Holder Name <span class="text-red-500">*</span></label>
                    <input type="text" name="account_holder_name" value="{{ $profile->account_holder_name ?? '' }}" placeholder="Enter Name"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>

                <div>
                    <label class="block text-sm text-gray-600 mb-1">Account Number <span class="text-red-500">*</span></label>
                    <input type="text" name="account_number" value="{{ $profile->account_number ?? '' }}" placeholder="Enter Account Number"
                        class="form-input block w-full border border-gray-300 rounded px-3 py-2 text-sm focus:outline-none focus:ring-1 focus:ring-green-500">
                </div>
            </div>

            {{-- Footer --}}
            <div class="flex gap-3 mt-6">
                <button type="button" onclick="closeModal('bankModal')"
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
async function handleIfscInput(input) {
    input.value = input.value.toUpperCase().replace(/[^A-Z0-9]/g, '').slice(0, 11);

    const ifsc = input.value;

    if (ifsc.length < 11) {
        return;
    }

    try {
        const response = await fetch(`https://ifsc.razorpay.com/${ifsc}`);

        if (!response.ok) {
            document.getElementById('bank_name').value = '';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Invalid IFSC code',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            return;
        }

        const data = await response.json();

        if (data && data.BANK) {
            document.getElementById('bank_name').value = data.BANK || '';
        } else {
            document.getElementById('bank_name').value = '';

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: 'Invalid IFSC code',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
        }
    } catch (error) {
        document.getElementById('bank_name').value = '';
        console.error('IFSC fetch error:', error);

        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: 'error',
            title: 'Unable to fetch bank details',
            showConfirmButton: false,
            timer: 3000,
            timerProgressBar: true
        });
    }
}
</script>