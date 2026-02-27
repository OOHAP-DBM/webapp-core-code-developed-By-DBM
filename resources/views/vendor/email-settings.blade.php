@extends('layouts.vendor')

@section('title', 'Email Settings')

@section('content')
<div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6 mt-8">

    <h2 class="text-2xl font-semibold mb-6">Email Management</h2>
    
    <form id="email-settings-form" method="POST" action="{{ route('vendor.email-settings.update') }}">
        @csrf
        
        <!-- Primary Email Section -->
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
            <label class="block font-semibold mb-2 text-blue-900">
                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Primary Email
            </label>
            <input type="email" 
                   name="primary_email" 
                   value="{{ old('primary_email', auth()->user()->email) }}" 
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:border-transparent" 
                   required
                   readonly>
            <p class="text-sm text-gray-600 mt-1">This is your account email and cannot be changed here.</p>
        </div>

        <!-- Additional Emails Section -->
        <div class="mb-6">
            <div class="flex items-center justify-between mb-3">
                <label class="block font-semibold text-gray-900">
                    <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Additional Emails
                </label>
                <button type="button" 
                        id="add-email-btn" 
                        class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition flex items-center  text-xs md:text-sm">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Email
                </button>
            </div>
            
            <div id="additional-emails-list" class="space-y-3">
                @php
                    $prefs = auth()->user()->vendorProfile?->email_preferences ?? [];
                    $additionalEmails = auth()->user()->vendorProfile?->additional_emails ?? [];
                @endphp
                
                @if(count($additionalEmails) > 0)
                    @foreach($additionalEmails as $email)
                        @php
                            $isVerified = !empty($prefs[$email]['verified']);
                            $notificationEnabled = $prefs[$email]['notifications'] ?? false;
                        @endphp
                        <div class="email-row border border-gray-200 rounded-lg p-4 bg-gray-50">
                            <div class="flex items-start gap-3">
                                <div class="flex-1">
                                    <input type="email" 
                                           name="additional_emails[]" 
                                           value="{{ $email }}" 
                                           class="w-full border border-gray-300 rounded-lg px-2 md:px-4 py-2 mb-2 additional-email-input {{ $isVerified ? 'bg-gray-100' : '' }}"
                                           data-verified="{{ $isVerified ? '1' : '0' }}"
                                           {{ $isVerified ? 'readonly' : '' }}>
                                    
                                    <!-- Notification Toggle -->
                                    <div class="flex items-center mt-2">
                                        <input type="checkbox" 
                                               name="email_notifications[{{ $email }}]" 
                                               value="1"
                                               {{ $notificationEnabled ? 'checked' : '' }}
                                               class="w-4 h-4 text-blue-600 rounded email-notification-checkbox"
                                               {{ !$isVerified ? 'disabled' : '' }}>
                                        <label class="ml-2 text-sm text-gray-700">
                                            Receive notifications on this email
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="flex flex-col gap-2">
                                    @if(!$isVerified)
                                        <button type="button" 
                                                class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm hover:bg-yellow-600 transition verify-btn whitespace-nowrap">
                                            Verify
                                        </button>
                                    @else
                                        <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium verified-label whitespace-nowrap flex items-center">
                                            <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                            </svg>
                                            Verified
                                        </span>
                                    @endif
                                    
                                    <button type="button" 
                                            class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition remove-email whitespace-nowrap">
                                        Remove
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <p class="text-gray-500 text-sm italic">No additional emails added yet.</p>
                @endif
            </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-3  w-30 bg-[#00995c] text-white rounded-lg hover:bg-green-700 transition font-medium">
                Save 
            </button>
        </div>
    </form>
</div>



<div class="max-w-4xl mx-auto bg-white rounded-xl shadow p-6 mt-8">
    <h2 class="text-2xl font-semibold mb-6">Global Notification Preferences</h2>
    <form id="global-notification-form" method="POST" action="{{ route('notification.global-preferences.update') }}">
        @csrf
        <div class="mb-6 p-4 bg-gray-50 border border-gray-200 rounded-lg">
            <div class="space-y-2">
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="notification_email" 
                           value="1" 
                           {{ old('notification_email', auth()->user()->notification_email) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-2 text-gray-700">Email Notifications</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="notification_push" 
                           value="1" 
                           {{ old('notification_push', auth()->user()->notification_push) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-2 text-gray-700">Push Notifications</span>
                </label>
                <label class="flex items-center">
                    <input type="checkbox" 
                           name="notification_whatsapp" 
                           value="1" 
                           {{ old('notification_whatsapp', auth()->user()->notification_whatsapp) ? 'checked' : '' }}
                           class="w-4 h-4 text-blue-600 rounded">
                    <span class="ml-2 text-gray-700">WhatsApp Notifications</span>
                </label>
            </div>
        </div>
        <div class="flex justify-end">
            <button type="submit" 
                    class="px-6 py-3 bg-[#00995c] w-30 text-white rounded-lg hover:bg-green-700 transition font-medium">
                Save
            </button>
        </div>
    </form>
</div>
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let emailCounter = {{ count($additionalEmails) }};

// Add new email row
document.getElementById('add-email-btn').addEventListener('click', function () {
    const container = document.getElementById('additional-emails-list');
    
    // Remove "no emails" message if it exists
    const noEmailsMsg = container.querySelector('p.text-gray-500');
    if (noEmailsMsg) {
        noEmailsMsg.remove();
    }

    const div = document.createElement('div');
    div.className = 'email-row border border-gray-200 rounded-lg p-4 bg-gray-50';

    div.innerHTML = `
        <div class="flex items-start gap-3">
            <div class="flex-1">
                <input type="email"
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 mb-2 additional-email-input"
                    data-verified="0"
                    placeholder="Enter email address">
                
                <!-- Notification Toggle -->
                <div class="flex items-center mt-2">
                    <input type="checkbox" 
                           name="email_notifications_new[]" 
                           value=""
                           class="w-4 h-4 text-blue-600 rounded email-notification-checkbox"
                           disabled>
                    <label class="ml-2 text-sm text-gray-700">
                        Receive notifications on this email
                    </label>
                </div>
            </div>
            
            <div class="flex flex-col gap-2">
                <button type="button" 
                        class="px-4 py-2 bg-yellow-500 text-white rounded-lg text-sm hover:bg-yellow-600 transition verify-btn whitespace-nowrap">
                    Verify
                </button>
                
                <span class="px-4 py-2 bg-green-100 text-green-700 rounded-lg text-sm font-medium verified-label whitespace-nowrap hidden flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Verified
                </span>
                
                <button type="button" 
                        class="px-4 py-2 bg-red-100 text-red-700 rounded-lg text-sm hover:bg-red-200 transition remove-email whitespace-nowrap">
                    Remove
                </button>
            </div>
        </div>
    `;

    container.appendChild(div);
    emailCounter++;

    const input = div.querySelector('.additional-email-input');
    input.focus();
});

// Remove email row
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-email')) {
        const row = e.target.closest('.email-row');
        
        Swal.fire({
            title: 'Remove Email?',
            text: 'Are you sure you want to remove this email?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6b7280',
            confirmButtonText: 'Yes, remove it'
        }).then((result) => {
            if (result.isConfirmed) {
                row.remove();
                
                // Show "no emails" message if no rows left
                const container = document.getElementById('additional-emails-list');
                if (container.querySelectorAll('.email-row').length === 0) {
                    container.innerHTML = '<p class="text-gray-500 text-sm italic">No additional emails added yet.</p>';
                }
            }
        });
    }
});

// Handle Verify button click
document.addEventListener('click', function (e) {
    if (e.target.classList.contains('verify-btn')) {
        const row = e.target.closest('.email-row');
        const input = row.querySelector('.additional-email-input');
        const email = input.value.trim();

        if (!email) {
            Swal.fire({
                icon: 'warning',
                title: 'Email Required',
                text: 'Please enter an email address first.'
            });
            return;
        }

        // Validate email format
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Invalid Email',
                text: 'Please enter a valid email address.'
            });
            return;
        }

        // Check for duplicates
        if (isDuplicateEmail(email, input)) {
            Swal.fire({
                icon: 'error',
                title: 'Duplicate Email',
                text: 'This email is already added.'
            });
            return;
        }

        // Disable button and show loading state
        e.target.disabled = true;
        const originalText = e.target.textContent;
        e.target.innerHTML = `
            <svg class="animate-spin h-4 w-4 inline-block mr-1" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
            </svg>
            Sending...
        `;

        // Send OTP
        sendOTP(email, input, row, e.target, originalText);
    }
});

