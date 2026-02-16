@extends('layouts.admin')

@section('title', 'Add Vendor')

@section('content')
<div class="bg-white rounded-xl p-6 shadow w-full mx-auto">

<form action="{{ route('admin.vendors.store') }}" method="POST" autocomplete="new-password">
@csrf

{{-- Chrome Autofill Blocker --}}
<input type="text" name="fake_username" style="position:absolute;top:-9999px;left:-9999px;" autocomplete="username">
<input type="password" name="fake_password" style="position:absolute;top:-9999px;left:-9999px;" autocomplete="new-password">

<h3 class="text-lg font-semibold mb-4 border-b pb-2 text-green-700">
Vendor Account Details
</h3>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

{{-- Name --}}
<div>
<label class="block mb-1">Vendor Name <span class="text-red-500">*</span></label>
<input type="text"
       name="name"
       value="{{ old('name') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

{{-- Phone --}}
<div>
<label class="block mb-1">Mobile <span class="text-red-500">*</span></label>
<div class="flex">
<span class="inline-flex items-center px-2 bg-gray-100 border border-r-0 rounded-l">+91</span>
<input type="text"
       name="phone"
       value="{{ old('phone') }}"
       class="border rounded-r px-3 py-2 w-full"
       inputmode="numeric"
       autocomplete="off"
       required>
</div>
@error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

{{-- Email --}}
<div>
<label class="block mb-1">Email <span class="text-red-500">*</span></label>
<input type="email"
       name="email"
       value="{{ old('email') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       autocorrect="off"
       autocapitalize="none"
       spellcheck="false"
       required>
@error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

{{-- Password --}}
<div class="md:col-span-3">
<label class="block mb-1">Password <span class="text-red-500">*</span></label>
<input type="password"
       name="password"
       class="border rounded px-3 py-2 w-full"
       autocomplete="new-password"
       readonly
       onfocus="this.removeAttribute('readonly');"
       required>
<p class="text-xs text-gray-400">Vendor can login using this password</p>
@error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

{{-- STATUS TOGGLE --}}
<div class="md:col-span-3">
<label class="block mb-2 font-medium">Vendor Status</label>

<div class="flex items-center gap-6">

<label class="flex items-center gap-2 cursor-pointer">
<input type="radio" name="status" value="active" checked class="accent-green-600 cursor-pointer">
<span class="text-green-600 font-semibold">Active</span>
</label>

<label class="flex items-center gap-2 cursor-pointer">
<input type="radio" name="status" value="inactive" class="accent-red-600 cursor-pointer">
<span class="text-red-600 font-semibold">Inactive</span>
</label>

</div>
@error('status') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

</div>

<h3 class="text-lg font-semibold mt-8 mb-4 border-b pb-2 text-green-700">
Business Details
</h3>

<div class="grid grid-cols-1 md:grid-cols-3 gap-4">

<div>
<label class="block mb-1">Company Name <span class="text-red-500">*</span></label>
<input type="text"
       name="company_name"
       value="{{ old('company_name') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('company_name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block mb-1">GSTIN</label>
<input type="text"
       name="gstin"
       value="{{ old('gstin') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off">
@error('gstin') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block mb-1">PAN</label>
<input type="text"
       name="pan"
       value="{{ old('pan') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off">
@error('pan') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div class="md:col-span-3">
<label class="block mb-1">Registered Address <span class="text-red-500">*</span></label>
<input type="text"
       name="address"
       value="{{ old('address') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block mb-1">City <span class="text-red-500">*</span></label>
<input type="text"
       name="city"
       id="cityField"
       value="{{ old('city') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block mb-1">State <span class="text-red-500">*</span></label>
<input type="text"
       name="state"
       id="stateField"
       value="{{ old('state') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('state') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

<div>
<label class="block mb-1">Pincode <span class="text-red-500">*</span></label>
<input type="text"
       name="pincode"
       maxlength="6"
       id="pincodeField"
       value="{{ old('pincode') }}"
       class="border rounded px-3 py-2 w-full"
       autocomplete="off"
       required>
@error('pincode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
</div>

</div>

<div class="flex justify-end gap-2 mt-8">
<a href="{{ route('admin.vendors.index') }}" class="px-6 py-2 rounded border border-gray-300 bg-white">
Cancel
</a>

<button type="submit" class="px-6 py-2 rounded bg-green-600 text-white font-semibold hover:bg-green-700">
Save Vendor
</button>
</div>

</form>
</div>
@endsection
<script>
       document.addEventListener("DOMContentLoaded", function(){

       const pincode = document.getElementById('pincodeField');
       const city    = document.getElementById('cityField');
       const state   = document.getElementById('stateField');

       let typingTimer;

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

       pincode.addEventListener('input', function(){

              clearTimeout(typingTimer);
              this.value = this.value.replace(/\D/g,'');
              if(this.value.length !== 6){
              city.value  = '';
              state.value = '';
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
                     }else{

                     city.value  = '';
                     state.value = '';
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
       });
</script>
