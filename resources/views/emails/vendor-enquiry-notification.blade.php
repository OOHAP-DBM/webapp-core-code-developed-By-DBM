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
    <h2 style="margin:0; color:#16a34a; font-weight:600;">
        🎉 New Enquiry Received!
    </h2>
          🎉 New Enquiry Received!
        Hi {{ $vendor->name }}, an advertiser is interested in your media.
    </p>
          Hi {{ $vendor->name }}, an advertiser is interested in your media.
</tr>

<!-- INTRO -->

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        You have received a new advertising enquiry. Please review the campaign details
        and confirm your availability.
    </p>
</tr>

<!-- CUSTOMER DETAILS -->

<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f0fdf4;">
    <th align="left" colspan="2" style="padding:10px; font-size:13px;">Customer Information</th>
</tr>

<tr>
        <td style="padding:10px; font-size:13px;"><strong>Customer Name</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->customer->name ?? 'Not provided' }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Email</strong></td>
    <td style="padding:10px; font-size:13px;">
        <a href="mailto:{{ $enquiry->customer->email }}" style="color:#16a34a; text-decoration:none;">
            {{ $enquiry->customer->email ?? 'Not provided' }}
        </a>
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Contact</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->contact_number ?? 'Not provided' }}</td>
</tr>

</table>
</td>
</tr>

<!-- ENQUIRY DETAILS -->

<tr>
<td style="padding:20px 40px 0 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;">
     <td style="padding:12px; font-size:14px; color:#065f46;">
    <th align="left" style="padding:10px; font-size:13px;">Information</th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Enquiry ID</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->formatted_id }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Campaign Start</strong></td>
    <td style="padding:10px; font-size:13px;">
        @php
            $startDate = $items->first()?->preferred_start_date;
            echo $startDate ? \Carbon\Carbon::parse($startDate)->format('d M Y') : '-';
        @endphp
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Your Hoardings</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $items->count() }}</td>
</tr>

</table>
</td>
</tr>

<!-- HOARDINGS -->

<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="font-size:14px; color:#444;"><strong>Your Hoardings in this Enquiry</strong></p>
</td>
</tr>

@foreach($items as $item)

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
    <strong>Campain Duration:</strong> {{ $item->expected_duration }}
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
    <div style="background:#f0fdf4; border-left:4px solid #16a34a; padding:14px; font-size:14px;">
        <strong>Action Required:</strong><br>
        Please confirm your availability. After confirmation, quotation will be generated.
    </div>
</td>
</tr>

<tr>
    <td style="padding:20px 40px;"><div style="
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
</div></td>
</tr>

@include('emails.partials.footer')

</table>
</td>
</tr>
</table>

</body>
</html>
