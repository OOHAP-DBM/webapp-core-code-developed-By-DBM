<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vendor Account Suspended - OOHAPP</title>
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
        <h2 style="margin:0; color:#dc2626; font-weight:600;">
            ⚠️ Vendor Account Temporarily Suspended
        </h2>
        <p style="margin-top:8px; color:#666; font-size:14px;">
            Hi {{ $name }}, your vendor account access has been temporarily restricted.
        </p>
    </td>
</tr>

<!-- WARNING BOX -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <div style="
        background:#fef2f2;
        border-left:4px solid #dc2626;
        padding:15px;
        font-size:14px;
        color:#7f1d1d;
    ">
        You will not be able to login to your vendor dashboard or receive advertiser enquiries until the account is reactivated.
    </div>
</td>
</tr>

<!-- DETAILS TABLE -->
<tr>
<td style="padding:20px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
    <tr style="background:#f3f4f6;">
        <th align="left" style="padding:10px; font-size:13px;">Information</th>
        <th align="left" style="padding:10px; font-size:13px;">Details</th>
    </tr>

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Vendor Name</strong></td>
        <td style="padding:10px; font-size:13px;">
            {{ $name }}
        </td>
    </tr>

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Login Email</strong></td>
        <td style="padding:10px; font-size:13px;">
            {{ $email }}
        </td>
    </tr>

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Account Status</strong></td>
        <td style="padding:10px; font-size:13px; color:#dc2626; font-weight:bold;">
            Suspended
        </td>
    </tr>
</table>
</td>
</tr>

<!-- WHY SECTION -->
<tr>
<td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>Why did this happen?</strong></p>
    <ul style="padding-left:18px;">
        <li>Incomplete or incorrect listing information</li>
        <li>Policy or compliance review</li>
        <li>Temporary administrative hold</li>
        <li>Quality or verification checks</li>
    </ul>
</td>
</tr>

<!-- SUPPORT -->
<tr>
<td style="padding:10px 40px 25px 40px; font-size:14px; color:#444; line-height:22px;">

    <p>
        This suspension may be temporary. Once reviewed, your account may be restored automatically or after verification.
    </p>

    <p>
        If you believe this was done in error or want to reactivate your account, please contact our support team.
    </p>

    <div style="margin:20px 0;">
        <a href="mailto:support@oohapp.com"
           style="background:#dc2626; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
            Contact Support
        </a>
    </div>

    <p>
        Support Email: <strong>support@oohapp.com</strong>
    </p>

    <p>
        Regards,<br>
        <strong>Team OOHAPP</strong>
    </p>
</td>
</tr>

<!-- FOOTER NOTE -->
<tr>
<td style="padding:10px 40px 7px 40px;">
    <div style="
        margin-top: 25px;
        padding-top: 12px;
        border-top: 1px dashed #ddd;
        font-size: 9px;
        color: #777;
        line-height: 1.5;
    ">
        <strong style="color:#555;">Important:</strong>
        This is an automated system notification regarding your vendor account status.
        You are receiving this email because you are registered as a vendor on OOHAPP.
    </div>
</td>
</tr>

@include('emails.partials.footer')

</table>
</td>
</tr>
</table>

</body>
</html>
