<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hoarding Approved - OOHAPP</title>
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
            🎉 Your Hoarding Has Been Approved & Published!
        </h2>
    </td>
</tr>

<!-- INTRO -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>Dear <strong>{{ $vendor_name }}</strong>,</p>

    <p>
        Your hoarding <strong>"{{ $hoarding->title }}"</strong> has been reviewed,
        approved, and is now live on the OOHAPP platform.
    </p>

    <p>
        Advertisers can now view your media and you may start receiving enquiries soon.
    </p>
</td>
</tr>

<!-- SUCCESS BOX -->
<tr>
<td style="padding:0 40px;">
    <div style="
        background:#ecfdf5;
        border-left:4px solid #16a34a;
        padding:14px;
        font-size:14px;
        color:#065f46;
    ">
        <strong>Status:</strong> Your hoarding is now active and visible to advertisers.
    </div>
</td>
</tr>

<!-- HOARDING DETAILS -->
<tr>
<td style="padding:20px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">

<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Hoarding Information</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Title</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $hoarding->title }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Location</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $hoarding->address ?? 'N/A' }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Category</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ ucfirst($hoarding->hoarding_type ?? 'N/A') }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Hoarding ID</strong></td>
    <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">
        {{ $hoarding_id }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Commission Rate</strong></td>
    <td style="padding:10px; font-size:13px;">
        <span style="background:#fef9c3; padding:3px 6px; font-weight:bold;">
            {{ $hoarding_commission ?? 'Not Set' }}%
        </span>
    </td>
</tr>

</table>
</td>
</tr>

<!-- NEXT STEPS -->
<tr>
<td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>What you can do now:</strong></p>
    <ul style="padding-left:18px;">
        <li>Update pricing and availability</li>
        <li>Add more photos for better visibility</li>
        <li>Create packages for advertisers</li>
        <li>Track enquiries from your dashboard</li>
    </ul>
</td>
</tr>

<!-- BUTTON -->
<tr>
<td align="center" style="padding:25px 40px;">
    <a href="https://staging.oohapp.io/vendor/hoardings"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        View My Hoardings
    </a>
</td>
</tr>

<!-- FOOT MESSAGE -->
<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        We appreciate your partnership and look forward to helping you
        maximize your media visibility and bookings.
    </p>

    <p>
        For any assistance, contact us at <strong>support@oohapp.in</strong>
    </p>

    <p>Best regards,<br><strong>Team OOHAPP</strong></p>
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
