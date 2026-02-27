<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Profile Approved - OOHAPP</title>
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
            🎉 Your Profile Has Been Approved!
        </h2>
        <p style="margin-top:8px; color:#666; font-size:14px;">
            Hi {{ $user->name }}, your vendor account is now live.
        </p>
    </td>
</tr>

<!-- SUCCESS BOX -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <div style="
        background:#ecfdf5;
        border-left:4px solid #16a34a;
        padding:15px;
        font-size:14px;
        color:#065f46;
    ">
        Your hoardings, DOOH screens, and media placements are now visible to advertisers on OOHAPP.
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
        <td style="padding:10px; font-size:13px;"><strong>Company Name</strong></td>
        <td style="padding:10px; font-size:13px;">
            {{ $user->vendorProfile->company_name ?? $user->company_name ?? '-' }}
        </td>
    </tr>

    <!-- <tr>
        <td style="padding:10px; font-size:13px;"><strong>Commission Rate</strong></td>
        <td style="padding:10px; font-size:13px;">
            <span style="background:#fef9c3; padding:3px 6px; font-weight:bold;">
                {{ env('ADMIN_COMMISSION_PERCENTAGE', 10) }}% - {{ $commissionPercentage }}%
            </span>
        </td>
    </tr> -->

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
        <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">
            Active
        </td>
    </tr>

    <tr>
        <td style="padding:10px; font-size:13px;"><strong>Dashboard URL</strong></td>
        <td style="padding:10px; font-size:13px;">
            <a href="https://staging.oohapp.io/vendor/dashboard">
                https://staging.oohapp.io/vendor/dashboard
            </a>
        </td>
    </tr>
</table>
</td>
</tr>

<!-- NEXT STEPS -->
<tr>
<td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>What’s Next?</strong></p>
    <ul style="padding-left:18px;">
        <li>Login to your vendor dashboard</li>
        <li>Manage your hoardings and DOOH screens</li>
        <li>Update pricing & availability</li>
        <li>Receive real-time booking enquiries</li>
        <li>Track earnings and commission payouts</li>
    </ul>
</td>
</tr>

<!-- BUTTON -->
<tr>
<td align="center" style="padding:25px 40px;">
    <a href="https://staging.oohapp.io/vendor/dashboard"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        Go to Dashboard
    </a>
</td>
</tr>

<!-- COMMISSION INFO -->
<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        <!-- Your approved commission rate is
        <strong>{{ env('ADMIN_COMMISSION_PERCENTAGE', 10) }}% - {{ $commissionPercentage }}%</strong>.
        This will be applied on successful bookings made via OOHAPP. -->
        Your Commission will be set By Admin
    </p>

    <p><strong>Need Help?</strong><br>
    Contact us at <strong>support@oohapp.com</strong></p>

    <p>Happy booking!<br><strong>Team OOHAPP</strong></p>
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
