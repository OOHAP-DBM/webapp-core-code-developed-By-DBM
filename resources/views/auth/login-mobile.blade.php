@extends('layouts.guest')

@section('title', 'Login with Mobile - OOHAPP')

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

</style>
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">

        <!-- LEFT IMAGE -->
        <div class="col-md-5 d-none d-md-block auth-left">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/login/login_image.jpeg') }}" alt="OOHAPP">
            </a>
        </div>

        <!-- RIGHT FORM -->
        <div class="col-md-7 col-12 auth-right">
            <div class="signup-box">

                <h3 class="text-start">Login with Mobile</h3>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 px-4 py-3 mb-3 position-relative" style="font-size: 15px;">
                        <ul class="mb-2 ps-4" style="list-style: disc;">
                            @foreach ($errors->all() as $error)
                                <li class="mb-1">{{ $error }}</li>
                            @endforeach
                        </ul>
                        <div class="text-end mt-2">
                            <a href="{{ route('password.request') }}" class="text-success small text-decoration-underline">Forgot Password?</a>
                        </div>
                        <button type="button" class="btn-close position-absolute top-0 end-0 mt-2 me-2" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <form method="POST"
                    action="{{ route('login.submit') }}"
                    id="loginForm"
                    autocomplete="off">
                    @csrf

                    <input type="text" name="fakeusernameremembered" style="display:none">
                    <input type="password" name="fakepasswordremembered" style="display:none">

                    <!-- MOBILE INPUT -->
                    <div class="mb-2 text-start">
                        <input type="tel"
                            name="login"
                            id="mobileInput"
                            class="form-control"
                            placeholder="Mobile Number"
                            maxlength="10"
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                            readonly>
                        <small class="text-muted" id="mobileHelp">
                            Enter your registered mobile number
                        </small>
                    </div>

                    <!-- PASSWORD -->
                    <div class="mb-2 text-start d-none" id="passwordBox">
                        <div class="position-relative">
                            <input type="password"
                                name="password"
                                id="passwordInput"
                                class="form-control pe-5"
                                placeholder="Password"
                                autocomplete="new-password"
                                readonly>

                            <span class="position-absolute top-50 end-0 translate-middle-y me-3"
                                id="togglePassword"
                                style="cursor:pointer;">
                                <i class="fa-solid fa-eye text-muted"></i>
                            </span>
                            <small>Enter your password</small>
                        </div>
                    </div>

                    <button type="submit"
                            class="btn btn-continue w-100 mt-3"
                            id="continueBtn"
                            disabled>
                        Continue
                    </button>
                </form>


                <div class="divider"><span>OR</span></div>

                <a href="{{ route('login') }}" class="social-btn btn border">
                    <i class="fa-solid fa-envelope me-2"></i>
                    Continue with Email
                </a>
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
                        <a href="{{ route('terms') }}">Terms & Conditions</a> and
                        <a href="{{ route('privacy') }}">Privacy policy</a> of OOHAPP.
                    </small>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const mobileInput  = document.getElementById('mobileInput');
        const mobileHelp   = document.getElementById('mobileHelp');
        const passwordBox = document.getElementById('passwordBox');
        const passwordInp = document.getElementById('passwordInput');
        const btn         = document.getElementById('continueBtn');
        const form        = document.getElementById('loginForm');
        const toggleEye   = document.getElementById('togglePassword');

        let step = 1; // 1 = mobile, 2 = password

        function isValidMobile(mobile) {
            return /^\d{10}$/.test(mobile);
        }

        /* MOBILE VALIDATION */
        mobileInput.addEventListener('input', () => {
            const val = mobileInput.value.trim();

            if (!isValidMobile(val)) {
                mobileHelp.innerText = 'Enter a valid 10-digit mobile number';
                mobileHelp.classList.add('text-danger');
                btn.disabled = true;
                btn.classList.remove('active');
            } else {
                mobileHelp.innerText = 'Looks good';
                mobileHelp.classList.remove('text-danger');
                btn.disabled = false;
                btn.classList.add('active');
            }
        });

        /* FORM SUBMIT */
        form.addEventListener('submit', function (e) {

            // STEP 1 → SHOW PASSWORD
            if (step === 1) {
                e.preventDefault();

                if (!isValidMobile(mobileInput.value)) return;

                mobileInput.closest('.mb-2').classList.add('d-none');
                passwordBox.classList.remove('d-none');

                passwordInp.setAttribute('required', 'required');
                btn.textContent = 'Login';
                btn.disabled = true;
                btn.classList.remove('active');

                passwordInp.focus();
                step = 2;
                return;
            }

            // STEP 2 → normal submit
        });

        passwordInp.addEventListener('input', () => {
            btn.disabled = !passwordInp.value.trim();
            btn.classList.toggle('active', !btn.disabled);
        });

        /* PASSWORD EYE */
        toggleEye.addEventListener('click', () => {
            const isPwd = passwordInp.type === 'password';
            passwordInp.type = isPwd ? 'text' : 'password';
            toggleEye.innerHTML = isPwd
                ? '<i class="fa-solid fa-eye-slash text-muted"></i>'
                : '<i class="fa-solid fa-eye text-muted"></i>';
        });

    });
</script>
<script>
    document.addEventListener('DOMContentLoaded', function () {

        const mobileInput  = document.getElementById('mobileInput');
        const passwordInp = document.getElementById('passwordInput');

        // 🔥 remove readonly only when user interacts
        setTimeout(() => {
            mobileInput.removeAttribute('readonly');
            passwordInp.removeAttribute('readonly');
        }, 500);

    });
</script>
@endpush
