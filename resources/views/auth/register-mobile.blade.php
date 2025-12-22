@extends('layouts.guest')

@section('title', 'Signup with Mobile - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
html, body {
    width: 100%;
    height: 100%;
    overflow: hidden;
}
.auth-wrapper {
    width: 100vw;
    height: 100vh;
}
.auth-left {
    background: #000;
    padding: 0;
}
.auth-left img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}
.auth-right {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}
.signup-box {
    width: 100%;
    max-width: 380px;
}
.form-control {
    height: 46px;
}
.btn-continue {
    height: 46px;
    background: #2bb57c;
    color: #fff;
}
.otp-box {
    width: 44px;
    height: 46px;
    text-align: center;
    font-size: 18px;
}
.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
}
.social-btn {
    height: 46px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    width: 100%;
    margin-bottom: 12px;
}
.social-btn.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.divider {
    display: flex;
    align-items: center;
    margin: 20px 0;
    font-size: 13px;
    color: #9ca3af;
}
.divider::before,
.divider::after {
    content: '';
    flex: 1;
    height: 1px;
    background: #e5e7eb;
}
.divider span {
    margin: 0 10px;
}
.social-btn,
.btn-continue {
    border: 1px solid #e5e7eb !important;
}

.social-btn:hover,
.btn-continue:hover {
    border: 1px solid #e5e7eb !important;
}
/* OTP section positioning fix */
.otp-section {
    position: relative;
    top: -150px;
    left: 0;
}

/* ONLY large screens (lg and above) */
@media (min-width: 992px) {
    .otp-section {
        left: -200px;
    }
}
/* PRIMARY GREEN BUTTON FIX */
.btn-continue {
    background-color: #2bb57c !important;
    color: #fff !important;
    border: 1px solid #2bb57c !important;
    transition: background-color 0.2s ease, color 0.2s ease;
}

.btn-continue:hover,
.btn-continue:focus {
    background-color: #249b69 !important; /* darker green */
    color: #fff !important;
    border-color: #249b69 !important;
}



</style>
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
<div class="row h-100">

    <!-- LEFT -->
    <div class="col-md-5 d-none d-md-block auth-left">
        <a href="{{ route('home') }}">
            <img src="{{ asset('assets/images/login/login_image.jpeg') }}">
        </a>
    </div>

    <!-- RIGHT -->
    <div class="col-md-7 col-12 auth-right">
    <div class="signup-box">
        <div id="errorBox" class="alert alert-danger d-none"></div>

        <!-- ================= MOBILE INPUT ================= -->
        <div id="mobile-section">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <h3 class="mb-3 text-center mb-3">Signup</h3>

            <div class="mb-2">
                <input type="tel"
                    id="mobileInput"
                    class="form-control"
                    placeholder="Mobile Number"
                    maxlength="10">
                <small class="text-muted">
                    We will send a 6-digit OTP to your mobile
                </small>
            </div>

            <button class="btn btn-continue w-100 mt-3" id="sendOtpBtn">
                Continue
            </button>
            <div class="divider"><span>OR</span></div>

            <!-- Continue with Email -->
            <a href="{{ route('register.form') }}"
            class="social-btn btn text-decoration-none border border-1">
                <i class="fa-solid fa-envelope me-2"></i>
                Continue with Email
            </a>

            <!-- Continue with Google -->
            <button type="button" class="social-btn google-btn">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18">
                <span>Continue with Google</span>
            </button>
            <div class="footer-text fs-6 text-center mt-5 pt-5">
                Already Have an Account?
                <a href="{{ route('login') }}" class="text-success fw-bold fs-6">Login</a>
            </div>
            
        </div>

        <!-- ================= OTP ================= -->
        <div id="otp-section" class="d-none mt-4 otp-section">
          <div id="otpError" class="alert alert-danger d-none mb-2"></div>
            <div class="otp-text">
                <h6 class="fw-semibold mb-1 fs-4">Verify with OTP</h6>
                <p class="text-muted small mb-3">
                    Enter the 4-digit code sent to you at <br>
                    <strong id="otp-mobile-text"></strong>
                </p>
            </div>
            <div class="d-flex gap-2 my-3">
                <input class="otp-box form-control" maxlength="1">
                <input class="otp-box form-control" maxlength="1">
                <input class="otp-box form-control" maxlength="1">
                <input class="otp-box form-control" maxlength="1">
            </div>
        </div>

        <!-- ================= FINAL REGISTER ================= -->
        <div id="final-section" class="d-none mt-4">

            <h5 class="mb-3">Create Password</h5>

            <form method="POST"
                action="{{ route('register.submit') }}"
                autocomplete="off"
                id="finalRegisterForm">
                @csrf

                <!-- IMPORTANT -->
                <input type="hidden" name="phone" id="finalPhone">
                <input type="hidden" name="phone_verified" value="1">
                <input type="hidden" name="role" value="{{ session('signup_role') }}">

                <!-- NAME -->
                <div class="mb-2">
                    <input type="text"
                        name="name"
                        id="nameInput"
                        class="form-control"
                        placeholder="Full Name"
                        autocomplete="new-name"
                        autocorrect="off"
                        spellcheck="false"
                        required>
                </div>

                <!-- PASSWORD -->
                <div class="mb-2 position-relative">
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control"
                        placeholder="Password"
                        autocomplete="new-password"
                        required>
                    <span class="toggle-password" data-target="password">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>

                <!-- CONFIRM PASSWORD -->
                <div class="mb-2 position-relative">
                    <input type="password"
                        id="password_confirmation"
                        name="password_confirmation"
                        class="form-control"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                        required>
                    <span class="toggle-password" data-target="password_confirmation">
                        <i class="fa fa-eye"></i>
                    </span>
                </div>

                <button class="btn btn-continue w-100 mt-2">
                    Create Account
                </button>
            </form>

        </div>

    </div>
    </div>
