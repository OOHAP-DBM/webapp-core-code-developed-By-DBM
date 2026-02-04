@extends('layouts.customer')

@section('title', 'Personal Information - OOHAPP')
<style>
    .password-toggle {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        cursor: pointer;
        color: #9ca3af;
        font-size: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        z-index: 10;
        pointer-events: auto;
        transition: color 0.2s;
    }
    .password-toggle:hover {
        color: #0a0d11ff;
    }

    /* Modal backdrop blur effect */
    .modal-backdrop {
        transition: backdrop-filter 0.3s ease;
    }

    .modal-backdrop.active {
        backdrop-filter: blur(4px);
    }

    /* Enhanced modal styling */
    .modal-container {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 25px 50px -12px rgba(0, 0, 0, 0.15);
        animation: modalSlideIn 0.3s ease-out;
    }

    @keyframes modalSlideIn {
        from {
            opacity: 0;
            transform: scale(0.95);
        }
        to {
            opacity: 1;
            transform: scale(1);
        }
    }
</style>
@section('content')
<div class="px-6 py-6 flex justify-center">

    {{-- Breadcrumb --}}
    <div class="w-full max-w-none">
        <div class="text-sm text-gray-500 mb-4">
        <a href="{{ route('home') }}" class="text-decoration-none">Home</a>
        >
        <a href="{{ route('customer.dashboard') }}" class="text-decoration-none">My Account</a>
        >
        Profile
        >
        <span class="text-gray-800">Personal Information</span>
    </div>
    {{-- Card --}}
    <div class="bg-white rounded-lg border border-gray-200 p-6 w-full">

        <h2 class="text-lg font-semibold text-gray-800 mb-1">
            Personal Information
        </h2>

        <p class="text-sm text-gray-500 mb-6">
            Hey! Please provide your information to create a customized OOHAPP experience.
        </p>

        <form action="{{ route('customer.profile.update') }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Profile Picture --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Profile picture
                </label>

                @if(auth()->user()->avatar)
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-3">
                                <div style="width:56px; height:56px; min-width:56px; min-height:56px; max-width:56px; max-height:56px; border-radius:50%; overflow:hidden;border:1px solid #d1d5db;flex-shrink:0;display:inline-block;">
                                    <img
                                        src="{{ str_starts_with(auth()->user()->avatar, 'http')
                                                ? auth()->user()->avatar
                                                : asset('storage/' . ltrim(auth()->user()->avatar, '/')) }}"
                                        alt="Profile Image"
                                        class="w-full h-full object-cover block"
                                    >
                                </div>
                            <a href="javascript:void(0)"
                                onclick="removeAvatar()"
                                class="text-sm text-red-500 hover:underline">
                                    remove
                            </a>
                        </div>
                    </div>
                @else
                <div class="flex items-center gap-4">
                    <label for="profileImage" class="text-sm text-blue-600 hover:underline cursor-pointer">
                        Upload
                    </label>
                    <input type="file" id="profileImage" name="avatar" accept="image/*" class="hidden">
                </div>
                @endif
            </div>

            {{-- Full Name --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Full Name <span class="text-red-500">*</span>
                </label>

                <input
                    type="text"
                    name="name"
                    value="{{ old('name', auth()->user()->name) }}"
                    class="w-full border-b border-gray-300 focus:border-green-500 focus:outline-none py-2"
                    required
                >
            </div>
            <div class="flex items-center justify-between gap-4">
                    <input
                        type="text"
                        id="phoneInput"
                        name="phone"
                        value="{{ auth()->user()->phone }}"
                        {{ auth()->user()->phone ? 'readonly' : '' }}
                        placeholder="Enter mobile number"
                        class="w-full border-b border-gray-300 py-2 bg-transparent"
                    >

                    @if(auth()->user()->phone_verified_at)
                        <span class="text-green-600 text-sm">✔</span>
                    @else
                        <a href="javascript:void(0)"
                        class="text-sm text-blue-600 hover:underline whitespace-nowrap"
                        onclick="sendOtp('phone')">
                            Verify Now
                        </a>
                    @endif
            </div>
             <div class="flex items-center justify-between gap-4">
                <input
                    type="email"
                    id="emailInput"
                    name="email"
                    value="{{ auth()->user()->email }}"
                    {{ auth()->user()->email ? 'readonly' : '' }}
                    placeholder="Enter email address"
                    class="w-full border-b border-gray-300 py-2 bg-transparent"
                >

                @if(auth()->user()->email_verified_at)
                    <span class="text-green-600 text-sm">✔</span>
                @else
                    <a href="javascript:void(0)"
                    class="text-sm text-blue-600 hover:underline whitespace-nowrap"
                    onclick="sendOtp('email')">
                        Verify Now
                    </a>
                @endif
            </div>


            {{-- Password --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Password
                </label>

                @if(auth()->user()->password)
                    <div class="flex items-center justify-between gap-4">
                        <input
                            type="text"
                            value="***********"
                            disabled
                            class="w-full border-b border-gray-300 py-2 bg-gray-50"
                        >
                        <a href="javascript:void(0)"
                            onclick="openChangePasswordModal()"
                            class="text-sm text-blue-600 hover:underline whitespace-nowrap">
                                Change Password
                        </a>
                    </div>
                @else
                    <input
                        type="password"
                        name="password"
                        placeholder="Enter Password"
                        class="w-full border-b border-gray-300 focus:border-green-500 focus:outline-none py-2"
                    >
                @endif
            </div>

            {{-- Company Name --}}
            <div class="mb-5">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Company Name
                </label>

                <input
                    type="text"
                    name="company_name"
                    value="{{ old('company_name', auth()->user()->company_name) }}"
                    placeholder="Enter Company Name"
                    class="w-full border-b border-gray-300 focus:border-green-500 focus:outline-none py-2"
                >
            </div>

            {{-- GSTIN --}}
            <div class="mb-8">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    GSTIN Number
                </label>

                <input
                    type="text"
                    name="gstin"
                    value="{{ old('gstin', auth()->user()->gstin) }}"
                    placeholder="Enter GSTIN"
                    class="w-full border-b border-gray-300 focus:border-green-500 focus:outline-none py-2"
                >
            </div>

            {{-- Submit --}}
            <div class="text-right">
                <button
                    type="submit"
                    class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-md text-sm font-medium">
                    Update
                </button>
            </div>
        </form>
    </div>
</div>
{{-- Change Password Modal --}}
<div id="changePasswordModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg w-100 max-w-lg p-6 relative border border-gray-300 modal-container">

        <button onclick="closeChangePasswordModal()"
                class="absolute cursor-pointer top-2 right-3 text-gray-800 text-xl hover:text-gray-500">×</button>

        <h3 class="text-lg font-semibold mb-4">Change Password</h3>

        <div class="space-y-4">
            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Current Password
                </label>
                <input
                    type="password"
                    id="currentPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Enter current password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('currentPassword'); return false;">
                    <i id="currentPasswordIcon" class="bi bi-eye mt-6"></i>
                </button>
            </div>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    New Password
                </label>
                <input
                    type="password"
                    id="newPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Enter new password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('newPassword'); return false;">
                    <i id="newPasswordIcon" class="bi bi-eye  mt-6"></i>
                </button>
            </div>

            <div class="relative">
                <label class="block text-sm font-medium text-gray-700 mb-1">
                    Confirm Password
                </label>
                <input
                    type="password"
                    id="confirmPassword"
                    class="w-full border border-gray-300 rounded py-2 px-3 pr-10 focus:border-green-500 focus:outline-none"
                    placeholder="Confirm new password"
                >
                <button type="button" class="password-toggle" onclick="togglePasswordVisibility('confirmPassword'); return false;">
                    <i id="confirmPasswordIcon" class="bi bi-eye  mt-6"></i>
                </button>
            </div>

            <div id="passwordError" class="text-red-500 text-sm hidden"></div>
        </div>

        <div class="flex gap-3 mt-6">
            <button
                onclick="closeChangePasswordModal()"
                class="flex-1 border border-gray-300 text-gray-700 py-2 rounded hover:bg-gray-50 cursor-pointer">
                Cancel
            </button>
            <button
                onclick="submitPasswordChange()"
                class="flex-1 bg-green-500 hover:bg-green-600 text-white py-2 rounded cursor-pointer">
                Update Password
            </button>
        </div>
    </div>
</div>
{{-- Otp Verify Modal --}}
<div id="otpModal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50 modal-backdrop">
    <div class="bg-white rounded-lg w-96 p-6 relative border border-gray-300 modal-container">

        <button onclick="closeOtpModal()"
                class="absolute top-2 right-3 text-gray-500 text-xl">×</button>

        <h3 class="text-lg font-semibold mb-2">Verify</h3>

        <p class="text-sm text-gray-500 mb-4">
            Enter the 4-digit code sent to
            <span id="otpTarget" class="font-medium"></span>
        </p>

        <input
            type="text"
            id="otpInput"
            maxlength="4"
            class="w-full text-center tracking-widest text-lg
                   border border-gray-300 rounded py-2 mb-4"
            placeholder="____"
        >

        <button
            onclick="verifyOtp()"
            class="w-full bg-green-500 hover:bg-green-600 text-white py-2 rounded">
            Verify
        </button>

        <p class="text-center text-xs text-gray-400 mt-3">
            Didn’t receive code?
            <a href="#" onclick="resendOtp()" class="text-green-600">Resend</a>
        </p>
    </div>
</div>
@endsection
<script>
    /* ===============================
    GLOBAL STATE
    ================================ */
    let currentIdentifier = '';
    let otpTimer = null;

    /* ===============================
    INPUT VALIDATIONS
    ================================ */

    // only numbers in phone & otp
    document.addEventListener('input', function (e) {
        if (e.target.id === 'phoneInput') {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 10);
        }

        if (e.target.id === 'otpInput') {
            e.target.value = e.target.value.replace(/\D/g, '').slice(0, 4);
        }
    });

    /* ===============================
    SEND OTP (FIXED)
    ================================ */
    function sendOtp(type) {

        let identifier = '';

        if (type === 'phone') {
            identifier = document.getElementById('phoneInput').value.trim();

            if (!identifier) {
                alert('Enter mobile number');
                return;
            }

            if (identifier.length !== 10) {
                alert('Enter valid 10 digit mobile number');
                return;
            }
        }

        if (type === 'email') {
            identifier = document.getElementById('emailInput').value.trim();

            if (!identifier) {
                alert('Enter email address');
                return;
            }

            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(identifier)) {
                alert('Invalid email address');
                return;
            }
        }

        fetch("{{ route('customer.profile.send-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ identifier })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alert(res.message);
                return;
            }

            openOtpModal(identifier);
        });
    }

    /* ===============================
    OTP MODAL
    ================================ */
    function openOtpModal(identifier) {
        currentIdentifier = identifier;

        document.getElementById('otpTarget').innerText = identifier;
        document.getElementById('otpInput').value = '';

        const modal = document.getElementById('otpModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');

        // Add blur effect to backdrop
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);

        document.getElementById('otpInput').focus();
    }

    function closeOtpModal() {
        const modal = document.getElementById('otpModal');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
    }

    /* ===============================
    VERIFY OTP
    ================================ */
    function verifyOtp() {

        const otp = document.getElementById('otpInput').value.trim();

        if (otp.length !== 4) {
            alert('Enter 4 digit OTP');
            return;
        }

        fetch("{{ route('customer.profile.verify-otp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                identifier: currentIdentifier,
                otp: otp
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                alert(res.message);
                return;
            }

            closeOtpModal();
            location.reload(); // ✔ refresh verified UI
        });
    }

    /* ===============================
    RESEND OTP
    ================================ */
    function resendOtp() {
        sendOtp(
            currentIdentifier.includes('@') ? 'email' : 'phone'
        );
    }
    function removeAvatar() {

        if (!confirm('Remove profile picture?')) return;

        fetch("{{ route('customer.profile.avatar.remove') }}", {
            method: "DELETE",
            headers: {
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            }
        })
        .then(res => {
            if (!res.ok) {
                throw new Error('Request failed');
            }
            return res.json();
        })
        .then(res => {
            if (res.success) {
                location.reload(); // ✅ auto reload
            } else {
                alert(res.message || 'Something went wrong');
            }
        })
        .catch(() => {
            alert('Server error');
        });
    }

    /* ===============================
    CHANGE PASSWORD MODAL
    ================================ */
    function openChangePasswordModal() {
        // Clear form fields
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
        document.getElementById('passwordError').classList.add('hidden');
        
        const modal = document.getElementById('changePasswordModal');
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        
        // Add blur effect to backdrop
        setTimeout(() => {
            modal.classList.add('active');
        }, 10);
        
        document.getElementById('currentPassword').focus();
    }

    function closeChangePasswordModal() {
        const modal = document.getElementById('changePasswordModal');
        modal.classList.remove('active');
        
        setTimeout(() => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }, 300);
        
        // Clear form
        document.getElementById('currentPassword').value = '';
        document.getElementById('newPassword').value = '';
        document.getElementById('confirmPassword').value = '';
    }

    function togglePasswordVisibility(fieldId) {
        const field = document.getElementById(fieldId);
        const iconId = fieldId + 'Icon';
        const icon = document.getElementById(iconId);
        
        if (field.type === 'password') {
            field.type = 'text';
            if (icon) {
                icon.classList.remove('bi-eye');
                icon.classList.add('bi-eye-slash');
            }
        } else {
            field.type = 'password';
            if (icon) {
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
            }
        }
    }

    function submitPasswordChange() {
        const currentPassword = document.getElementById('currentPassword').value.trim();
        const newPassword = document.getElementById('newPassword').value.trim();
        const confirmPassword = document.getElementById('confirmPassword').value.trim();
        const errorDiv = document.getElementById('passwordError');

        // Validation
        if (!currentPassword) {
            errorDiv.innerText = 'Current password is required';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (!newPassword) {
            errorDiv.innerText = 'New password is required';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (newPassword.length < 6) {
            errorDiv.innerText = 'Password must be at least 6 characters';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (newPassword !== confirmPassword) {
            errorDiv.innerText = 'Passwords do not match';
            errorDiv.classList.remove('hidden');
            return;
        }

        if (currentPassword === newPassword) {
            errorDiv.innerText = 'New password must be different from current password';
            errorDiv.classList.remove('hidden');
            return;
        }

        // Submit password change
        fetch("{{ route('customer.profile.change-password') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                current_password: currentPassword,
                new_password: newPassword,
                new_password_confirmation: confirmPassword
            })
        })
        .then(res => res.json())
        .then(res => {
            if (!res.success) {
                errorDiv.innerText = res.message || 'Failed to update password';
                errorDiv.classList.remove('hidden');
                return;
            }

            alert('Password updated successfully!');
            closeChangePasswordModal();
        })
        .catch(err => {
            errorDiv.innerText = 'Server error. Please try again.';
            errorDiv.classList.remove('hidden');
        });
    }
</script>