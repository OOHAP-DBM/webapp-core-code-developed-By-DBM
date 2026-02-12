@extends('layouts.guest')
@section('title', 'Reset Password - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

<style>
    html,
    body {
        width: 100%;
        height: 100%;
        overflow: hidden;
    }

    /* WRAPPER */
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

    /* RIGHT SIDE */
    .auth-right {
        display: flex;
        align-items: center;
        justify-content: center;
        background: #fff;
    }

    /* FORM BOX (same as login) */
    .signup-box {
        width: 100%;
        max-width: 380px;
        text-align: left;
    }

    /* TITLE */
    .signup-box h3 {
        font-weight: 600;
        margin-bottom: 20px;
    }

    /* INPUTS */
    .form-control {
        height: 46px;
        border-radius: 6px;
    }

    /* BUTTON */
    .btn-main {
        height: 46px;
        border-radius: 8px;
        background: #2bb57c;
        color: #fff;
        border: none;
        font-weight: 500;
        transition: .2s;
    }

    .btn-main:hover {
        background: #239866;
        color: #fff;
    }

    /* OTP BOX */
    .otp-input {
        letter-spacing: 12px;
        font-size: 24px;
        text-align: center;
        font-weight: 600;
    }

    /* STEPS */
    .step {
        display: none;
    }

    .step.active {
        display: block;
    }

    /* SMALL LINKS */
    .small-link {
        font-size: 13px;
        cursor: pointer;
    }

    /* FOOTER */
    .footer-text {
        margin-top: 60px;
        font-size: 13px;
        color: #6b7280;
    }

    @media(max-width: 768px) {
        .auth-left {
            display: none;
        }
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

                <!-- STEP 1 -->
                <div id="step1" class="step active">
                    <h3>Forgot Password</h3>

                    <div class="mb-2">
                        <input type="text"
                               id="phone"
                               class="form-control"
                               value="{{ $phone ?? '' }}"
                               readonly>
                        <small class="text-muted">
                            We will send OTP to this number
                        </small>
                    </div>

                    <button class="btn btn-main w-100 mt-3" onclick="sendOtp()">
                        Send OTP
                    </button>

                    <div class="text-center mt-3">
                        <a href="{{ route('login.mobile') }}" class="text-success font-semibold small-link">
                            Back to Login
                        </a>
                    </div>
                </div>

                <!-- STEP 2 -->
                <div id="step2" class="step">
                    <h3>Verify OTP</h3>

                    <div class="mb-3">
                        <input type="text"
                               id="otp"
                               maxlength="4"
                               class="form-control otp-input"
                               placeholder="••••">
                    </div>

                    <button class="btn btn-main w-100" onclick="verifyOtp()">
                        Verify OTP
                    </button>

                    <div class="text-center mt-3">
                        <span class="text-muted small-link" onclick="sendOtp()">
                            Resend OTP
                        </span>
                    </div>
                </div>

                <!-- STEP 3 -->
                <div id="step3" class="step">
                    <h3>Create New Password</h3>

                    <input type="password"
                        id="password"
                        class="form-control mb-3"
                        placeholder="New Password"
                        autocomplete="new-password"
                        readonly
                        onfocus="this.removeAttribute('readonly');">
                    <input type="password"
                        id="password_confirmation"
                        class="form-control mb-3"
                        placeholder="Confirm Password"
                        autocomplete="new-password"
                        readonly
                        onfocus="this.removeAttribute('readonly');">
                    <button class="btn btn-main w-100" onclick="resetPassword()">
                        Change Password
                    </button>
                </div>

                <div class="footer-text text-center">
                    By continuing, you agree to OOHAPP
                    <a href="{{ route('terms') }}" class="text-dark font-semibold">Terms</a> &
                    <a href="{{ route('privacy') }}" class="text-dark font-semibold">Privacy Policy</a>
                </div>

            </div>

        </div>
    </div>
</div>
@endsection


@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    function toast(icon, msg) {
        Swal.fire({
            toast: true,
            position: 'top-end',
            icon: icon,
            title: msg,
            showConfirmButton: false,
            timer: 2000
        });
    }

    function go(step) {
        document.querySelectorAll('.step').forEach(s => s.classList.remove('active'));
        document.getElementById(step).classList.add('active');
    }

    /* SEND OTP */
    function sendOtp() {
        fetch("{{ route('password.mobile.sendOtp') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone: document.getElementById('phone').value
                })
            })
            .then(res => res.json())
            .then(r => {
                if (r.status) {
                    toast('success', 'OTP sent successfully');
                    go('step2');
                } else {
                    toast('error', r.message ?? 'Failed to send OTP');
                }
            });
    }

    /* VERIFY OTP */
    function verifyOtp() {
        fetch("{{ route('password.mobile.verifyOtp') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    phone: document.getElementById('phone').value,
                    otp: document.getElementById('otp').value
                })
            })
            .then(res => res.json())
            .then(r => {
                if (r.status) {
                    toast('success', 'OTP verified');
                    go('step3');
                } else {
                    toast('error', 'Invalid OTP');
                }
            });
    }

    /* RESET PASSWORD */
    function resetPassword() {
        fetch("{{ route('password.mobile.reset') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    password: document.getElementById('password').value,
                    password_confirmation: document.getElementById('password_confirmation').value
                })
            })
            .then(res => res.json())
            .then(r => {
                if (r.status) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Password Updated',
                        text: 'Please login with new password'
                    }).then(() => {
                        window.location.href = "{{ route('login.mobile') }}";
                    });
                } else {
                    toast('error', r.message ?? 'Unable to change password');
                }
            });
    }
</script>
@endpush
