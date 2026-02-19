<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vendor Account Activated - OOHAPP</title>
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
        <h2 style="margin:0; color:#16a34a; font-weight:600;">
            ✅ Your Vendor Account is Active Again!
        </h2>
        <p style="margin-top:8px; color:#666; font-size:14px;">
            Hi {{ $name }}, your vendor account has been successfully re-activated.
        </p>
    </td>
</tr>

<!-- INFO BOX -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <div style="
        background:#ecfdf5;
        border-left:4px solid #16a34a;
        padding:15px;
        font-size:14px;
        color:#065f46;
    ">
        You can now login to your dashboard, manage your hoardings and start receiving advertiser enquiries again.
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
        <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
        <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">
            Active
        </td>
    </tr>

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Dashboard URL</strong></td>
        <td style="padding:10px; font-size:13px;">
            <a href="{{ url('/vendor/dashboard') }}">
                {{ url('/vendor/dashboard') }}
            </a>
        </td>
    </tr>
</table>
</td>
</tr>

<!-- NEXT STEPS -->
<tr>
<td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>You can now:</strong></p>
    <ul style="padding-left:18px;">
        <li>Login to vendor dashboard</li>
        <li>Manage hoardings & DOOH screens</li>
        <li>Update pricing and availability</li>
        <li>Receive booking enquiries</li>
        <li>Track orders and earnings</li>
    </ul>
</td>
</tr>

<!-- BUTTON -->
<tr>
<td align="center" style="padding:25px 40px;">
    <a href="{{ url('/vendor/dashboard') }}"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        Login to Dashboard
    </a>
</td>
</tr>

<!-- HELP -->
<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">

    <p>
        If you were previously unable to login, please try again now — your access has been restored.
    </p>

    <p><strong>Need Help?</strong><br>
    Contact us at <strong>support@oohapp.com</strong></p>

    <p>Welcome back!<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

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
        <strong style="color:#555;">Disclaimer:</strong>
        OOHAPP provides a platform to connect you with interested advertisers.
                All quotations, pricing, timelines, and execution details are shared by you directly.
                Any discussion or confirmation happens between you and the advertiser.
                OOHAPP acts as a facilitator and does not participate in pricing or execution decisions.
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