// Send OTP function
function sendOTP(email, input, row, btn, originalText) {
    fetch("{{ route('vendor.email-settings.send-verification') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ email })
    })
    .then(res => res.json())
    .then(data => {
        // Re-enable button
        btn.disabled = false;
        btn.textContent = originalText;

        if (!data.success) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message || 'Failed to send OTP'
            });
            return;
        }

        // Show OTP modal
        showOTPModal(email, input, row, btn);
    })
    .catch(error => {
        btn.disabled = false;
        btn.textContent = originalText;
        Swal.fire({
            icon: 'error',
            title: 'Network Error',
            text: 'Failed to send verification email. Please try again.'
        });
    });
}

// Show OTP verification modal
function showOTPModal(email, input, row, btn) {
    Swal.fire({
        title: 'Enter Verification Code',
        html: `
            <div class="text-left">
                <p class="text-sm text-gray-600 mb-4">
                    We've sent a 4-digit verification code to<br>
                    <strong class="text-blue-600">${email}</strong>
                </p>
                <p class="text-xs text-gray-500">Please check your inbox and enter the code below. And Click on save changes button to save this email. </p>
            </div>
        `,
        input: 'text',
        inputPlaceholder: '0000',
        showCancelButton: true,
        confirmButtonText: 'Verify',
        cancelButtonText: 'Cancel',
        confirmButtonColor: '#10b981',
        cancelButtonColor: '#6b7280',
        allowOutsideClick: false,
        inputAttributes: {
            maxlength: 4,
            autocomplete: 'off'
        },
        inputValidator: (value) => {
            if (!value) {
                return 'Please enter the verification code';
            }
            if (!/^\d{4}$/.test(value)) {
                return 'Code must be 4 digits';
            }
        },
        preConfirm: (otp) => {
            Swal.showLoading();
            return fetch("{{ route('vendor.email-settings.verify-otp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({ email, otp })
            })
            .then(response => response.json())
            .then(data => {
                if (!data.success) {
                    throw new Error(data.message || 'Invalid verification code');
                }
                return data;
            })
            .catch(error => {
                Swal.showValidationMessage(error.message);
            });
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Mark as verified
            input.setAttribute('name', 'additional_emails[]');
            input.setAttribute('data-verified', '1');
            input.readOnly = true;
            input.classList.add('bg-gray-100');
            
            // Enable notification checkbox and set proper name
            const notificationCheckbox = row.querySelector('.email-notification-checkbox');
            notificationCheckbox.disabled = false;
            notificationCheckbox.setAttribute('name', `email_notifications[${email}]`);
            notificationCheckbox.value = '1';
            
            // Hide verify button and show verified label
            btn.classList.add('hidden');
            row.querySelector('.verified-label').classList.remove('hidden');
            
            Swal.fire({
                icon: 'success',
                title: 'Verified!',
                text: 'Email successfully verified.',
                timer: 2000,
                showConfirmButton: false
            });
        }
    });
}

