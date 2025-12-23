@extends('layouts.app')

@section('title', 'Add User Account Info')

@section('content')
 <meta name="csrf-token" content="{{ csrf_token() }}">

<div class="vendor-page-white">

    <!-- Header -->
    <div class="vendor-header mt-5">
        <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP">
        <span>Vendor</span>
    </div>

    <!-- Stepper -->
    <div class="vendor-signup-wrapper">

        <div class="signup-steps">
            <div class="step active">
                <span>1</span>
                <p>ADD USER ACCOUNT INFO</p>
            </div>
            <div class="line"></div>
            <div class="step">
                <span>2</span>
                <p>ADD BUSINESS INFO</p>
            </div>
        </div>

        <!-- Card -->
        <div class="signup-card">
            <h2>Welcome to OOHAPP</h2>
            <p class="sub-title">Mobile & Email Verification</p>

            @php $user = auth()->user(); @endphp

            <div class="row">

                <!-- PHONE -->
                <div class="col">
                    <label>Mobile</label>
                    <div class="input-with-status">
                        <input type="number"
                            id="phoneInput"
                            placeholder="Enter you phone"
                            value="{{ $user->phone }}"
                            {{ $user->phone_verified_at ? 'readonly' : '' }}>

                        @if($user->phone_verified_at)
                            <span class="verified">Verified</span>
                        @else
                            <button type="button" class="verify-btn" onclick="sendPhoneOtp()">
                                Verify now
                            </button>
                        @endif
                    </div>
                </div>

                <!-- EMAIL -->
                <div class="col">
                    <label>Email</label>
                    <div class="input-with-status">
                        <input type="email"
                            id="emailInput"
                            placeholder="Enter you valid email"
                            value="{{ $user->email }}"
                            {{ $user->email_verified_at ? 'readonly' : '' }}>

                        @if($user->email_verified_at)
                            <span class="verified">Verified</span>
                        @else
                            <button type="button" class="verify-btn" onclick="sendEmailOtp()">
                                Verify now
                            </button>
                        @endif
                    </div>
                </div>

            </div>

            <!-- CONTINUE -->
            <button
                class="continue-btn {{ ($user->email_verified_at && $user->phone_verified_at) ? 'enabled' : '' }}"
                {{ ($user->email_verified_at && $user->phone_verified_at) ? '' : 'disabled' }}
                onclick="goNextStep()"
            >
                Continue
            </button>

        </div>
    </div>
</div>

<!-- OTP MODAL -->
<div id="otpModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:999;">
    <div style="background:#fff; width:360px; margin:120px auto; padding:24px; border-radius:8px;">
        <h4>Enter OTP</h4>

        <input type="text" id="otpInput" maxlength="4"
               style="width:100%; height:42px; text-align:center; font-size:18px;">

        <button onclick="verifyOtp()" class="continue-btn continue-btn-main"
                style="width:100%; margin-top:16px;">
            Verify
        </button>
    </div>
</div>
@endsection


@push('styles')
<style>
/* FORCE WHITE BG ONLY FOR THIS PAGE */
.vendor-page-white {
    background: #ffffff;
    min-height: 100vh;
    padding-bottom: 60px;
}

/* HEADER */
.vendor-header {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 18px 30px;
}
.vendor-header img {
    height: 28px;
}
.vendor-header span {
    font-size: 13px;
    color: #6b7280;
}

/* WRAPPER */
.vendor-signup-wrapper {
    max-width: 900px;
    margin: 20px auto 0;
    font-family: Inter, sans-serif;
}

