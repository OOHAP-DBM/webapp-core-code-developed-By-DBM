<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Your Role - OohApp</title>
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
        
        .role-selection {
            max-width: 500px;
            width: 100%;
        }
        
        .role-selection h2 {
            font-size: 2rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 10px;
        }
        
        .role-selection p {
            color: #636e72;
            margin-bottom: 40px;
        }
        
        .role-card {
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 30px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .role-card:hover {
            border-color: #00B894;
            box-shadow: 0 8px 20px rgba(0, 184, 148, 0.2);
            transform: translateY(-4px);
        }
        
        .role-card.selected {
            border-color: #00B894;
            background: #f0fdf7;
            box-shadow: 0 8px 20px rgba(0, 184, 148, 0.3);
        }
        
        .role-icon {
            width: 80px;
            height: 80px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #00B894, #00cec9);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 2rem;
        }
        
        .role-card h4 {
            font-size: 1.5rem;
            font-weight: 600;
            color: #2d3436;
            margin-bottom: 0;
        }
        
        .btn-continue {
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
        
        .btn-continue:hover:not(:disabled) {
            background: #00a383;
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(0, 184, 148, 0.3);
        }
        
        .btn-continue:disabled {
            background: #b2bec3;
            cursor: not-allowed;
        }
        
        .login-link {
            margin-top: 30px;
            text-align: center;
            color: #636e72;
        }
        
        .login-link a {
            color: #00B894;
            text-decoration: none;
            font-weight: 600;
        }
        
        .login-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row min-vh-100">
            <!-- Left Section -->
            <div class="col-lg-6 left-section">
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
                <div class="role-selection">
                    <h2>What you are?</h2>
                    <p>Select your role</p>

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <form action="{{ route('register.store-role') }}" method="POST" id="roleForm">
                        @csrf
                        <input type="hidden" name="role" id="selectedRole" value="">

                        <!-- Customer Card -->
                        <div class="role-card" onclick="selectRole('customer')">
                            <div class="role-icon">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4>Customer</h4>
                        </div>

                        <!-- Vendor Card -->
                        <div class="role-card" onclick="selectRole('vendor')">
                            <div class="role-icon">
                                <i class="fas fa-briefcase"></i>
                            </div>
                            <h4>Vendor</h4>
                        </div>

                        <button type="submit" class="btn btn-continue" id="continueBtn" disabled>
                            Continue <i class="fas fa-arrow-right ms-2"></i>
                        </button>
                    </form>

                    <div class="login-link">
                        Already Have an Account? <a href="{{ route('login') }}">Login</a>
                    </div>

                    <div class="mt-4 text-center" style="font-size: 0.85rem; color: #999;">
                        By clicking continue button, you agree with the 
                        <a href="#" style="color: #00B894; text-decoration: none;">Terms & Conditions</a> and 
                        <a href="#" style="color: #00B894; text-decoration: none;">Privacy policy</a> of OOHAPP App.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        let selectedRoleValue = '';

        function selectRole(role) {
            // Remove selected class from all cards
            document.querySelectorAll('.role-card').forEach(card => {
                card.classList.remove('selected');
            });

            // Add selected class to clicked card
            event.currentTarget.classList.add('selected');

            // Update hidden input
            selectedRoleValue = role;
            document.getElementById('selectedRole').value = role;

            // Enable continue button
            document.getElementById('continueBtn').disabled = false;
        }
    </script>
</body>
</html>