</div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {

        /* ================= ELEMENTS ================= */

        const mobileInput   = document.getElementById('mobileInput');
        const sendOtpBtn    = document.getElementById('sendOtpBtn');
        const otpBoxes      = document.querySelectorAll('.otp-box');

        const mobileSection = document.getElementById('mobile-section');
        const otpSection    = document.getElementById('otp-section');
        const finalSection  = document.getElementById('final-section');

        const otpMobileText = document.getElementById('otp-mobile-text');

        const errorBox      = document.getElementById('errorBox');   // global errors
        const otpErrorBox   = document.getElementById('otpError');   // OTP-only error

        const form          = document.getElementById('finalRegisterForm');
        const nameInput     = document.getElementById('nameInput');
        const password      = document.getElementById('password');
        const confirmPwd    = document.getElementById('password_confirmation');

        /* ================= ERROR HELPERS ================= */

        function showError(msg) {
            errorBox.innerText = msg;
            errorBox.classList.remove('d-none');
            errorBox.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        function clearError() {
            errorBox.innerText = '';
            errorBox.classList.add('d-none');
        }

        function showOtpError(msg) {
            otpErrorBox.innerText = msg;
            otpErrorBox.classList.remove('d-none');
        }

        function clearOtpError() {
            otpErrorBox.innerText = '';
            otpErrorBox.classList.add('d-none');
        }

        /* ================= SEND OTP ================= */

        sendOtpBtn.addEventListener('click', () => {
            clearError();
            clearOtpError();

            if (!/^\d{10}$/.test(mobileInput.value)) {
                showError('Enter a valid 10-digit mobile number');
                return;
            }

            fetch("{{ route('register.sendPhoneOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ phone: mobileInput.value })
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    showError(res.message || 'Failed to send OTP');
                    return;
                }

                mobileSection.classList.add('d-none');
                otpSection.classList.remove('d-none');

                otpMobileText.innerText = '+91 ' + mobileInput.value;
                otpBoxes[0].focus();
            })
            .catch(() => showError('Something went wrong. Try again.'));
        });

        /* ================= OTP INPUT ================= */

        otpBoxes.forEach((box, index) => {

            box.addEventListener('input', () => {
                clearError();
                clearOtpError();

                box.value = box.value.replace(/\D/g, '');

                if (box.value && otpBoxes[index + 1]) {
                    otpBoxes[index + 1].focus();
                }

                const otp = Array.from(otpBoxes).map(b => b.value).join('');
                if (otp.length === 4) verifyOtp(otp);
            });

            box.addEventListener('keydown', e => {
                if (e.key === 'Backspace' && !box.value && otpBoxes[index - 1]) {
                    otpBoxes[index - 1].focus();
                }
            });
        });

        /* ================= VERIFY OTP ================= */

        function verifyOtp(otp) {
            fetch("{{ route('register.verifyPhoneOtp') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone: mobileInput.value,
                    otp: otp
                })
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) {
                    clearError();
                    showOtpError(res.message || 'Invalid OTP');

                    otpBoxes.forEach(b => b.value = '');
                    otpBoxes[0].focus();
                    return;
                }

                document.getElementById('finalPhone').value = mobileInput.value;

                otpSection.classList.add('d-none');
                finalSection.classList.remove('d-none');
            })
            .catch(() => showOtpError('OTP verification failed'));
        }

        /* ================= FINAL FORM VALIDATION ================= */

        function isValidName(name) {
            return /^[A-Za-z ]{2,100}$/.test(name.trim());
        }

        form.addEventListener('submit', e => {
            clearError();

            if (!isValidName(nameInput.value)) {
                e.preventDefault();
                showError('Name must contain only letters and spaces');
                nameInput.focus();
                return;
            }

            if (password.value.length < 8) {
                e.preventDefault();
                showError('Password must be at least 8 characters long');
                password.focus();
                return;
            }

            if (password.value !== confirmPwd.value) {
                e.preventDefault();
                showError('Password and Confirm Password do not match');
                confirmPwd.focus();
                return;
            }
        });

        /* ================= PASSWORD EYE TOGGLE ================= */

        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                const icon  = this.querySelector('i');

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.replace('fa-eye', 'fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.replace('fa-eye-slash', 'fa-eye');
                }
            });
        });

    });
</script>
@endpush