/* STEPPER */
.signup-steps {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
}
.signup-steps .step {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 13px;
    color: #9ca3af;
}
.signup-steps .step span {
    width: 26px;
    height: 26px;
    border-radius: 50%;
    background: #e5e7eb;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}
.signup-steps .step.active {
    color: #111827;
}
.signup-steps .step.active span {
    background: #22c55e;
    color: #fff;
}
.signup-steps .line {
    flex: 1;
    height: 1px;
    background: #e5e7eb;
    margin: 0 16px;
}

/* CARD */
.signup-card {
    background: #fff;
}
.signup-card h2 {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 5px;
}
.sub-title {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 22px;
}

/* FORM */
.row {
    display: flex;
    gap: 30px;
}
.col {
    flex: 1;
}
label {
    font-size: 13px;
    font-weight: 500;
    margin-bottom: 6px;
    display: block;
}
label span {
    color: red;
}
input {
    width: 100%;
    height: 42px;
    padding: 0 12px;
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 14px;
}

/* VERIFIED */
.input-with-status {
    position: relative;
}
.input-with-status .verified {
    position: absolute;
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 12px;
    font-weight: 500;
    color: #22c55e;
}

/* BUTTON */
.continue-btn {
    margin-top: 28px;
    width: 160px;
    height: 42px;
    border-radius: 6px;
    border: none;
    background: #e5e7eb;
    color: #9ca3af;
}
.input-with-status {
    position: relative;         
    width: 100%;
}

.input-with-status input {
    width: 100%;
    height: 42px;
    padding: 0 90px 0 12px;      
    border: 1px solid #e5e7eb;
    border-radius: 4px;
    font-size: 14px;
}

.verify-btn {
    position: absolute;        
    right: 12px;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #22c55e;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    padding: 0;
    line-height: 1;
}

.verify-btn:hover {
    text-decoration: underline;
}
.continue-btn.enabled {
    background: #22c55e;   /* light green */
    color: #ffffff;
    cursor: pointer;
}

.continue-btn:disabled {
    cursor: not-allowed;
}

/* Main theme color variable */
:root {
    --main-color: #22c55e; /* Change this value to update the main color globally */
}

.continue-btn-main {
    background: var(--main-color) !important;
    color: #fff !important;
    border: none;
    transition: background 0.2s;
}
.continue-btn-main:hover, .continue-btn-main:focus {
    background: #16a34a !important; /* Slightly darker for hover */
    color: #fff !important;
}
</style>
@endpush
@push('scripts')
<script>
    let verifyType = null;
    const csrf = document.querySelector('meta[name="csrf-token"]').content;

    /* ================== SEND PHONE OTP ================== */
    function sendPhoneOtp() {
        verifyType = 'phone';
        const phone = document.getElementById('phoneInput').value.trim();

        if (!phone) {
            alert('Please enter your mobile number');
            return;
        }

        if (!/^\d{10}$/.test(phone)) {
            alert('Please enter a valid 10 digit mobile number');
            return;
        }

        fetch("{{ route('vendor.onboarding.send-phone') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ phone })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            openOtpModal();
        })
        .catch(err => {
            alert(
                err.message ||
                err.errors?.phone?.[0] ||
                'Unable to send OTP'
            );
        });
    }

    /* ================== SEND EMAIL OTP ================== */
    function sendEmailOtp() {
        verifyType = 'email';
        const email = document.getElementById('emailInput').value.trim();

        if (!email) {
            alert('Please enter your email address');
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            alert('Please enter a valid email address');
            return;
        }

        fetch("{{ route('vendor.onboarding.send-email') }}", {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ email })
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            openOtpModal();
        })
        .catch(err => {
            alert(
                err.message ||
                err.errors?.email?.[0] ||
                'Unable to send OTP'
            );
        });
    }

    /* ================== OTP MODAL ================== */
    function openOtpModal() {
        document.getElementById('otpInput').value = '';
        document.getElementById('otpModal').style.display = 'block';
    }

    /* ================== VERIFY OTP ================== */
    function verifyOtp() {
        const otp = document.getElementById('otpInput').value.trim();

        if (!otp) {
            alert('Please enter OTP');
            return;
        }

        if (!/^\d{4}$/.test(otp)) {
            alert('OTP must be exactly 4 digits');
            return;
        }

        let url, payload;

        if (verifyType === 'phone') {
            url = "{{ route('vendor.onboarding.verify-phone') }}";
            payload = {
                phone: document.getElementById('phoneInput').value,
                otp
            };
        } else {
            url = "{{ route('vendor.onboarding.verify-email') }}";
            payload = {
                email: document.getElementById('emailInput').value,
                otp
            };
        }

        fetch(url, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': csrf,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) throw data;
            location.reload(); // Verified badge + Continue enable
        })
        .catch(err => {
            alert(
                err.message ||
                err.errors?.otp?.[0] ||
                'Invalid OTP'
            );
        });
    }
</script>
<script>
function goNextStep() {
    const btn = document.querySelector('.continue-btn');
    if (btn.hasAttribute('disabled')) return;

    window.location.href = "{{ route('vendor.onboarding.business-info') }}";
}
</script>
@endpush

