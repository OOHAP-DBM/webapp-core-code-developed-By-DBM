<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>{{ $subject }} - OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

<tr>
    <td align="center" style="padding:20px 40px 0 40px;">
        <h2 style="margin:0; color:#16a34a; font-weight:600;">
            {{ $type === 'set' ? '💰 Your Commission Has Been Set' : '🔄 Your Commission Has Been Updated' }}
        </h2>
    </td>
</tr>

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>Dear <strong>{{ $vendorName }}</strong>,</p>
    <p>{{ $intro }}</p>
</td>
</tr>

<tr>
<td style="padding:0 40px;">
    <div style="background:#ecfdf5; border-left:4px solid #16a34a; padding:14px; font-size:14px; color:#065f46;">
        <strong>Status:</strong>
        {{ $type === 'set' ? 'Commission is now configured for your account.' : 'Updated commission is active immediately.' }}
    </div>
</td>
</tr>

<tr>
<td style="padding:20px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">

<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Commission Information</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>

@if(!empty($hoardingName))
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Hoarding</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $hoardingName }}</td>
</tr>
@endif

@if($commissionType === 'all')
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Commission Rate</strong></td>
    <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">{{ $commission }}% (OOH & DOOH)</td>
</tr>
@else
@if($oohCommission !== null)
<tr>
    <td style="padding:10px; font-size:13px;"><strong>OOH Commission</strong></td>
    <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">{{ $oohCommission }}%</td>
</tr>
@endif
@if($doohCommission !== null)
<tr>
    <td style="padding:10px; font-size:13px;"><strong>DOOH Commission</strong></td>
    <td style="padding:10px; font-size:13px; color:#16a34a; font-weight:bold;">{{ $doohCommission }}%</td>
</tr>
@endif
@endif

</table>
</td>
</tr>

<tr>
<td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
    @if($type === 'set')
        <p>By continuing to use our platform, you agree to the commission rate mentioned above.</p>
    @else
        <p>This updated rate is effective immediately for incoming business.</p>
    @endif
</td>
</tr>

<tr>
<td align="center" style="padding:25px 40px;">
    <a href="{{ $actionUrl ?? $dashboardUrl }}"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        View My Commission
    </a>
</td>
</tr>

<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>If you have any questions or concerns, contact us at <strong>support@oohapp.in</strong>.</p>
    <p>Best regards,<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

<tr>
    <td style="padding:10px 40px 7px 40px;">
        <div style="margin-top: 25px; padding-top: 12px; border-top: 1px dashed #ddd; font-size: 9px; color: #777; line-height: 1.5;">
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
