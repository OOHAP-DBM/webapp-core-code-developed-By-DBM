<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Email Verification - OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

<!-- TITLE -->
<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#1e293b; font-weight:600;">
        Email Verification Required
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Use the One-Time Password (OTP) below to verify your email address.
    </p>
</td>
</tr>

<!-- OTP BOX -->
<tr>
<td align="center" style="padding:30px 20px;">
    <table cellpadding="0" cellspacing="0" style="background:#f8fafc; border:2px solid #2563eb; border-radius:8px;">
        <tr>
            <td align="center" style="padding:18px 40px;">
                <div style="color:#64748b; font-size:13px; margin-bottom:8px;">
                    Your Verification Code
                </div>

                <div style="
                    color:#2563eb;
                    font-size:34px;
                    font-weight:bold;
                    letter-spacing:8px;
                ">
                    {{ $otp }}
                </div>
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- VALIDITY -->
<tr>
<td align="center" style="padding:0 40px 10px 40px;">
    <p style="margin:0; color:#ef4444; font-size:14px; font-weight:bold;">
        ⏰ This code is valid for 5 minutes only
    </p>
</td>
</tr>

<!-- INFO -->
<tr>
<td style="padding:10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>Hello,</p>

    <p>
        You requested to verify your email address for your OOHAPP account.
        Please enter the above OTP on the verification screen to continue.
    </p>
</td>
</tr>

<!-- SECURITY WARNING -->
<tr>
<td style="padding:10px 40px;">
    <div style="
        background:#fef3c7;
        border-left:4px solid #f59e0b;
        padding:14px;
        font-size:13px;
        color:#7c2d12;
    ">
        <strong>Security Notice:</strong><br>
        Never share this OTP with anyone. OOHAPP team will never ask for your OTP via phone, email, or message.
    </div>
</td>
</tr>

<!-- IGNORE MESSAGE -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        If you did not request this verification, you can safely ignore this email
        or contact our support team immediately.
    </p>
</td>
</tr>

<!-- SUPPORT -->
<tr>
<td align="center" style="padding:20px 40px 10px 40px;">
    <p style="margin:0; color:#333; font-weight:600;">Need help?</p>
    <p style="margin:8px 0 0 0; color:#666; font-size:14px;">
        Contact us at
        <a href="mailto:care@oohapp.com" style="color:#2563eb; text-decoration:none;">
            care@oohapp.com
        </a>
    </p>
</td>
</tr>

@include('emails.partials.footer')

</table>
</td>
</tr>
</table>

</body>
</html>
