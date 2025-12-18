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
.social-btn.google-btn {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}
.footer-text {
    margin-top: 40px;
    font-size: 13px;
    color: #6b7280;
}
.otp-box {
    width: 44px;
    height: 46px;
    text-align: center;
    font-size: 18px;
    border: 1px solid #d1d5db;
    border-radius: 6px;
}
@media (max-width: 768px) {
    .auth-left { display: none; }
}
.otp-wrapper {
    margin-top: -250px !important; 
}

.otp-text {
    text-align: left;
}

.otp-icon {
    width: 68px;
    height: 68px;
    border-radius: 50%;
    background: #d1fae5;
    display: flex;
    align-items: center;
    justify-content: center;
}
/* default: mobile + tablet */
.d-done-box {
    margin-left: 0 !important;
}

/* ONLY lg and above */
@media (min-width: 992px) {
    .d-done-box {
        margin-left: -200px !important;
    }
}
.toggle-password {
    position: absolute;
    right: 14px;
    top: 50%;
    transform: translateY(-50%);
    cursor: pointer;
    color: #6b7280;
    z-index: 5;
}

.toggle-password:hover {
    color: #111827;
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

        <div id="ajaxError" class="alert alert-danger text-start d-none"></div>

        <!-- ================= OTP SECTION ================= -->
        <div id="otp-section">

            <!-- EMAIL FORM -->
            <form id="signupForm">
                <h3>Signup</h3>
                  @if ($errors->any())
                    <div class="alert alert-danger text-start mb-3">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                @csrf
                <div class="mb-2 text-start">
                    <input type="email" id="emailInput" class="form-control" placeholder="Email" required>
                    <small class="text-muted">We will send you a 4-digit OTP to confirm your email</small>
                </div>

                <button type="submit" class="btn btn-continue w-100 mt-3" id="continueBtn" disabled>
                    Continue
                </button>
            </form>

            <div class="divider"><span>OR</span></div>

            <a href="{{ route('register.mobile-form')}}"
                class="social-btn btn border border-1">
                Continue with Mobile
            </a>


            <button class="social-btn google-btn">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" width="18">
                <span>Continue with Google</span>
            </button>

            <div class="footer-text">
                Already Have an Account?
                <a href="{{ route('login') }}" class="text-success">Login</a>
            </div>

            <!-- OTP VERIFY UI (FIGMA STYLE) -->
           <div id="otp-ui" class="d-none mt-4 otp-wrapper">

            <!-- ICON -->
            <div class="mb-3 otp-text">
                <div class="otp-icon">
                    <i class="fa-solid fa-envelope text-success fs-3"></i>
                </div>
            </div>

            <!-- TEXT -->
            <div class="otp-text">
                <h6 class="fw-semibold mb-1 fs-4">Verify with OTP</h6>
                <p class="text-muted small mb-3">
                    Enter the 4-digit code sent to you at <br>
                    <strong id="otp-email-text"></strong>
                </p>
            </div>

            <!-- OTP BOXES -->
            <div class="d-flex gap-2 my-3 otp-text">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
                <input class="otp-box" maxlength="1" inputmode="numeric">
            </div>

            <!-- RESEND -->
            <div class="otp-text">
                <small class="text-muted">
                    Resend OTP in <span class="text-success fw-bold ">00:30</span>
                </small>
            </div>

            </div>


           </div>
        <!-- ================= FINAL REGISTER ================= -->
        <div id="d-done" class="d-done-box" style="display:none; margin-top:-250px !important;">

            <!-- Heading & Info -->
            <h3 class="text-start mb-2">Create a Password</h3>
            <p class="text-start mb-1 small">
                Password should be minimum 8 character + 1 upper case +
            </p>
            <p class="text-start mb-3 small">
                Lower case + 1 special symbol
            </p>
            <form action="{{ route('register.submit') }}"
                method="POST"
                autocomplete="off">
                @csrf

                <!-- REQUIRED HIDDEN FIELDS -->
                <input type="hidden" name="email" id="finalEmail">
                <input type="hidden" name="email_verified" value="1">
                <input type="hidden" name="role" value="{{ $role ?? 'customer' }}">

                <!-- FULL NAME -->
                <p class="text-start mb-1">
                    Name <span class="text-danger">*</span>
                </p>
                <div class="mb-2 text-start">
                    <input type="text"
                        name="name"
                        class="form-control"
                        placeholder="Full Name"
                        autocomplete="new-name"
                        autocorrect="off"
                        spellcheck="false"
                        required
                        >
                </div>
                    <!-- PASSWORD -->
                <p class="text-start mb-1">
                    Password <span class="text-danger">*</span>
                </p>

                <div class="mb-2 text-start position-relative">
                    <input type="password"
                        id="password"
                        name="password"
                        class="form-control pe-5"
                        placeholder="Password"
                        autocomplete="new-password"
                        required>

                    <span class="toggle-password"
                        data-target="password">
                        <i class="fa-solid fa-eye"></i>
                    </span>
                </div>
                <!-- CONFIRM PASSWORD -->
              <p class="text-start mb-1">
                Confirm Password <span class="text-danger">*</span>
            </p>

            <div class="mb-2 text-start position-relative">
                <input type="password"
                    id="password_confirmation"
                    name="password_confirmation"
                    class="form-control pe-5"
                    placeholder="Confirm Password"
                    autocomplete="new-password"
                    required>

                <span class="toggle-password"
                    data-target="password_confirmation">
                    <i class="fa-solid fa-eye"></i>
                </span>
            </div>
                <!-- SUBMIT -->
                <button type="submit" class="btn btn-continue active w-100">
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
    document.addEventListener('DOMContentLoaded', function () {

        /* ===================== HELPERS ===================== */

        function showFieldError(input, msg) {
            clearFieldError(input);
            const err = document.createElement('small');
            err.className = 'text-danger d-block mt-1';
            err.innerText = msg;
            input.parentNode.appendChild(err);
        }

        function clearFieldError(input) {
            const err = input.parentNode.querySelector('.text-danger');
            if (err) err.remove();

            // show helper text again if exists
            const helper = input.parentNode.querySelector('.text-muted');
            if (helper) helper.style.display = 'block';
        }

        function validateEmail(email) {
            return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email.trim());
        }

        function validateName(name) {
            return /^[A-Za-z ]{2,100}$/.test(name.trim());
        }

        function validatePassword(pwd) {
            return /^(?=.*[a-z])(?=.*\d)[^\s]{8,64}$/.test(pwd);
        }

        /* ===================== EMAIL + OTP ===================== */

        const emailInput   = document.getElementById('emailInput');
        const signupForm   = document.getElementById('signupForm');
        const continueBtn  = document.getElementById('continueBtn');
        const errorBox     = document.getElementById('ajaxError');
        const helperText   = emailInput.parentNode.querySelector('.text-muted');

        const otpUI        = document.getElementById('otp-ui');
        const otpBoxes     = document.querySelectorAll('.otp-box');
        const otpEmailText = document.getElementById('otp-email-text');

        const divider      = document.querySelector('.divider');
        const socialBtns   = document.querySelectorAll('.social-btn');
        const footerText   = document.querySelector('.footer-text');

        let enteredEmail = '';

        emailInput.addEventListener('input', () => {
            errorBox.classList.add('d-none');
            errorBox.innerText = '';
            clearFieldError(emailInput);

            if (!validateEmail(emailInput.value)) {
                continueBtn.disabled = true;
                continueBtn.classList.remove('active');
                if (helperText) helperText.style.display = 'none';
                showFieldError(emailInput, 'Enter a valid email address');
            } else {
                continueBtn.disabled = false;
                continueBtn.classList.add('active');
                if (helperText) helperText.style.display = 'block';
            }
        });

        signupForm.addEventListener('submit', function (e) {
            e.preventDefault();

            if (!validateEmail(emailInput.value)) {
                showFieldError(emailInput, 'Invalid email format');
                return;
            }

            enteredEmail = emailInput.value;

            fetch("{{ route('register.sendEmailOtp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: enteredEmail })
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) return showGlobalError(res.message);

                signupForm.style.display = 'none';
                divider.style.display = 'none';
                socialBtns.forEach(b => b.style.display = 'none');
                footerText.style.display = 'none';

                otpEmailText.innerText = enteredEmail;
                otpUI.classList.remove('d-none');
                otpBoxes[0].focus();
            });
        });

        /* ===================== OTP ===================== */

        otpBoxes.forEach((box, index) => {
            box.addEventListener('input', () => {
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

        function verifyOtp(otp) {
            if (!/^\d{4}$/.test(otp)) {
                showGlobalError('OTP must be 4 digits');
                return;
            }

            fetch("{{ route('register.verifyEmailOtp') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ email: enteredEmail, otp })
            })
            .then(r => r.json())
            .then(res => {
                if (!res.success) return showGlobalError(res.message);

                document.getElementById('otp-section').style.display = 'none';
                document.getElementById('d-done').style.display = 'block';
                document.getElementById('finalEmail').value = enteredEmail;
            });
        }

        function showGlobalError(msg) {
            errorBox.innerText = msg || 'Something went wrong';
            errorBox.classList.remove('d-none');
        }

        /* ===================== FINAL FORM ===================== */

        const finalForm = document.querySelector('#d-done form');
        if (!finalForm) return;

        const nameInput   = finalForm.querySelector('input[name="name"]');
        const password   = document.getElementById('password');
        const confirmPwd = document.getElementById('password_confirmation');

        nameInput.addEventListener('input', () => {
            clearFieldError(nameInput);
            if (!validateName(nameInput.value)) {
                showFieldError(nameInput, 'Name must contain only letters and spaces');
            }
        });

        password.addEventListener('input', () => {
            clearFieldError(password);
            if (!validatePassword(password.value)) {
                showFieldError(password, 'Password must be strong (8+, upper, lower, number, symbol)');
            }
        });

        confirmPwd.addEventListener('input', () => {
            clearFieldError(confirmPwd);
            if (confirmPwd.value !== password.value) {
                showFieldError(confirmPwd, 'Passwords do not match');
            }
        });

        finalForm.addEventListener('submit', function (e) {
            let valid = true;

            clearFieldError(nameInput);
            clearFieldError(password);
            clearFieldError(confirmPwd);

            if (!validateName(nameInput.value)) {
                showFieldError(nameInput, 'Enter a valid full name');
                valid = false;
            }

            // if (!validatePassword(password.value)) {
            //     showFieldError(password, 'Weak password');
            //     valid = false;
            // }

            if (password.value !== confirmPwd.value) {
                showFieldError(confirmPwd, 'Password confirmation mismatch');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        document.querySelectorAll('.toggle-password').forEach(toggle => {
            toggle.addEventListener('click', function () {

                const inputId = this.getAttribute('data-target');
                const input   = document.getElementById(inputId);
                const icon    = this.querySelector('i');

                if (!input) return;

                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('fa-eye');
                    icon.classList.add('fa-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('fa-eye-slash');
                    icon.classList.add('fa-eye');
                }
            });
        });

    });
</script>

@endpush
