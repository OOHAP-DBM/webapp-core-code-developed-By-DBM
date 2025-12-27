@extends('layouts.app')

@section('title', 'Vendor Onboarding – Account Info')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">

<div class="vendor-page-white">

    <!-- Header -->
    <div class="vendor-header mt-5">
        <img src="{{ asset('assets/images/logo/logo_image.jpeg') }}" alt="OOHAPP">
        <span>Vendor</span>
    </div>

    <!-- Wrapper -->
    <div class="vendor-signup-wrapper">

        <!-- Stepper -->
        <div class="signup-steps">
            <div class="step active">
                <span>1</span>
                <p>ACCOUNT INFO</p>
            </div>
            <div class="line"></div>
            <div class="step">
                <span>2</span>
                <p>BUSINESS INFO</p>
            </div>
        </div>

        <!-- Card -->
        <div class="signup-card">
            <h2>Welcome to OOHAPP</h2>
            <p class="sub-title">Mobile & Email Verification (Optional)</p>

            @php $user = auth()->user(); @endphp

            <div class="row">

                <!-- PHONE -->
                <div class="col">
                    <label>Mobile</label>
                    <div class="input-with-status">
                        <input type="number"
                               id="phoneInput"
                               placeholder="Enter mobile number"
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
                               placeholder="Enter email"
                               value="{{ $user->email }}"
                               {{ $user->email_verified_at ? 'readonly' : '' }}>

                        @if($user->email_verified_at)
                            <span class="verified">Verified</span>
                        @else
                            <button type="button" class="verify-btn" onclick="sendEmailOtp()">
                                Verify
                            </button>
                        @endif
                    </div>
                </div>

            </div>

            <!-- ACTIONS -->
            <div style="margin-top:30px;">
                <button class="continue-btn continue-btn-main" onclick="skipAndContinue()">
                    Skip & Continue
                </button>

                <p class="skip-note">
                    You can verify your mobile and email later from your profile.
                </p>
            </div>

        </div>
    </div>
</div>


<!-- OTP MODAL -->
<div id="otpModal" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,.4); z-index:999;">
    <div style="background:#fff; width:360px; margin:120px auto; padding:24px; border-radius:8px;">
        <h4>Enter OTP</h4>

        <input type="text" id="otpInput" maxlength="4"
               style="width:100%; height:42px; text-align:center; font-size:18px; border:1.5px solid #e5e7eb; border-radius:4px;">

        <button onclick="verifyOtp()" class="continue-btn continue-btn-main"
                style="width:100%; margin-top:16px;">
            Verify
        </button>
    </div>
</div>
@endsection
@push('styles')
<style>
.vendor-page-white { background:#fff; min-height:100vh; padding-bottom:60px; }
.vendor-header { display:flex; align-items:center; gap:8px; padding:18px 30px; }
.vendor-header img { height:28px; }
.vendor-header span { font-size:13px; color:#6b7280; }

.vendor-signup-wrapper { max-width:900px; margin:20px auto; font-family:Inter,sans-serif; }

.signup-steps { display:flex; align-items:center; margin-bottom:30px; }
.step { display:flex; align-items:center; gap:8px; font-size:13px; color:#9ca3af; }
.step span { width:26px; height:26px; border-radius:50%; background:#e5e7eb; display:flex; align-items:center; justify-content:center; }
.step.active { color:#111827; }
.step.active span { background:#22c55e; color:#fff; }
.line { flex:1; height:1px; background:#e5e7eb; margin:0 16px; }

.signup-card h2 { font-size:22px; font-weight:600; }
.sub-title { font-size:14px; color:#6b7280; margin-bottom:22px; }

.row { display:flex; gap:30px; }
.col { flex:1; }
label { font-size:13px; font-weight:500; margin-bottom:6px; display:block; }

.input-with-status { position:relative; }
.input-with-status input {
    width:100%; height:42px; padding:0 90px 0 12px;
    border:1px solid #e5e7eb; border-radius:4px;
}

.verify-btn {
    position:absolute; right:12px; top:50%;
    transform:translateY(-50%);
    background:none; border:none;
    color:#22c55e; font-size:12px; cursor:pointer;
}

.verified { position:absolute; right:12px; top:50%; transform:translateY(-50%); font-size:12px; color:#22c55e; }

.continue-btn {
    height:42px; padding:0 24px;
    border-radius:6px; border:none;
    background:#22c55e; color:#fff;
    cursor:pointer;
}

.full { width:100%; margin-top:16px; }

.skip-note {
    font-size:12px; color:#6b7280;
    margin-top:8px;
}

.otp-modal {
    display:none; position:fixed; inset:0;
    background:rgba(0,0,0,.4); z-index:999;
}

.otp-card {
    background:#fff; width:360px;
    margin:120px auto; padding:24px;
    border-radius:8px;
}
</style>
@endpush
@push('scripts')
<script>
let verifyType = null;
const csrf = document.querySelector('meta[name="csrf-token"]').content;

/* ================= OTP SEND ================= */
function sendPhoneOtp() {
    verifyType = 'phone';
    sendOtp("{{ route('vendor.onboarding.send-phone') }}", {
        phone: phoneInput.value
    });
}

function sendEmailOtp() {
    verifyType = 'email';
    sendOtp("{{ route('vendor.onboarding.send-email') }}", {
        email: emailInput.value
    });
}

function sendOtp(url, payload) {
    fetch(url, {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':csrf,
            'Content-Type':'application/json'
        },
        body:JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(() => document.getElementById('otpModal').style.display='block')
    .catch(() => alert('Unable to send OTP'));
}

/* ================= OTP VERIFY ================= */
function verifyOtp() {
    const otp = otpInput.value;
    const url = verifyType === 'phone'
        ? "{{ route('vendor.onboarding.verify-phone') }}"
        : "{{ route('vendor.onboarding.verify-email') }}";

    fetch(url, {
        method:'POST',
        headers:{
            'X-CSRF-TOKEN':csrf,
            'Content-Type':'application/json'
        },
        body:JSON.stringify({
            otp,
            phone: phoneInput.value,
            email: emailInput.value
        })
    })
    .then(() => location.reload())
    .catch(() => alert('Invalid OTP'));
}

/* ================= SKIP ================= */
function skipAndContinue() {
    window.location.href = "{{ route('vendor.onboarding.business-info') }}";
}
</script>
@endpush
