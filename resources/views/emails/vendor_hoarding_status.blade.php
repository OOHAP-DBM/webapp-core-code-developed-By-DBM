<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hoarding Status - OOHAPP</title>
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
            🏢 Hoarding Status Update
        </h2>
        <p style="margin-top:8px; color:#666; font-size:14px;">
            {{ $greeting }}
        </p>
    </td>
</tr>
<!-- STATUS BOX -->
<tr>
<td style="padding:20px 40px 0 40px;">
    @php
        $isNegative = in_array(strtolower($action), ['deactivate', 'deactivated', 'suspend', 'suspended']);
        $statusBg = $isNegative ? '#fef2f2' : '#ecfdf5';
        $statusBorder = $isNegative ? '#dc2626' : '#16a34a';
        $statusColor = $isNegative ? '#991b1b' : '#065f46';
    @endphp
    <div style="background:{{ $statusBg }}; border-left:4px solid {{ $statusBorder }}; padding:15px; font-size:14px; color:{{ $statusColor }};">
        {{ $mailMessage }}
    </div>
</td>
</tr>
<!-- DETAILS -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Hoarding:</strong> {{ $hoardingTitle }}
            </td>
        </tr>
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Status:</strong> {{ ucfirst($action) }}
            </td>
        </tr>
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Updated By:</strong> {{ $adminName }}
            </td>
        </tr>
    </table>
</td>
</tr>
<!-- ACTION BUTTON -->
<tr>
<td align="center" style="padding:30px 40px 40px 40px;">
    <a href="{{ $actionUrl }}" style="display:inline-block; background:#16a34a; color:#fff; font-weight:600; padding:12px 32px; border-radius:6px; text-decoration:none; font-size:16px;">
        View Hoarding
    </a>
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
