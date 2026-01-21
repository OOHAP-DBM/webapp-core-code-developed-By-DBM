@extends('layouts.guest')

@section('title', 'Forgot Password - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">
        <div class="col-md-5 d-none d-md-block auth-left"
            onclick="window.location.href='{{ route('home') }}'">
        </div>
        <div class="col-md-7 col-12 auth-right d-flex align-items-center justify-content-center">
    <div class="verify-box text-center">

        <h3 class="verify-title">Verify Email</h3>
        <p class="verify-subtitle">
            Enter your Email ID associated with OOHAPP<br>
            account
        </p>

        @if (session('status'))
            <div class="alert alert-success small">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}">
            @csrf

            <div class="mb-4">
                <input
                    type="email"
                    name="email"
                    class="form-control verify-input"
                    placeholder="Enter Email"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn border border-gray-200 verify-btn w-100">
                Send Password Reset Link
            </button>
        </form>

        <div class="verify-footer">
            Don’t Have an Account?
            <a href="">Sign up</a>
        </div>

        <div class="verify-terms">
            By clicking continue button, you agree with the
            <a href="#">Terms & Conditions</a> and
            <a href="#">Privacy policy</a> of OOHAPP.
        </div>

    </div>
</div>

    </div>
</div>
@endsection
<style>
    .auth-right {
    background: #ffffff;
}

.verify-box {
    width: 100%;
    max-width: 420px;
}

/* Heading */
.verify-title {
    font-size: 22px;
    font-weight: 600;
    margin-bottom: 6px;
    color: #111;
}

/* Subtitle */
.verify-subtitle {
    font-size: 14px;
    color: #6b7280;
    margin-bottom: 28px;
    line-height: 1.5;
}

/* Input */
.verify-input {
    height: 48px;
    border-radius: 8px;
    font-size: 14px;
    padding: 0 14px;
}

/* Button (disabled look like image) */
.verify-btn {
    height: 48px;
    border-radius: 8px;
    background: #e5e5e5;
    color: #ffffff;
    font-weight: 600;
    border: none;
}

/* Footer */
.verify-footer {
    margin-top: 140px;
    font-size: 14px;
    color: #111;
}

.verify-footer a {
    color: #22c55e;
    font-weight: 500;
    text-decoration: none;
}

/* Terms */
.verify-terms {
    margin-top: 10px;
    font-size: 11px;
    color: #9ca3af;
}

.verify-terms a {
    color: #111;
    font-weight: 500;
}
.auth-left {
    background-image: url('{{ asset("assets/images/login/login_image.jpeg") }}');
    background-size: cover;
    background-position: left center; 
    background-repeat: no-repeat;
    cursor: pointer;
}

</style>