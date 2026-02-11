@extends('layouts.vendor')

@section('title', 'My Profile')

@section('content')
<div 
    class="max-w-full mx-auto px-2 py-8 space-y-6"
    x-data="{
    showModal: {{ session('reopen_personal_modal') ? 'true' : ($errors->has('current_password') ? 'true' : 'false') }},
    modalType: {{ session('reopen_personal_modal') ? "'personal'" : ($errors->has('current_password') ? "'change-password'" : 'null') }}}">
    {{-- COMMISSION INFO --}}
    <div class="bg-[#e8fff2] rounded-xl shadow p-6">
        <h2 class="text-black text-lg font-semibold">Commission Offered</h2>
        <i class="text-gray-700">The offer is set by OOHAPP</i>
        <h3 class="text-base font-medium">
            Your Commission –
            <span class="text-green-600 font-semibold text-lg">
                {{ $vendor->commission_percentage }}
            </span>
        </h3>
       
    </div>

    {{-- PERSONAL INFO --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Personal Info</h2>
            <button @click="showModal = true; modalType = 'personal'" class="text-blue-600 text-sm">
                Edit
            </button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <div class="flex items-center gap-3 mt-2">
                    @if(auth()->user()->avatar)
                        <img
                            src="{{ route('vendor.view-avatar', auth()->user()->id) }}?t={{ time() }}"
                            alt="Avatar"
                            class="w-17 h-17 rounded-full object-cover border border-gray-300"
                        >
                        <form action="{{ route('vendor.profile.update') }}" method="POST" class="inline" onsubmit="return confirm('Remove avatar?')">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="section" value="remove-avatar">
                            <button type="submit" class="text-red-600 text-xs hover:text-red-700 font-medium">
                                Remove
                            </button>
                        </form>
                    @else
                        <span
                            class="w-16 h-16 flex items-center justify-center
                                text-black text-[10px]
                                border border-gray-200 rounded-full">
                            No avatar
                        </span>
                    @endif
                </div>
            </div>

            <div>
                <label class="text-black">Full Name</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ auth()->user()->name }}
                </p>
            </div>

            <div>
                <label class="text-black">Email Address</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center gap-1">
                    {{ auth()->user()->email }}
                    @if(auth()->user()->email)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
            </div>

            <div>
                <label class="text-black">Mobile Number</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center gap-1">
                    {{ auth()->user()->phone }}
                    @if(auth()->user()->phone)
                        <span class="text-green-600">✔</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="mt-4 text-right">
            <button @click="showModal = true; modalType = 'change-password'" class="text-blue-600 text-sm">
                Change Password
            </button>
        </div>
    </div>

    {{-- BUSINESS DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Business Details</h2>
            <button @click="showModal = true; modalType = 'business'" class="text-blue-600 text-sm">
                Edit
            </button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-black">GSTIN</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->gstin }}
                </p>
            </div>

            <div>
                <label class="text-black">Business Name</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->company_name }}
                </p>
            </div>

            <div>
                <label class="text-black">Business Type</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->company_type }}
                </p>
            </div>

            <div>
                <label class="text-black">PAN</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center justify-between">
                    {{ $vendor->pan ?: ($vendor->user->pan ?? '') }}
                    <button @click="showModal = true; modalType = 'pan'" class="text-blue-600 text-xs">
                        <!-- SVG AS-IT-IS -->
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none"
                             xmlns="http://www.w3.org/2000/svg">
                            <path d="M21.544 11.045C21.848 11.471 22 11.685 22 12C22 12.316 21.848 12.529 21.544 12.955C20.178 14.871 16.689 19 12 19C7.31 19 3.822 14.87 2.456 12.955C2.152 12.529 2 12.315 2 12C2 11.684 2.152 11.471 2.456 11.045C3.822 9.129 7.311 5 12 5C16.69 5 20.178 9.13 21.544 11.045Z"
                                  stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                            <path d="M15 12C15 11.2044 14.6839 10.4413 14.1213 9.87868C13.5587 9.31607 12.7956 9 12 9C11.2044 9 10.4413 9.31607 9.87868 9.87868C9.31607 10.4413 9 11.2044 9 12C9 12.7956 9.31607 13.5587 9.87868 14.1213C10.4413 14.6839 11.2044 15 12 15C12.7956 15 13.5587 14.6839 14.1213 14.1213C14.6839 13.5587 15 12.7956 15 12Z"
                                  stroke="black" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                    </button>
                </p>
            </div>
        </div>
    </div>

    {{-- BANK DETAILS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Bank Details</h2>
            <button @click="showModal = true; modalType = 'bank'" class="text-blue-600 text-sm">
                Edit
            </button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div>
                <label class="text-black">Bank Name</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->bank_name }}
                </p>
            </div>

            <div>
                <label class="text-black">Account Holder</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->account_holder_name }}
                </p>
            </div>

            <div>
                <label class="text-black">Account Number</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->account_number }}
                </p>
            </div>

            <div>
                <label class="text-black">IFSC Code</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->ifsc_code }}
                </p>
            </div>
        </div>
    </div>

    {{-- ADDRESS --}}
    <div class="bg-white rounded-xl shadow p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-semibold">Registered Business Address</h2>
            <button @click="showModal = true; modalType = 'address'" class="text-blue-600 text-sm">
                Edit
            </button>
        </div>

        <div class="grid md:grid-cols-4 gap-4 text-sm">
            <div class="md:col-span-4">
                <label class="text-black">Business Address</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->registered_address }}
                </p>
            </div>

            <div>
                <label class="text-black">Pincode</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->pincode }}
                </p>
            </div>

            <div>
                <label class="text-black">City</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->city }}
                </p>
            </div>

            <div>
                <label class="text-black">State</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $vendor->state }}
                </p>
            </div>

            <div>
                <label class="text-black">Country</label>
                <p class="font-medium border border-gray-200 rounded-md px-3 min-h-[44px] flex items-center">
                    {{ $user->country }}
                </p>
            </div>
        </div>
    </div>

    {{-- DELETE ACCOUNT --}}
    <div class="bg-white rounded-xl shadow p-6 border border-red-200">
        <div class="flex justify-between items-center">
            <div>
                <h2 class="text-lg font-semibold text-red-600">Delete Account</h2>
                <p class="text-sm font-semibold">Account can be deleted only when:</p>
                <ul class="text-sm text-black list-disc ml-5 mt-1">
                    <li>There are no actively pending orders</li>
                    <li>There are no pending settlements</li>
                </ul>
            </div>
            <button @click="showModal = true; modalType = 'delete'" class="text-red-600 text-sm font-medium">
                Delete
            </button>
        </div>
    </div>
     {{-- GLOBAL MODAL --}}
    <div
        x-show="showModal"
        x-cloak
        class="fixed inset-0 z-50 flex items-center justify-center bg-black/40"
        >
        <div class="bg-white w-full max-w-lg rounded-xl p-4 sm:p-6 relative max-h-[90vh] overflow-y-auto mx-auto">

            <button
                @click="showModal=false"
                class="absolute top-3 right-3 text-gray-700 hover:text-black"
            >✕</button>

            <template x-if="modalType === 'personal'">
                @include('vendor.profile.modals.personal')
            </template>

            <template x-if="modalType === 'business'">
                @include('vendor.profile.modals.business')
            </template>

            <template x-if="modalType === 'bank'">
                @include('vendor.profile.modals.bank')
            </template>

            <template x-if="modalType === 'address'">
                @include('vendor.profile.modals.address')
            </template>

            <template x-if="modalType === 'pan'">
                @include('vendor.profile.modals.pan')
            </template>

            <template x-if="modalType === 'delete'">
                @include('vendor.profile.modals.delete-account')
            </template>

            <template x-if="modalType === 'change-password' || {{ $errors->has('current_password') ? 'true' : 'false' }}">
                @include('vendor.profile.modals.change-password')
            </template>


        </div>
    </div>

