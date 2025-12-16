@extends('layouts.guest')

@section('title', 'Signup - OOHAPP')

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

/* LEFT IMAGE */
.auth-left {
    background: #000;
    padding: 0;
}

.auth-left img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* RIGHT FORM */
.auth-right {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #fff;
}

/* FORM BOX */
.signup-box {
    width: 100%;
    max-width: 380px;
    text-align: center;
}

.signup-box h3 {
    font-weight: 600;
    margin-bottom: 20px;
}

.form-control {
    height: 46px;
    border-radius: 6px;
}

.btn-continue {
    height: 46px;
    border-radius: 8px;
    background: #e5e7eb;
    color: #9ca3af;
    cursor: not-allowed;
}

.btn-continue.active {
    background: #2bb57c;
    color: #fff;
    cursor: pointer;
}

.divider {
    display: flex;
    align-items: center;
    margin: 25px 0;
    color: #9ca3af;
    font-size: 13px;
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

.social-btn {
    height: 46px;
    border-radius: 8px;
    border: 1px solid #e5e7eb;
    background: #fff;
    width: 100%;
    margin-bottom: 12px;
}

.footer-text {
    margin-top: 60px;
    font-size: 13px;
    color: #6b7280;
}

.footer-text a {
    text-decoration: none;
    font-weight: 500;
}

@media (max-width: 768px) {
    .auth-left {
        display: none;
    }
}
.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;

    border: 1px solid #d1d5db;
    color: #111827;
    font-weight: 500;

    transition: all 0.2s ease;
}

.google-btn:hover {
    background: #f9fafb;
    border-color: #9ca3af;
}

.google-btn img {
    display: block;
}
.otp-box {
    width: 44px;
    height: 46px;
    text-align: center;
    font-size: 18px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}


</style>
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">

        <!-- LEFT IMAGE -->
        <div class="col-md-5 d-none d-md-block auth-left">
             <a href="{{route('home')}}"><img src="{{ asset('assets/images/login/login_image.jpeg') }}"></a>
        </div>

        <!-- RIGHT FORM -->
        <div class="col-md-7 col-12 auth-right">
            <div class="signup-box">

                <h3 class="text-start">Login to your account</h3>

                @if ($errors->any())
                    <div class="alert alert-danger text-start">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.submit') }}" id="signupForm">
                    @csrf

                    <div class="mb-2 text-start">
                        <input type="email"
                               name="email"
                               id="emailInput"
                               class="form-control"
                               placeholder="Email"
                               required>
                        <small class="text-muted">
                            We will send you a 4-digit OTP to confirm your email
                        </small>
                    </div>

                    <button type="submit"
                            class="btn btn-continue w-100 mt-3"
                            id="continueBtn"
                            disabled>
                        Continue
                    </button>
                </form>

                <div class="divider">
                    <span>OR</span>
                </div>

                <button class="social-btn">
                    <i class="fa-solid fa-mobile-screen me-2"></i>
                    Continue with Mobile
                </button>

                <button class="social-btn google-btn">
                    <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg"
                        width="18"
                        class="me-2">
                    Continue with Google
                </button>


                <div class="footer-text">
                    <p class="mb-1">
                        Create an Account?
                        <a href="{{route('register.role-selection')}}" class="text-success">SignUp</a>
                    </p>
                    <small>
                        By clicking continue button, you agree with the
                        <a href="#">Terms & Conditions</a> and
                        <a href="#">Privacy policy</a> of OOHAPP.
                    </small>
                </div>

            </div>
            <!-- OTP LOGIN UI -->
            <div id="otp-login-ui" class="d-none mt-4">

                <div class="mb-3 text-start">
                    <h6 class="fw-semibold">Verify with OTP</h6>
                    <p class="text-muted small">
                        Enter the 4-digit code sent to <br>
                        <strong id="otp-login-email"></strong>
                    </p>
                </div>

                <div class="d-flex gap-2 mb-3">
                    <input class="otp-box" maxlength="1">
                    <input class="otp-box" maxlength="1">
                    <input class="otp-box" maxlength="1">
                    <input class="otp-box" maxlength="1">
                </div>

                <small class="text-muted">
                    Resend OTP in <span class="text-success">00:30</span>
                </small>

            </div>

        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    const emailInput = document.getElementById('emailInput');
    const continueBtn = document.getElementById('continueBtn');
    const form = document.getElementById('signupForm');

    const otpUI = document.getElementById('otp-login-ui');
    const otpEmailText = document.getElementById('otp-login-email');
    const otpBoxes = document.querySelectorAll('.otp-box');

    let enteredEmail = '';

    // enable button
    emailInput.addEventListener('input', () => {
        continueBtn.disabled = !emailInput.value.trim();
        continueBtn.classList.toggle('active', !continueBtn.disabled);
    });

    // CONTINUE CLICK
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        sendLoginOtp();
    });

    // SEND OTP
    function sendLoginOtp() {
        enteredEmail = emailInput.value;

        fetch("{{ route('login.sendEmailOtp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ email: enteredEmail })
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alert(res.message || 'OTP send failed');
                return;
            }

            // hide email input
            form.style.display = 'none';

            // show otp
            otpEmailText.innerText = enteredEmail;
            otpUI.classList.remove('d-none');
            otpBoxes[0].focus();
        });
    }

    // OTP INPUT HANDLING
    otpBoxes.forEach((box, index) => {

        box.addEventListener('input', () => {
            box.value = box.value.replace(/\D/g, '');

            if (box.value && otpBoxes[index + 1]) {
                otpBoxes[index + 1].focus();
            }

            const otp = Array.from(otpBoxes).map(b => b.value).join('');
            if (otp.length === 4) {
                verifyLoginOtp(otp);
            }
        });

        box.addEventListener('keydown', e => {
            if (e.key === 'Backspace' && !box.value && otpBoxes[index - 1]) {
                otpBoxes[index - 1].focus();
            }
        });
    });

    // VERIFY OTP
    function verifyLoginOtp(otp) {
        fetch("{{ route('login.verifyEmailOtp') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                email: enteredEmail,
                otp: otp
            })
        })
        .then(r => r.json())
        .then(res => {
            if (!res.success) {
                alert(res.message || 'Invalid OTP');
                return;
            }

            // LOGIN SUCCESS → redirect
            window.location.href = res.redirect || "{{ route('home') }}";
        });
    }

});
</script>

@endpush
