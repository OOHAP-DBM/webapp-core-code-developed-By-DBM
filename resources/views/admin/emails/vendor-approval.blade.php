<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Vendor Approval Pending - OOHAPP</title>
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
    <h2 style="margin:0; color:#f59e0b; font-weight:600;">
        Vendor Approval Required ⚠️
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        A new vendor has registered and is awaiting approval.
    </p>
</td>
</tr>

<!-- VENDOR DETAILS -->
<tr>
<td style="padding:15px 40px 0 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:13px;">Field</th>
            <th align="left" style="padding:10px; font-size:13px;">Details</th>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Name</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $name }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Email</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $email }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Registered At</strong></td>
            <td style="padding:10px; font-size:13px;">{{ now()->format('d M Y, h:i A') }}</td>
        </tr>
    </table>
</td>
</tr>

<!-- ACTION -->
@if(!empty($actionUrl))
<tr>
<td align="center" style="padding:25px 40px;">
    <a href="{{ $actionUrl }}" 
       style="background:#16a34a; color:#fff; padding:12px 20px; text-decoration:none; border-radius:6px; font-size:14px; display:inline-block;">
        {{ $actionText ?? 'View Vendor' }}
    </a>
</td>
</tr>
@endif

<!-- MESSAGE -->
<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>{{ $footer ?? 'Please review and approve the vendor registration.' }}</p>
</td>
</tr>

<!-- DISCLAIMER -->
<tr>
<td style="padding:10px 40px 0 40px;">
    <div style="
        margin-top: 20px;
        padding-top: 12px;
        border-top: 1px dashed #ddd;
        font-size: 10px;
        color: #777;
        line-height: 1.5;
    ">
        <strong style="color:#555;">Note:</strong>
        This is an automated email. Please do not reply directly.
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