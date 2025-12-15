<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - OohApp</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }
        .register-container {
            max-width: 500px;
            width: 100%;
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            padding: 40px;
        }
        .logo {
            text-align: center;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 10px;
        }
        .logo .ooh { color: #2d3436; }
        .logo .app { color: #00B894; }
        .register-header { text-align: center; margin-bottom: 30px; }
        .register-header h2 { font-size: 1.8rem; color: #2d3436; margin-bottom: 5px; }
        .register-header p { color: #636e72; font-size: 0.95rem; }
        .role-badge {
            display: inline-block;
            padding: 6px 16px;
            background: #00B894;
            color: #fff;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .nav-tabs { border: none; justify-content: center; margin-bottom: 24px; }
        .nav-tabs .nav-link {
            border: none;
            border-radius: 8px 8px 0 0;
            color: #636e72;
            font-weight: 600;
            background: #f1f2f6;
            margin: 0 4px;
        }
        .nav-tabs .nav-link.active {
            background: #00B894;
            color: #fff;
        }
        .tab-content > .tab-pane { padding-top: 0; }
        .form-label { font-weight: 600; color: #2d3436; margin-bottom: 8px; }
        .form-control { padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; transition: all 0.3s; }
        .form-control:focus { border-color: #00B894; box-shadow: 0 0 0 0.2rem rgba(0, 184, 148, 0.15); }
        .input-group-text { background: #f8f9fa; border: 2px solid #e0e0e0; border-right: none; }
        .input-group .form-control { border-left: none; }
        .btn-register { width: 100%; padding: 14px; font-size: 1.1rem; font-weight: 600; background: #00B894; border: none; border-radius: 8px; color: #fff; margin-top: 20px; transition: all 0.3s; }
        .btn-register:hover { background: #00a383; transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0, 184, 148, 0.3); }
        .login-link { text-align: center; margin-top: 20px; color: #636e72; }
        .login-link a { color: #00B894; text-decoration: none; font-weight: 600; }
        .change-role { text-align: center; margin-top: 10px; }
        .change-role a { color: #636e72; font-size: 0.9rem; text-decoration: none; }
        .change-role a:hover { text-decoration: underline; }
        .is-invalid { border-color: #dc3545; }
        .invalid-feedback { display: block; color: #dc3545; font-size: 0.875rem; margin-top: 5px; }
        .otp-group { display: flex; gap: 8px; }
        .otp-group input { width: 40px; text-align: center; font-size: 1.2rem; }
        .d-none { display: none !important; }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="logo">
            <span class="ooh">OOH</span><span class="app">APP</span>
        </div>
        <div class="register-header">
            <div class="role-badge">
                <i class="fas {{ $role === 'customer' ? 'fa-user' : 'fa-briefcase' }} me-2"></i>
                {{ ucfirst($role) }} Registration
            </div>
            <h2>Create Account</h2>
            <p>Sign up with your email or phone</p>
        </div>

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

        <ul class="nav nav-tabs" id="signupTab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="email-tab" data-bs-toggle="tab" data-bs-target="#signup-email" type="button" role="tab">Sign up with Email</button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="phone-tab" data-bs-toggle="tab" data-bs-target="#signup-phone" type="button" role="tab">Sign up with Phone</button>
            </li>
        </ul>
        <div class="tab-content">
            <!-- Email Signup -->
            <div class="tab-pane fade show active" id="signup-email" role="tabpanel">
                <form id="email-step1" class="mt-3">
                    @csrf
                    <div class="mb-3">
                        <label for="email" class="form-label">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" required placeholder="you@example.com">
                    </div>
                    <button type="button" class="btn btn-register" onclick="sendEmailOtp()">Send OTP</button>
                </form>
                <form id="email-step2" class="mt-3 d-none">
                    <div class="mb-3">
                        <label for="email_otp" class="form-label">Enter OTP sent to your email</label>
                        <input type="text" class="form-control" id="email_otp" name="email_otp" maxlength="6" required>
                    </div>
                    <button type="button" class="btn btn-register" onclick="verifyEmailOtp()">Verify OTP</button>
                </form>
                <form id="email-step3" class="mt-3 d-none" method="POST" action="{{ route('register.submit') }}">
                    @csrf
                    <input type="hidden" name="email" id="final_email">
                    <input type="hidden" name="email_verified" value="1">
                    <input type="hidden" name="role" value="{{ $role }}">
                    <div class="mb-3">
                        <label for="name_email" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name_email" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_email" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_email" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation_email" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation_email" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-register">Sign Up</button>
                </form>
            </div>
            <!-- Phone Signup -->
            <div class="tab-pane fade" id="signup-phone" role="tabpanel">
                <form id="phone-step1" class="mt-3">
                    @csrf
                    <div class="mb-3">
                        <label for="phone" class="form-label">Mobile Number</label>
                        <input type="tel" class="form-control" id="phone" name="phone" required placeholder="10-digit mobile number" maxlength="15">
                    </div>
                    <button type="button" class="btn btn-register" onclick="sendPhoneOtp()">Send OTP</button>
                </form>
                <form id="phone-step2" class="mt-3 d-none">
                    <div class="mb-3">
                        <label for="phone_otp" class="form-label">Enter OTP sent to your phone</label>
                        <input type="text" class="form-control" id="phone_otp" name="phone_otp" maxlength="6" required>
                    </div>
                    <button type="button" class="btn btn-register" onclick="verifyPhoneOtp()">Verify OTP</button>
                </form>
                <form id="phone-step3" class="mt-3 d-none" method="POST" action="{{ route('register.submit') }}">
                    @csrf
                    <input type="hidden" name="phone" id="final_phone">
                    <input type="hidden" name="phone_verified" value="1">
                    <input type="hidden" name="role" value="{{ $role }}">
                    <div class="mb-3">
                        <label for="name_phone" class="form-label">Full Name</label>
                        <input type="text" class="form-control" id="name_phone" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_phone" class="form-label">Password</label>
                        <input type="password" class="form-control" id="password_phone" name="password" required>
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation_phone" class="form-label">Confirm Password</label>
                        <input type="password" class="form-control" id="password_confirmation_phone" name="password_confirmation" required>
                    </div>
                    <button type="submit" class="btn btn-register">Sign Up</button>
                </form>
            </div>
        </div>
        <div class="login-link">
            Already have an account? <a href="{{ route('login') }}">Login</a>
        </div>
        <div class="change-role">
            <a href="{{ route('register.role-selection') }}">
                <i class="fas fa-arrow-left me-1"></i> Change Role
            </a>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Email OTP flow
        function sendEmailOtp() {
            let email = document.getElementById('email').value;
            if (!email) return alert('Please enter your email.');
            fetch('{{ route('register.sendEmailOtp') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({email})
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    document.getElementById('email-step1').classList.add('d-none');
                    document.getElementById('email-step2').classList.remove('d-none');
                } else {
                    alert(res.message || 'Failed to send OTP');
                }
            });
        }
        function verifyEmailOtp() {
            let email = document.getElementById('email').value;
            let otp = document.getElementById('email_otp').value;
            fetch('{{ route('register.verifyEmailOtp') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({email, otp})
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    document.getElementById('email-step2').classList.add('d-none');
                    document.getElementById('email-step3').classList.remove('d-none');
                    document.getElementById('final_email').value = email;
                } else {
                    alert(res.message || 'Invalid OTP');
                }
            });
        }
        // Phone OTP flow
        function sendPhoneOtp() {
            let phone = document.getElementById('phone').value;
            if (!phone) return alert('Please enter your phone number.');
            fetch('{{ route('register.sendPhoneOtp') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({phone})
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    document.getElementById('phone-step1').classList.add('d-none');
                    document.getElementById('phone-step2').classList.remove('d-none');
                } else {
                    alert(res.message || 'Failed to send OTP');
                }
            });
        }
        function verifyPhoneOtp() {
            let phone = document.getElementById('phone').value;
            let otp = document.getElementById('phone_otp').value;
            fetch('{{ route('register.verifyPhoneOtp') }}', {
                method: 'POST',
                headers: {'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}'},
                body: JSON.stringify({phone, otp})
            }).then(r => r.json()).then(res => {
                if (res.success) {
                    document.getElementById('phone-step2').classList.add('d-none');
                    document.getElementById('phone-step3').classList.remove('d-none');
                    document.getElementById('final_phone').value = phone;
                } else {
                    alert(res.message || 'Invalid OTP');
                }
            });
        }
    </script>
</body>
</html>