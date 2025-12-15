<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - OohApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            display: flex;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .left-section {
            background: #000;
            color: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
            position: relative;
            overflow: hidden;
        }
        
        .left-section::before {
            content: '';
            position: absolute;
            bottom: -50px;
            right: -50px;
            width: 600px;
            height: 600px;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            border-radius: 50%;
        }
        
        .billboard-illustration {
            max-width: 400px;
            margin-bottom: 40px;
            z-index: 1;
        }
        
        .logo {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
            letter-spacing: 2px;
        }
        
        .logo .ooh {
            color: #fff;
        }
        
        .logo .app {
            color: #00B894;
        }
        
        .tagline {
            font-size: 1.1rem;
            color: #ddd;
            text-align: center;
            max-width: 400px;
        }
        
        .right-section {
            background: #fff;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px 40px;
        }
        
        .login-form {
            max-width: 450px;
            width: 100%;
        }
        
        .login-form h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 10px;
        }
        
        .login-form p {
            color: #636e72;
            margin-bottom: 30px;
        }
        
        .form-label {
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 8px;
        }
        
        .form-control {
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            transition: all 0.3s;
        }
        
        .form-control:focus {
            border-color: #00B894;
            box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.15);
        }
        
        .input-group-text {
            background: #f8f9fa;
            border: 2px solid #e0e0e0;
            border-right: none;
        }
        
        .input-group .form-control {
            border-left: none;
        }
        
        .btn-login {
            width: 100%;
            padding: 14px;
            font-size: 1.1rem;
            font-weight: 600;
            background: #00B894;
            border: none;
            border-radius: 8px;
            color: #fff;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #00a383;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 184, 148, 0.3);
        }
        
        .form-check-input:checked {
            background-color: #00B894;
            border-color: #00B894;
        }
        
        .forgot-password {
            color: #00B894;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        .forgot-password:hover {
            text-decoration: underline;
        }
        
        .signup-link {
            text-align: center;
            margin-top: 30px;
            color: #636e72;
        }
        
        .signup-link a {
            color: #00B894;
            text-decoration: none;
            font-weight: 600;
        }
        
        .signup-link a:hover {
            text-decoration: underline;
        }
        
        .is-invalid {
            border-color: #dc3545;
        }
        
        .invalid-feedback {
            display: block;
            color: #dc3545;
            font-size: 0.875rem;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Section -->
            <div class="col-lg-6 left-section d-none d-lg-flex">
                <div class="billboard-illustration">
                    <svg viewBox="0 0 400 300" xmlns="http://www.w3.org/2000/svg">
                        <!-- Billboard Structure -->
                        <rect x="50" y="40" width="300" height="180" rx="8" fill="#f0f0f0" stroke="#333" stroke-width="3"/>
                        <line x1="200" y1="220" x2="200" y2="280" stroke="#555" stroke-width="6"/>
                        <line x1="180" y1="280" x2="220" y2="280" stroke="#555" stroke-width="8"/>
                        <circle cx="200" cy="130" r="50" fill="#00B894" opacity="0.7"/>
                        <text x="200" y="140" font-size="20" fill="#fff" text-anchor="middle" font-weight="bold">OOH</text>
                    </svg>
                </div>
                <div class="logo">
                    <span class="ooh">OOH</span><span class="app">APP</span>
                </div>
                <p class="tagline">"Empowering brands to rise above the skyline."</p>
            </div>

            <!-- Right Section -->
            <div class="col-lg-6 right-section">
                <div class="login-form">
                    <h2>Welcome Back!</h2>
                    <p>Sign in to continue to OohApp</p>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('info'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ session('info') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('login.submit') }}" method="POST">
                        @csrf

                        <!-- Email or Phone -->
                        <div class="mb-3">
                            <label for="login" class="form-label">Email or Mobile Number</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-user"></i>
                                </span>
                                <input 
                                    type="text" 
                                    class="form-control @error('login') is-invalid @enderror" 
                                    id="login" 
                                    name="login" 
                                    value="{{ old('login') }}" 
                                    placeholder="Enter your email or phone number"
                                    required
                                    autofocus
                                >
                            </div>
                            @error('login')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Password -->
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <div class="input-group">
                                <span class="input-group-text">
                                    <i class="fas fa-lock"></i>
                                </span>
                                <input 
                                    type="password" 
                                    class="form-control @error('password') is-invalid @enderror" 
                                    id="password" 
                                    name="password" 
                                    placeholder="Enter your password"
                                    required
                                >
                                <button class="btn btn-outline-secondary" type="button" onclick="togglePassword()">
                                    <i class="fas fa-eye" id="password-eye"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Remember Me & Forgot Password -->
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <div class="form-check">
                                <input 
                                    class="form-check-input" 
                                    type="checkbox" 
                                    id="remember" 
                                    name="remember"
                                    {{ old('remember') ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="remember">
                                    Remember Me
                                </label>
                            </div>
                            <a href="#" class="forgot-password">Forgot Password?</a>
                        </div>

                        <button type="submit" class="btn btn-login">
                            Sign In <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="signup-link">
                        Don't have an account? <a href="{{ route('register.role-selection') }}">Sign Up</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function togglePassword() {
            const field = document.getElementById('password');
            const icon = document.getElementById('password-eye');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
