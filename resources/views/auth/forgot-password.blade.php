@extends('layouts.guest')

@section('title', 'Forgot Password - OOHAPP')

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
                <h3 class="text-start mb-4">Forgot Password</h3>
                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif
                <form method="POST" action="{{ route('password.email') }}">
                    @csrf
                    <div class="mb-3 text-start">
                        <label for="email" class="form-label">Email address</label>
                        <input type="email" name="email" id="email" class="form-control" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-success w-100">Send Password Reset Link</button>
                </form>
                <div class="footer-text mt-4">
                    <a href="{{ route('login') }}">Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
