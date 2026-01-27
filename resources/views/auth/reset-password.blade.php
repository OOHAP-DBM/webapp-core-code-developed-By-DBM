@extends('layouts.guest')

@section('title', 'Reset Password - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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

                <h3 class="text-start mb-3">Reset your password</h3>

                @if ($errors->any())
                    <div class="alert alert-danger border-0 shadow-sm rounded-3 py-3 ps-3 mb-3">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('password.update') }}">
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
                            autofocus
                        >
                    </div>

                    <div class="mb-2 text-start">
                        <input
                            type="password"
                            name="password"
                            class="form-control"
                            placeholder="New Password"
                            required
                        >
                    </div>

                    <div class="mb-3 text-start">
                        <input
                            type="password"
                            name="password_confirmation"
                            class="form-control"
                            placeholder="Confirm Password"
                            required
                        >
                    </div>

                    <button type="submit" class="btn btn-success w-100">
                        Reset Password
                    </button>
                </form>

                <div class="footer-text mt-4">
                    <a href="{{ route('login') }}" class="text-success">
                        Back to Login
                    </a>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
