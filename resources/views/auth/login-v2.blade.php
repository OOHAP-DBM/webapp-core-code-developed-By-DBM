@extends('layouts.guest')

@section('title', 'Login - OOHAPP')

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
<style>
    * { margin: 0; padding: 0; box-sizing: border-box; }
    
    body {
        font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        min-height: 100vh;
    }
    
    .auth-container {
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    
    .auth-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        max-width: 440px;
        width: 100%;
        padding: 48px 40px;
    }
    
    .brand-logo {
        text-align: center;
        margin-bottom: 32px;
    }
    
    .brand-logo h1 {
        font-size: 32px;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        margin-bottom: 8px;
    }
    
    .brand-logo p {
        color: #64748b;
        font-size: 15px;
    }
    
    .form-group {
        margin-bottom: 24px;
    }
    
    .form-label {
        font-weight: 500;
        font-size: 14px;
        color: #334155;
        margin-bottom: 8px;
        display: block;
    }
    
    .form-control-custom {
        width: 100%;
        padding: 14px 16px;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        font-size: 15px;
        transition: all 0.3s;
        outline: none;
    }
    
    .form-control-custom:focus {
        border-color: #667eea;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-control-custom::placeholder {
        color: #94a3b8;
    }
    
    .btn-primary-custom {
        width: 100%;
        padding: 14px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border: none;
        border-radius: 12px;
        color: white;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    
    .btn-primary-custom:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.4);
    }
    
    .btn-primary-custom:active {
        transform: translateY(0);
    }
    
    .divider {
        display: flex;
        align-items: center;
        text-align: center;
        margin: 24px 0;
    }
    
    .divider::before,
    .divider::after {
        content: '';
        flex: 1;
        border-bottom: 1px solid #e2e8f0;
    }
    
    .divider span {
        padding: 0 16px;
        color: #94a3b8;
        font-size: 14px;
    }
    
    .btn-otp {
        width: 100%;
        padding: 14px;
        background: white;
        border: 2px solid #e2e8f0;
        border-radius: 12px;
        color: #334155;
        font-weight: 600;
        font-size: 15px;
        cursor: pointer;
        transition: all 0.2s;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    
    .btn-otp:hover {
        border-color: #667eea;
        background: #f8fafc;
    }
    
    .footer-links {
        text-align: center;
        margin-top: 24px;
        font-size: 14px;
        color: #64748b;
    }
    
    .footer-links a {
        color: #667eea;
        text-decoration: none;
        font-weight: 600;
    }
    
    .footer-links a:hover {
        text-decoration: underline;
    }
    
    .checkbox-group {
        display: flex;
        align-items: center;
        gap: 8px;
        margin-bottom: 20px;
    }
    
    .checkbox-group input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    
    .checkbox-group label {
        font-size: 14px;
        color: #64748b;
        cursor: pointer;
        margin: 0;
    }
    
    .alert-custom {
        padding: 12px 16px;
        border-radius: 12px;
        margin-bottom: 20px;
        font-size: 14px;
    }
    
    .alert-error {
        background: #fef2f2;
        color: #991b1b;
        border: 1px solid #fecaca;
    }
    
    .alert-success {
        background: #f0fdf4;
        color: #166534;
        border: 1px solid #bbf7d0;
    }
</style>
@endpush

@section('content')
<div class="auth-container">
    <div class="auth-card">
        <!-- Brand Logo -->
        <div class="brand-logo">
            <h1>OOHAPP</h1>
            <p>Welcome back! Please login to your account</p>
        </div>

        <!-- Error Messages -->
        @if ($errors->any())
            <div class="alert-custom alert-error">
                <i class="bi bi-exclamation-circle"></i>
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Success Messages -->
        @if (session('success'))
            <div class="alert-custom alert-success">
                <i class="bi bi-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        <!-- Login Form -->
        <form method="POST" action="{{ route('login') }}">
            @csrf

            <!-- Email or Phone -->
            <div class="form-group">
                <label class="form-label">Email or Phone Number</label>
                <input 
                    type="text" 
                    name="identifier" 
                    class="form-control-custom" 
                    placeholder="Enter your email or phone"
                    value="{{ old('identifier') }}"
                    required
                    autofocus
                >
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label">Password</label>
                <input 
                    type="password" 
                    name="password" 
                    class="form-control-custom" 
                    placeholder="Enter your password"
                    required
                >
            </div>

            <!-- Remember Me & Forgot Password -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="checkbox-group mb-0">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">Remember me</label>
                </div>
                {{-- <a href="{{ route('password.request') }}" style="font-size: 14px; color: #667eea; text-decoration: none;">Forgot password?</a> --}}
                <span style="font-size: 14px; color: #94a3b8;">Forgot password?</span>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn-primary-custom">
                Sign In
            </button>
        </form>

        <!-- Divider -->
        <div class="divider">
            <span>OR</span>
        </div>

        <!-- OTP Login Button -->
        <a href="{{ route('login.otp') }}" class="btn-otp">
            <i class="bi bi-shield-lock"></i>
            Login with OTP
        </a>

        <!-- Footer Links -->
        <div class="footer-links">
            Don't have an account? <a href="{{ route('register.role-selection') }}">Sign Up</a>
        </div>
    </div>
</div>
@endsection
