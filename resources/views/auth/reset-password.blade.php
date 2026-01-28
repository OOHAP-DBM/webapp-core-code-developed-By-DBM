@extends('layouts.guest')

@section('title', 'Reset Password - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

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
        background: #ffffff;
    }

    /* FORM BOX */
    .signup-box {
        width: 100%;
        max-width: 380px;
        text-align: center;
    }

    .signup-box h3 {
        font-weight: 600;
        margin-bottom: 12px;
    }

    .form-control {
        height: 46px;
        border-radius: 6px;
        font-size: 14px;
    }

    .form-control:focus {
        box-shadow: none;
        border-color: #2bb57c;
    }

    /* Disable autofill bg */
    input:-webkit-autofill,
    input:-webkit-autofill:hover,
    input:-webkit-autofill:focus {
        -webkit-box-shadow: 0 0 0 1000px #fff inset !important;
        box-shadow: 0 0 0 1000px #fff inset !important;
        -webkit-text-fill-color: #111827 !important;
    }

    .btn-reset {
        height: 46px;
        border-radius: 8px;
        background: #2bb57c;
        color: #ffffff;
        font-weight: 500;
        border: none;
        transition: background 0.2s ease;
    }

    .btn-reset:hover {
        background: #239a68;
        color: #ffffff;
    }

    .footer-text {
        margin-top: 32px;
        font-size: 13px;
        color: #6b7280;
    }

    .footer-text a {
        text-decoration: none;
        font-weight: 500;
        color: #2bb57c;
    }

    @media (max-width: 768px) {
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

                <h3 class="text-start">Reset your password</h3>
                <p class="text-muted text-start mb-3" style="font-size:13px;">
                    Enter your email and new password below.
                </p>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 py-3 ps-3 mb-3 text-start">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li style="font-size:13px;">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}" autocomplete="off">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">

                    <div class="mb-2 text-start">
                        <input
                            type="email"
                            name="email"
                            class="form-control"
                            placeholder="Email"
                            value="{{ $email ?? old('email') }}"
                            required
                            autocomplete="off"
                            autocorrect="off"
                            autocapitalize="off"
                            spellcheck="false"
                        >
                    </div>

                    <div class="mb-2 text-start">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="New Password"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <div class="mb-3 text-start">
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm Password"
                            required
                            autocomplete="new-password"
                        >
                    </div>

                    <button type="submit" class="btn btn-reset w-100">
                        Reset Password
                    </button>
                </form>

                <div class="footer-text text-start">
                    <a href="{{ route('login') }}">
                        ← Back to Login
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