</div>
<div id="otpModal" class="fixed inset-0 z-[999] hidden items-center justify-center bg-black/40">
    <div class="bg-white rounded-xl shadow-lg w-full max-w-sm p-6 relative">

        <button onclick="closeOtpModal()" class="absolute right-3 top-3 text-gray-600">✕</button>

        <h2 class="text-lg font-semibold text-center mb-1">Verify OTP</h2>
        <p id="otpTargetText" class="text-sm text-gray-500 text-center mb-4"></p>

        <input
            id="otpInput"
            type="text"
            maxlength="4"
            class="w-full border rounded-lg px-4 py-3 text-center text-xl tracking-[6px] outline-none focus:border-blue-500"
            placeholder="----"
        >

        <p id="otpTimer" class="text-xs text-gray-500 text-center mt-3"></p>

        <button
            onclick="verifyOtp()"
            type="button"
            class="w-full mt-4 bg-blue-600 text-white py-2 rounded-lg hover:bg-blue-700"
        >
            Verify OTP
        </button>

        <button
            onclick="resendOtp()"
            id="resendBtn"
            class="w-full mt-2 text-sm text-blue-600 hidden"
            type="button"
        >
            Resend OTP
        </button>

    </div>
</div>
<style>
button {
    cursor: pointer;
}
</style>
@endsection
<script>
    let otpType   = null;
    let otpValue  = null;
    let sending   = false;
    let timerInterval = null;
    let seconds = 60;
    function toast(type, message){
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: type,
            title: message,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true
        });
    }
    function openOtpModal(){
        document.getElementById('otpModal').classList.remove('hidden');
        document.getElementById('otpModal').classList.add('flex');

        document.getElementById('otpInput').value = '';
        document.getElementById('otpInput').focus();

        document.getElementById('otpTargetText').innerText =
            otpType === 'email'
            ? "OTP sent to: " + otpValue
            : "OTP sent to: +91 " + otpValue;
    }
    function closeOtpModal(){
        document.getElementById('otpModal').classList.add('hidden');
        document.getElementById('otpModal').classList.remove('flex');
        clearInterval(timerInterval);
    }
    function startTimer(){
        seconds = 60;
        const timerEl = document.getElementById('otpTimer');
        const resendBtn = document.getElementById('resendBtn');
        resendBtn.classList.add('hidden');

        timerInterval = setInterval(() => {
            seconds--;
            timerEl.innerText = "Resend OTP in " + seconds + " sec";

            if(seconds <= 0){
                clearInterval(timerInterval);
                timerEl.innerText = "";
                resendBtn.classList.remove('hidden');
            }
        }, 1000);
    }
    async function autoSendOtp(type){
        if(sending) return;
        otpType = type;
        otpValue = (type === 'email')
            ? document.getElementById('emailField').value.trim()
            : document.getElementById('phoneField').value.trim();

        if(!otpValue){
            toast('error','Enter value first');
            return;
        }
        sending = true;
        try{
            const res = await fetch("{{ route('vendor.profile.send-otp') }}",{
                method:"POST",
                headers:{
                    "Content-Type":"application/json",
                    "Accept":"application/json",
                    "X-CSRF-TOKEN":"{{ csrf_token() }}"
                },
                body:JSON.stringify({ type:otpType, value:otpValue })
            });

            const data = await res.json();
            sending = false;

            if(data.success){
                toast('success',data.message);
                openOtpModal();
                startTimer();
            }else{
                toast('error',data.message || 'OTP failed');
            }

        }catch(e){
            sending=false;
            toast('error','Connection error');
        }
    }
    function resendOtp(){
        autoSendOtp(otpType);
    }
    async function verifyOtp(){

        const otp = document.getElementById('otpInput').value.trim();

        if(otp.length !== 4){
            toast('error','Enter 4 digit OTP');
            return;
        }

        let data = null;

        try{

            const res = await fetch("{{ route('vendor.profile.verify-otp') }}",{
                method:"POST",
                headers:{
                    "Content-Type":"application/json",
                    "Accept":"application/json",
                    "X-CSRF-TOKEN":"{{ csrf_token() }}"
                },
                body:JSON.stringify({
                    type: otpType,
                    value: otpValue,
                    otp: otp
                })
            });

            // IMPORTANT: read response BEFORE any DOM change
            const text = await res.text();
            data = JSON.parse(text);

        }catch(e){
            console.error(e);
            toast('error','Server response error');
            return;
        }

        // ⚠️ NOW handle UI AFTER response parsed
        setTimeout(() => {

            if(data && data.success){

                toast('success',data.message);

                closeOtpModal();

                if(otpType === 'email'){
                    const tick = document.getElementById('emailVerifiedTick');
                    if(tick) tick.classList.remove('hidden');
                }

                if(otpType === 'phone'){
                    const tick = document.getElementById('phoneVerifiedTick');
                    if(tick) tick.classList.remove('hidden');
                }
                setTimeout(() => {
            window.location.reload();
        }, 800);

            }else{
                toast('error', data?.message || 'Verification failed');
            }

        }, 120);
    }
</script>
