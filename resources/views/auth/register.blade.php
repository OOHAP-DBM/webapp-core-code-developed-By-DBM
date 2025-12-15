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

</style>
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">

        <!-- LEFT IMAGE -->
        <div class="col-md-5 d-none d-md-block auth-left">
            <img src="{{ asset('assets/images/login/login_image.jpeg') }}" alt="OOHAPP">
        </div>

        <!-- RIGHT FORM -->
        <div class="col-md-7 col-12 auth-right">
            <div class="signup-box">

                <h3>Signup</h3>
                 @if ($errors->any())
                    <div class="alert alert-danger text-start">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('register.sendEmailOtp') }}" id="signupForm">
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
                        Already Have an Account?
                        <a href="{{ route('login') }}" class="text-success">Login</a>
                    </p>
                    <small>
                        By clicking continue button, you agree with the
                        <a href="#">Terms & Conditions</a> and
                        <a href="#">Privacy policy</a> of OOHAPP.
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
    const email = document.getElementById('emailInput');
    const btn   = document.getElementById('continueBtn');

    email.addEventListener('input', function () {
        if (this.value.trim() !== '') {
            btn.disabled = false;
            btn.classList.add('active');
        } else {
            btn.disabled = true;
            btn.classList.remove('active');
        }
    });
});
</script>
@endpush
