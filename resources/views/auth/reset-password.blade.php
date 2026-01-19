@extends('layouts.guest')

@section('title', 'Reset Password - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
@endpush

@section('content')
<div class="container-fluid auth-wrapper">
    <div class="row h-100">
        <div class="col-md-5 d-none d-md-block auth-left">
            <a href="{{ route('home') }}">
                <img src="{{ asset('assets/images/login/login_image.jpeg') }}" alt="OOHAPP">
            </a>
        </div>
        <div class="col-md-7 col-12 auth-right">
            <div class="signup-box">
                <h3 class="text-start mb-4">Reset Password</h3>
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
                <form method="POST" action="{{ route('password.update') }}">
                    @csrf
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" value="{{ $email ?? old('email') }}" required autofocus>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password" class="form-label">New Password</label>
                        <input type="password" name="password" id="password" class="form-control" required>
                    </div>
                    <div class="mb-3 text-start">
                        <label for="password_confirmation" class="form-label">Confirm Password</label>
                        <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Reset Password</button>
                </form>
                <div class="footer-text mt-4">
                    <a href="{{ route('login') }}">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
