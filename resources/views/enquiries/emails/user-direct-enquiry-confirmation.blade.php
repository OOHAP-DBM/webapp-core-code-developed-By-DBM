
<!DOCTYPE html>
<html>
<head>
	<meta charset="UTF-8">
	<title>Enquiry Confirmation - OOHAPP</title>
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
		Thank You for Your Enquiry! 🎯
	</h2>
	<p style="margin-top:8px; color:#666; font-size:14px;">
		Hi {{ $enquiry->name }}, we've received your hoarding enquiry and our team is on it!
	</p>
</td>
</tr>

<!-- ENQUIRY PANEL -->
<tr>
<td style="padding:15px 40px 0 40px;">
	<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
		<tr style="background:#f3f4f6;">
			<th align="left" style="padding:10px; font-size:13px;">Information</th>
			<th align="left" style="padding:10px; font-size:13px;">Details</th>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Enquiry ID</strong></td>
			<td style="padding:10px; font-size:13px;">#{{ str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Location</strong></td>
			<td style="padding:10px; font-size:13px;">{{ $enquiry->location_city }}</td>
		</tr>
		@if($enquiry->preferred_locations && count($enquiry->preferred_locations) > 0 && $enquiry->preferred_locations[0] !== 'To be discussed')
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Preferred Areas</strong></td>
			<td style="padding:10px; font-size:13px;">{{ implode(', ', $enquiry->preferred_locations) }}</td>
		</tr>
		@endif
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Hoarding Type</strong></td>
			<td style="padding:10px; font-size:13px;">{{ str_replace(',', ', ', $enquiry->hoarding_type) }}</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Submitted</strong></td>
			<td style="padding:10px; font-size:13px;">{{ $enquiry->created_at->format('d M Y, h:i A') }}</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Phone</strong></td>
			<td style="padding:10px; font-size:13px;">{{ $enquiry->formatted_phone }}</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Email</strong></td>
			<td style="padding:10px; font-size:13px;">{{ $enquiry->email }}</td>
		</tr>
	</table>
</td>
</tr>

<!-- NEXT STEPS TABLE -->
<tr>
<td style="padding:20px 40px 0 40px;">
	<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
		<tr style="background:#f3f4f6;">
			<th align="left" style="padding:10px; font-size:13px;">Timeline</th>
			<th align="left" style="padding:10px; font-size:13px;">Action</th>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Now</strong></td>
			<td style="padding:10px; font-size:13px;">We're notifying verified vendors in your area</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>Within 24 hours</strong></td>
			<td style="padding:10px; font-size:13px;">You'll start receiving quotes via {{ implode('/', $enquiry->preferred_modes ?? ['phone', 'email']) }}</td>
		</tr>
		<tr>
			<td style="padding:10px; font-size:13px;"><strong>24-48 hours</strong></td>
			<td style="padding:10px; font-size:13px;">Multiple vendors will reach out with their best offers</td>
		</tr>
	</table>
</td>
</tr>

<!-- CONTACT MODES -->
<tr>
<td style="padding:20px 40px 0 40px;">
	<p style="font-size:14px; color:#444; margin-bottom:8px;"><strong>Our vendors will contact you via:</strong></p>
	<ul style="padding-left:18px; margin:0;">
		@if($enquiry->preferred_modes && count($enquiry->preferred_modes) > 0)
			@foreach($enquiry->preferred_modes as $mode)
				<li style="margin-bottom:4px;">✅ {{ $mode }}</li>
			@endforeach
		@else
			<li>✅ Phone Call</li>
			<li>✅ Email</li>
		@endif
	</ul>
</td>
</tr>
<tr>
<td style="padding:10px 40px; font-size:14px; color:#444; line-height:22px;">
	<p><strong>Pro Tips While You Wait:</strong></p>
	<ul style="padding-left:18px;">
		<li>Keep your phone handy - Vendors move fast!</li>
		<li>Compare multiple quotes - Don't settle on the first offer</li>
		<li>Ask about visibility - Request foot traffic and vehicle count data</li>
		<li>Check availability - Popular spots get booked quickly</li>
	</ul>
</td>
</tr>

<!-- SUPPORT -->
<tr>
<td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
	<p style="margin-bottom:6px;">Need to update your requirements?</p>
	<p>Contact us at:<br>
		📧 Email: support@yourdomain.com<br>
		📱 Phone: +91-1234567890<br>
		💬 WhatsApp: +91-1234567890
	</p>
</td>
</tr>

<!-- DISCLAIMER -->
<tr>
<td style="padding:10px 40px 0 40px;">
	<div style="
		margin-top: 25px;
		padding-top: 12px;
		border-top: 1px dashed #ddd;
		font-size: 9px;
		color: #777;
		line-height: 1.5;
	">
		<strong style="color:#555;">Disclaimer:</strong>
		This is an automated confirmation. Please do not reply to this email. For any queries, contact us at support@yourdomain.com
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
