<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>New Direct Enquiry - OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

<!-- TITLE -->
<tr>
<td align="center" style="padding:25px 40px 0 40px;">
    <h2 style="margin:0; color:#2563eb; font-weight:600;">
        🚀 New Lead Alert!
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hello <strong>{{ $vendor->name }}</strong>, a potential advertiser is looking for hoardings in your service area.
    </p>
</td>
</tr>

<!-- INTRO -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    A client is planning an outdoor advertising campaign in 
    <strong style="color:#2563eb;">{{ $enquiry->location_city }}</strong>.  
    Please review the details below and respond quickly to increase your chances of winning this deal.
</td>
</tr>

<!-- CLIENT DETAILS -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#ecfdf5;">
    <th align="left" colspan="2" style="padding:10px; font-size:13px;">
        Client Information
    </th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px; width:35%;"><strong>Name</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->name }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Phone</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ $enquiry->formatted_phone }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Email</strong></td>
    <td style="padding:10px; font-size:13px;">
        <a href="mailto:{{ $enquiry->email }}" style="color:#2563eb; text-decoration:none;">
            {{ $enquiry->email }}
        </a>
    </td>
</tr>

</table>
</td>
</tr>

<!-- REQUIREMENTS -->
<tr>
<td style="padding:20px 40px 0 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Requirement</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Hoarding Type</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ str_replace(',', ', ', $enquiry->hoarding_type) }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>City</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ $enquiry->location_city }}
    </td>
</tr>

@if($enquiry->preferred_locations && count($enquiry->preferred_locations) > 0 && $enquiry->preferred_locations[0] !== 'To be discussed')
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Preferred Locations</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ implode(', ', $enquiry->preferred_locations) }}
    </td>
</tr>
@endif

@if($enquiry->preferred_modes)
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Preferred Contact Mode</strong></td>
    <td style="padding:10px; font-size:13px;">
        {{ implode(', ', $enquiry->preferred_modes) }}
    </td>
</tr>
@endif

</table>
</td>
</tr>

<!-- CLIENT MESSAGE -->
<tr>
<td style="padding:20px 40px;">
    <div style="background:#eff6ff; border-left:4px solid #2563eb; padding:12px; font-size:13px;">
        <strong>Client Message:</strong><br>
        {{ $enquiry->remarks }}
    </div>
</td>
</tr>

<!-- ACTION -->
<tr>
<td style="padding:20px 40px;">
    <div style="background:#ecfdf5; border-left:4px solid #16a34a; padding:14px; font-size:14px;">
        <strong>Next Steps:</strong><br>
        • Contact the client within 24 hours<br>
        • Share hoarding options and pricing<br>
        • Provide availability & campaign duration
    </div>
</td>
</tr>

<tr>
<td style="padding:20px 40px;">
    <a href="{{ route('vendor.dashboard') }}"
       style="display:inline-block; background:#2563eb; color:#fff; text-decoration:none; padding:12px 20px; border-radius:6px; font-weight:bold;">
        View & Respond in Dashboard
    </a>
</td>
</tr>

<!-- DISCLAIMER -->
<tr>
<td style="padding:20px 40px;">
<div style="margin-top:10px; padding-top:12px; border-top:1px dashed #ddd; font-size:11px; color:#777; line-height:1.6;">
<strong style="color:#555;">Disclaimer:</strong><br>
OOHAPP is a platform connecting advertisers and media owners.  
Pricing, negotiation, and execution are handled directly between you and the advertiser.
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
