@extends('layouts.admin')

@section('title', 'Add Customer')

@section('content')
<div class="bg-white rounded-xl p-6 shadow w-full mx-auto">
    <form action="{{ route('admin.customers.store') }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        <h3 class="text-lg font-semibold mb-4 border-b pb-2 text-green-700">General Details</h3>
        <div class="mb-4">
            <label class="block font-medium mb-1">Upload Profile Image<span class="text-red-500">*</span></label>
            <input type="file" name="avatar" class="border rounded px-3 py-2 w-full">
            @error('avatar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block mb-1">Full Name<span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">Mobile Number<span class="text-red-500">*</span></label>
                <div class="flex">
                    <span class="inline-flex items-center px-2 bg-gray-100 border border-r-0 rounded-l">+91</span>
                    <input type="text" name="phone" value="{{ old('phone') }}" class="border rounded-r px-3 py-2 w-full" required autocomplete="off">
                </div>
                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <!-- Dummy hidden field to trick browser autofill -->
                <input type="text" name="fake_email" id="fake_email" style="display:none" autocomplete="off">
                <label class="block mb-1">Email<span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email') }}" class="border rounded px-3 py-2 w-full" required autocomplete="nope">
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">Street Address<span class="text-red-500">*</span></label>
                <input type="text" name="address" value="{{ old('address') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">Pincode<span class="text-red-500">*</span></label>
                <input type="text" name="pincode" maxlength="6" id="pincodeField" value="{{ old('pincode') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('pincode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">City<span class="text-red-500">*</span></label>
                <input type="text" name="city" id="cityField" value="{{ old('city') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">State<span class="text-red-500">*</span></label>
                <input type="text" name="state" id="stateField" value="{{ old('state') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('state') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
            <div>
                <label class="block mb-1">Country<span class="text-red-500">*</span></label>
                <input type="text" id="countryField" name="country" value="{{ old('country', 'India') }}" class="border rounded px-3 py-2 w-full" required autocomplete="off">
                @error('country') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        <div class="mt-6 border-t pt-4">
            <h4 class="font-semibold mb-2">Create a Password</h4>
            <p class="text-xs text-gray-500 mb-2">If customer can login with email then password field will open while from mobile OTP field will open</p>
            <div>
                <label class="block mb-1">Password<span class="text-red-500">*</span></label>
                <!-- Dummy hidden field to trick browser autofill -->
                <input type="password" name="fake_password" id="fake_password" style="display:none" autocomplete="off">
                <input type="password" name="password" class="border rounded px-3 py-2 w-full" required autocomplete="new-password">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                <p class="text-xs text-gray-400 mt-1">Password should be minimum 4 character</p>
            </div>
        </div>
        <div class="flex justify-end gap-2 mt-8">
            <a href="{{ route('admin.customers.index') }}" class="px-6 py-2 rounded border border-gray-300 bg-white">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded bg-green-600 text-white font-semibold hover:bg-green-700">Save</button>
        </div>
    </form>
</div>
@endsection
<script>
    document.addEventListener("DOMContentLoaded", function(){

    const pincode = document.getElementById('pincodeField');
    const city    = document.getElementById('cityField');
    const state   = document.getElementById('stateField');
    const country = document.getElementById('countryField');

    let typingTimer;

    function toast(type, message){
        if(typeof Swal !== 'undefined') {
            Swal.fire({
             toast: true,
             position: 'top-end',
             icon: type,
             title: message,
             showConfirmButton: false,
             timer: 2500,
             timerProgressBar: true
            });
        } else {
            alert(message);
        }
    }

    if(pincode) {
        pincode.addEventListener('input', function(){

            clearTimeout(typingTimer);
            this.value = this.value.replace(/\D/g,'');
            if(this.value.length !== 6){
            city.value  = '';
            state.value = '';
            country.value = '';
            return;
            }

            typingTimer = setTimeout(() => {

            fetch("https://api.postalpincode.in/pincode/" + this.value)
            .then(res => res.json())
            .then(data => {

                if(data[0].Status === "Success"){

                const postOffice = data[0].PostOffice[0];

                city.value  = postOffice.District;
                state.value = postOffice.State;
                country.value = "India";
                }else{

                city.value  = '';
                state.value = '';
                country.value = '';
                toast('error','Invalid Pincode');
                }
            })
            .catch(() => {
                city.value  = '';
                state.value = '';
                toast('error','Unable to fetch pincode details');
            });

            }, 500);
        });
    }
    });
</script>
