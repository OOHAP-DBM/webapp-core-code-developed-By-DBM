@extends('layouts.admin')

@section('title', 'Edit Customer')

@section('content')
<div class="bg-white rounded-xl p-6 shadow w-full mx-auto">

    <form action="{{ route('admin.customers.update',$user->id) }}" method="POST" enctype="multipart/form-data" autocomplete="off">
        @csrf
        @method('PUT')

        <h3 class="text-lg font-semibold mb-4 border-b pb-2 text-green-700">General Details</h3>

        {{-- Profile Image --}}
        <div class="mb-4">
            <label class="block font-medium mb-1">Upload Profile Image</label>

            @if($user->avatar)
                <img id="avatarPreview"
                    src="{{ asset('storage/'.$user->avatar) }}"
                    class="w-20 h-20 rounded-lg mb-3 object-cover border">
            @else
                <img id="avatarPreview"
                    src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}"
                    class="w-20 h-20 rounded-lg mb-3 object-cover border">
            @endif

            <input type="file"
                name="avatar"
                id="avatarInput"
                accept="image/*"
                class="border rounded px-3 py-2 w-full">

            @error('avatar') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
        </div>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">

            {{-- Name --}}
            <div>
                <label class="block mb-1">Full Name<span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name',$user->name) }}" class="border rounded px-3 py-2 w-full" required>
                @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Phone --}}
            <div>
                <label class="block mb-1">Mobile Number<span class="text-red-500">*</span></label>
                <div class="flex">
                    <span class="inline-flex items-center px-2 bg-gray-100 border border-r-0 rounded-l">+91</span>
                    <input type="text" name="phone" value="{{ old('phone',$user->phone) }}" class="border rounded-r px-3 py-2 w-full" required>
                </div>
                @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Email --}}
            <div>
                <label class="block mb-1">Email<span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email',$user->email) }}" class="border rounded px-3 py-2 w-full" required>
                @error('email') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Address --}}
            <div>
                <label class="block mb-1">Street Address<span class="text-red-500">*</span></label>
                <input type="text" name="address" value="{{ old('address',$user->address) }}" class="border rounded px-3 py-2 w-full" required>
                @error('address') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Pincode --}}
            <div>
                <label class="block mb-1">Pincode<span class="text-red-500">*</span></label>
                <input type="text" name="pincode" value="{{ old('pincode',$user->pincode) }}" class="border rounded px-3 py-2 w-full" required>
                @error('pincode') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- City --}}
            <div>
                <label class="block mb-1">City<span class="text-red-500">*</span></label>
                <input type="text" name="city" value="{{ old('city',$user->city) }}" class="border rounded px-3 py-2 w-full" required>
                @error('city') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- State --}}
            <div>
                <label class="block mb-1">State<span class="text-red-500">*</span></label>
                <input type="text" name="state" value="{{ old('state',$user->state) }}" class="border rounded px-3 py-2 w-full" required>
                @error('state') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>

            {{-- Country --}}
            <div>
                <label class="block mb-1">Country<span class="text-red-500">*</span></label>
                <input type="text" name="country" value="{{ old('country',$user->country ?? 'India') }}" class="border rounded px-3 py-2 w-full" required>
                @error('country') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>
        {{-- STATUS TOGGLE --}}
        <div class="mt-6 border-t pt-4">
            <h4 class="font-semibold mb-3">Account Status</h4>

            <label class="flex items-center justify-between border rounded-lg px-4 py-3 cursor-pointer">

                <div>
                    <div class="font-medium text-gray-800">Customer Account</div>
                    <div class="text-xs text-gray-500">
                        Disable karne par customer login nahi kar payega
                    </div>
                </div>

                <div class="flex items-center gap-3">

                    {{-- TEXT BADGE --}}
                    <span id="statusText" class="text-sm font-medium">
                        {{ $user->status === 'active' ? 'Active' : 'Inactive' }}
                    </span>

                    {{-- IMPORTANT PART (hidden input) --}}
                    <input type="hidden" name="status" id="statusInput" value="{{ $user->status }}">

                    {{-- TOGGLE --}}
                    <input 
                        type="checkbox"
                        id="statusToggle"
                        class="sr-only peer"
                        {{ $user->status === 'active' ? 'checked' : '' }}
                    >

                    <div class="w-11 h-6 bg-gray-300 rounded-full peer-checked:bg-green-600 relative transition">
                        <div class="absolute left-1 top-1 w-4 h-4 bg-white rounded-full transition-all peer-checked:translate-x-5"></div>
                    </div>

                </div>
            </label>
        </div>

        {{-- Password --}}
        <div class="mt-6 border-t pt-4">
            <h4 class="font-semibold mb-2">Change Password</h4>
            <p class="text-xs text-gray-500 mb-2">Leave blank if you do not want to change password</p>

            <div>
                <label class="block mb-1">New Password</label>
                <input type="password" name="password" class="border rounded px-3 py-2 w-full">
                @error('password') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
            </div>
        </div>

        {{-- Buttons --}}
        <div class="flex justify-end gap-2 mt-8">
            <a href="{{ route('admin.customers.index') }}" class="px-6 py-2 rounded border border-gray-300 bg-white">Cancel</a>
            <button type="submit" class="px-6 py-2 rounded bg-green-600 text-white font-semibold hover:bg-green-700">Update</button>
        </div>

    </form>
</div>
@endsection
<script>
document.addEventListener('DOMContentLoaded', function () {

    const toggle = document.getElementById('statusToggle');
    const input  = document.getElementById('statusInput');
    const text   = document.getElementById('statusText');

    function updateStatus(){
        if(toggle.checked){
            input.value = 'active';
            text.innerText = 'Active';
            text.classList.remove('text-red-600');
            text.classList.add('text-green-600');
        }else{
            input.value = 'inactive';
            text.innerText = 'Inactive';
            text.classList.remove('text-green-600');
            text.classList.add('text-red-600');
        }
    }

    updateStatus(); // page load
    toggle.addEventListener('change', updateStatus);
});
</script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    // STATUS TOGGLE (existing)
    const toggle = document.getElementById('statusToggle');
    const input  = document.getElementById('statusInput');
    const text   = document.getElementById('statusText');

    function updateStatus(){
        if(toggle.checked){
            input.value = 'active';
            text.innerText = 'Active';
            text.classList.remove('text-red-600');
            text.classList.add('text-green-600');
        }else{
            input.value = 'inactive';
            text.innerText = 'Inactive';
            text.classList.remove('text-green-600');
            text.classList.add('text-red-600');
        }
    }

    updateStatus();
    toggle.addEventListener('change', updateStatus);


    // ================= IMAGE LIVE PREVIEW =================

    const avatarInput = document.getElementById('avatarInput');
    const avatarPreview = document.getElementById('avatarPreview');

    if(avatarInput){
        avatarInput.addEventListener('change', function(e){

            const file = e.target.files[0];
            if(!file) return;

            // only image validation
            if(!file.type.startsWith('image/')){
                alert('Please select an image file');
                avatarInput.value = '';
                return;
            }

            const reader = new FileReader();

            reader.onload = function(event){
                avatarPreview.src = event.target.result;
            }

            reader.readAsDataURL(file);
        });
    }

});
</script>
