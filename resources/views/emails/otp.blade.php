<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OTP Verification</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: #ffffff;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .logo {
            text-align: center;
            margin-bottom: 30px;
        }
        .logo h1 {
            color: #2563eb;
            margin: 0;
            font-size: 28px;
        }
        .otp-box {
            background-color: #f8fafc;
            border: 2px solid #2563eb;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            margin: 30px 0;
        }
        .otp-code {
            font-size: 36px;
            font-weight: bold;
            color: #2563eb;
            letter-spacing: 8px;
            margin: 10px 0;
        }
        .otp-label {
            color: #64748b;
            font-size: 14px;
            text-transform: uppercase;
            margin-bottom: 10px;
        }
        .warning {
            background-color: #fef3c7;
            border-left: 4px solid #f59e0b;
            padding: 15px;
            margin: 20px 0;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e5e7eb;
            color: #64748b;
            font-size: 12px;
        }
        .validity {
            color: #ef4444;
            font-weight: bold;
            font-size: 14px;
            text-align: center;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="logo" style="text-align:center; margin-bottom:30px;">
            <img src="https://staging.oohapp.io/assets/images/logo/logo_image.jpeg" 
                alt="OOHAPP Logo"
                width="180"
                style="display:block; margin:0 auto;">
        </div>   
        <h2 style="color: #1e293b; margin-bottom: 20px;">Email Verification Required</h2>
        
        <p>Hello,</p>
        
        <p>You have requested to verify your email address for your OOHAPP account. Please use the following One-Time Password (OTP) to complete your verification:</p>
        
        <div class="otp-box">
            <div class="otp-label">Your Verification Code</div>
            <div class="otp-code">{{ $otp }}</div>
        </div>
        
        <p class="validity">⏰ This code is valid for 5 minutes only</p>
        <div class="warning">
            <strong>⚠️ Security Notice:</strong><br>
            Never share this OTP with anyone. OOHAPP staff will never ask for your OTP.
        </div>
        <p>If you did not request this verification, please ignore this email or contact our support team immediately.</p>
        
        <div class="footer">
            <p><strong>OOHAPP - Out of Home Advertising Platform</strong></p>
            <p>This is an automated email. Please do not reply to this message.</p>
            <p>&copy; {{ date('Y') }} OOHAPP. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