// Check for duplicate emails
function isDuplicateEmail(email, currentInput) {
    const primary = document.querySelector('input[name="primary_email"]').value.trim().toLowerCase();
    if (email.toLowerCase() === primary) {
        return true;
    }
    
    const additionalInputs = document.querySelectorAll('.additional-email-input');
    for (let input of additionalInputs) {
        if (input === currentInput) continue;
        const val = input.value.trim().toLowerCase();
        if (val && val === email.toLowerCase()) {
            return true;
        }
    }
    
    return false;
}

// Form submission validation
document.getElementById('email-settings-form').addEventListener('submit', function(e) {
    const primary = document.querySelector('input[name="primary_email"]');
    const additionalInputs = document.querySelectorAll('.additional-email-input');

    // Check if primary email is filled
    if (!primary.value.trim()) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Primary email is required.'
        });
        return false;
    }

    // Check additional emails verification and remove empty ones
    const emailsToCheck = [];
    for (let input of additionalInputs) {
        const value = input.value.trim();
        
        // Skip empty inputs
        if (!value) {
            input.closest('.email-row').remove();
            continue;
        }
        
        emailsToCheck.push(input);
        
        // Check if verified
        if (input.getAttribute('data-verified') !== '1') {
            e.preventDefault();
            Swal.fire({
                icon: 'warning',
                title: 'Verification Required',
                text: `Please verify "${value}" before saving.`,
            });
            return false;
        }
    }

    // Duplicate check
    const emails = [];
    if (primary.value.trim()) {
        emails.push(primary.value.trim().toLowerCase());
    }

    emailsToCheck.forEach(function(input) {
        const val = input.value.trim().toLowerCase();
        emails.push(val);
    });

    const unique = new Set(emails);

    if (unique.size !== emails.length) {
        e.preventDefault();
        Swal.fire({
            icon: 'error',
            title: 'Duplicate Emails',
            text: 'Each email must be unique. Please remove duplicates.',
        });
        return false;
    }

    // Show loading state
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.innerHTML = `
        <svg class="animate-spin h-5 w-5 inline-block mr-2" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        Saving...
    `;

    return true;
});

// Show success message
@if(session('success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('success') }}',
        timer: 3000,
        showConfirmButton: false
    });
@endif

// Show error message
@if(session('error'))
    Swal.fire({
        icon: 'error',
        title: 'Error',
        text: '{{ session('error') }}',
    });
@endif

// Show validation errors
@if($errors->any())
    Swal.fire({
        icon: 'error',
        title: 'Validation Error',
        html: '<ul class="text-left">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
    });
@endif
</script>
@endpush
@endsection