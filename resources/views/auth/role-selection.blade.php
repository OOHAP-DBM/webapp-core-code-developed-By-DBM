@extends('layouts.guest')

@section('title', 'Login - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">

<style>
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

html, body {
    width: 100%;
    height: 100%;
    overflow: hidden;
}

body {
    font-family: 'Inter', sans-serif;
    background: #ffffff;
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
    display: block;
}

/* RIGHT FORM */
.auth-right {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #ffffff;
}

/* FORM BOX */
.login-box {
    width: 100%;
    max-width: 380px;
}

/* ROLE SELECTION */
.role-card {
    cursor: pointer;
    text-align: center;
}

.role-card span {
    display: block;
    margin-top: 10px;
    font-weight: 500;
}

.icon-circle {
    width: 90px;
    height: 90px;
    border-radius: 50%;
    color: #0aa84f !important;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 75px;
    color: #ffffff;
    transition: 0.3s ease;
}

.role-card input:checked + .icon-circle,
.role-card:hover .icon-circle {
    background: #078d42;
    color:white !important;
    padding: 2px;0px !important;
    transform: scale(1.05);
}

.role-divider {
    width: 1px;
    height: 80px;
    background: #e5e7eb;
}

/* FOOTER */
.auth-footer {
    margin-top: 80px; /* 👈 thoda niche kiya */
}

.auth-footer p a {
    text-decoration: none;
}

/* MOBILE */
@media (max-width: 768px) {
    .auth-left {
        display: none;
    }

    .auth-right {
        width: 100%;
    }
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
            <div class="login-box text-center">

                <h3 class="fw-semibold mb-1">What you are?</h3>
                <p class="text-muted mb-4">Select your role</p>
                <form action="{{ route('register.store-role') }}" method="POST" id="roleForm">
                    @csrf
                    <input type="hidden" name="role" id="selectedRole">

                    <div class="role-wrapper d-flex justify-content-center align-items-center gap-5 text-dark">

                        <!-- CUSTOMER -->
                        <label class="role-card" onclick="selectRole('customer', this)">
                            <div class="icon-circle">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span>Customer</span>
                        </label>

                        <div class="role-divider"></div>

                        <!-- VENDOR -->
                        <label class="role-card" onclick="selectRole('vendor', this)">
                            <div class="icon-circle">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <span>Vendor</span>
                        </label>

                    </div>

                    <!-- optional: auto submit OR button -->
                    <button type="submit" id="continueBtn" hidden></button>
                </form>
                <!-- FOOTER -->
                <div class="auth-footer mt-5 pt-5">
                    <p class="mb-1 mt-5 pt-5">
                        Already Have an Account?
                        <a href="{{ route('login') }}" class="text-success fw-semibold">Login</a>
                    </p>

                    <small class="text-muted">
                        By clicking continue button, you agree with the
                        <a href="{{ route('terms') }}" class="text-dark">Terms & Conditions</a> and
                        <a href="{{ route('privacy') }}" class="text-dark">Privacy Policy</a>
                        of OOHAPP App.
                    </small>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
<script>
function selectRole(role, element) {
    // remove active from all
    document.querySelectorAll('.role-card').forEach(card => {
        card.classList.remove('active');
    });

    // add active to selected
    element.classList.add('active');

    // set role
    document.getElementById('selectedRole').value = role;

    // auto submit form
    document.getElementById('roleForm').submit();
}
</script>
