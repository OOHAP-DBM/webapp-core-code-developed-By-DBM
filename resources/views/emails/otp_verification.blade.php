<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>OOHAPP Email Verification</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

    <!-- Main Container -->
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        @include('emails.partials.header')

        <!-- Title -->
        <tr>
            <td align="center" style="padding:10px 40px 0 40px;">
                <h2 style="margin:0; color:#222; font-weight:600;">Email Verification</h2>
                <p style="margin-top:8px; color:#666; font-size:14px;">
                    Please use the verification code below to verify your email address.
                </p>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:30px 20px;">
                <table cellpadding="0" cellspacing="0">
                    <tr>
                        <td align="center"
                            style="color:#15803d;
                                   font-size:32px;
                                   letter-spacing:6px;
                                   font-weight:bold;
                                   padding:18px 40px;
                                   border-radius:8px;">
                            {{ $otp }}
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:0 40px 25px 40px;">
                <p style="margin:0; color:#555; font-size:14px; line-height:22px;">
                    This OTP is valid for <strong>1 minutes</strong>.<br>
                    If you did not request this verification, you can safely ignore this email.
                </p>
            </td>
        </tr>
        <tr>
            <td style="padding:0 40px;">
                <hr style="border:none; border-top:1px solid #e5e7eb;">
            </td>
        </tr>
        <tr>
            <td align="center" style="padding:25px 40px 10px 40px;">
                <p style="margin:0; color:#333; font-weight:600;">Need help?</p>
                <p style="margin:8px 0 0 0; color:#666; font-size:14px;">
                    Contact us at
                    <a href="mailto:care@oohapp.com" style="color:#22c55e; text-decoration:none;">
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
