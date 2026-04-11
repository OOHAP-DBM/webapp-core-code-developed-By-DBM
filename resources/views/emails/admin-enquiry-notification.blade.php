<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Enquiry Received - OOHAPP</title>
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
    <h2 style="margin:0; color:#2563eb; font-weight:600;">
         New Enquiry Received
    </h2>
    <p style="margin:0; color:#444; font-size:15px;">
        Hello Admin, a new enquiry has been submitted on OOHAPP.
    </p>
</td>
</tr>
<!-- ENQUIRY SUMMARY -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        <strong>Customer Name :</strong> {{ $customerName }}<br>
        <strong>Total Hoardings:</strong> {{ $totalItems }}<br>
        <strong>Multi-Vendor:</strong> {{ $isMultiVendor ? 'Yes' : 'No' }}
    </p>
</td>
</tr>
<!-- ENQUIRY DETAILS TABLE -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f0fdf4;">
    <th align="left" colspan="2" style="padding:10px; font-size:13px;">Enquiry Information</th>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Enquiry ID</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->formatted_id ?? $enquiry->id }}</td>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
    <td style="padding:10px; font-size:13px;">{{ ucfirst($enquiry->status) }}</td>
</tr>
<tr>
    <td style="padding:10px; font-size:13px;"><strong>Submitted At</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->created_at->format('d M Y, h:i A') }}</td>
</tr>
</table>
</td>
</tr>
<!-- HOARDINGS LIST -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="font-size:14px; color:#444;"><strong>Hoardings in this Enquiry</strong></p>
</td>
</tr>
@foreach($enquiry->items as $item)
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f0fdf4;">
<td style="padding:12px; font-size:14px; color:#166534;">
    <strong>{{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}</strong>
</td>
</tr>
<tr>
<td style="padding:10px; font-size:13px;">
    📍 {{ $item->hoarding->display_location ?? 'Location not specified' }}
</td>
</tr>
<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Type:</strong> {{ strtoupper($item->hoarding_type) }}
</td>
</tr>
@if($item->package_type === 'package')
<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Package:</strong> {{ $item->package_label ?? 'Standard Package' }}
</td>
</tr>
@endif
@if($item->expected_duration)
<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Campaign Duration:</strong> {{ $item->expected_duration }}
</td>
</tr>
@endif
@if($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs']))
<tr>
<td style="padding:10px; font-size:13px; background:#dcfce7;">
    <strong>DOOH Specs:</strong><br>
    Video: {{ $item->meta['dooh_specs']['video_duration'] ?? 15 }} sec |
    Slots/Days: {{ $item->meta['dooh_specs']['slots_per_day'] ?? 120 }} |
    Days: {{ $item->meta['dooh_specs']['total_days'] ?? 0 }}
</td>
</tr>
@endif
</table>
</td>
</tr>
@endforeach
<!-- CUSTOMER MESSAGE -->
@if($enquiry->customer_note)
<tr>
<td style="padding:20px 40px;">
    <div style="background:#eff6ff; border-left:4px solid #2563eb; padding:12px; font-size:13px;">
        <strong>Customer Message:</strong><br>
        {{ $enquiry->customer_note }}
    </div>
</td>
</tr>
@endif
<!-- ACTION BOX -->
<tr>
<td style="padding:20px 40px;">
    <div style="background:#f0fdf4; border-left:4px solid #2563eb; padding:14px; font-size:14px;">
        <strong>Action Required:</strong><br>
        Please review this enquiry and coordinate with vendors as needed. <br>
        <a href="{{ url('/admin/enquiries/' . $enquiry->id) }}" style="display:inline-block; margin-top:10px; background:#2563eb; color:#fff; padding:10px 18px; border-radius:4px; text-decoration:none; font-weight:600;">View in Admin Panel</a>
    </div>
</td>
</tr>
<tr>
    <td style="padding:20px 40px;"><div style="margin-top: 25px; padding-top: 12px; border-top: 1px dashed #ddd; font-size: 9px; color: #777; line-height: 1.5;"><strong style="color:#555;">Disclaimer:</strong> OOHAPP provides a platform to connect advertisers and vendors. All quotations, pricing, timelines, and execution details are managed by vendors. OOHAPP acts as a facilitator and does not participate in pricing or execution decisions.</div></td>
</tr>
@include('emails.partials.footer')
</table>
</td>
</tr>
</table>
</body>
</html>
