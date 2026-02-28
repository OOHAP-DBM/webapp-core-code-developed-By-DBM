<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Booking Confirmation - OOHAPP</title>
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
        Booking Confirmation ✅
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $customer->name }}, your booking has been created successfully.
    </p>
</td>
</tr>

<!-- INTRO -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        Thank you for booking with OOHAPP. Your campaign is now in process and our team will coordinate with you for the next steps.
    </p>
</td>
</tr>

<!-- BOOKING DETAILS -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Information</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Booking ID</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $booking->invoice_number ?? $booking->id }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
    <td style="padding:10px; font-size:13px;">
        <span style="background:#fef3c7; padding:4px 8px; font-weight:bold;">
            {{ ucfirst($booking->status) }}
        </span>
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Campaign Start Date</strong></td>
    <td style="padding:10px; font-size:13px;">
        @php
            $startDate = $booking->start_date;
            $formattedDate = $startDate
                ? \Carbon\Carbon::parse($startDate)->format('d M Y')
                : 'N/A';
        @endphp
        {{ $formattedDate }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Number of Hoardings</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $booking->bookingHoardings->count() ?? 1 }}</td>
</tr>
</table>
</td>
</tr>

<!-- HOARDING LIST -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="font-size:14px; color:#444;"><strong>Booked Hoardings</strong></p>
</td>
</tr>

@foreach($booking->bookingHoardings as $item)
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb; margin-bottom:12px;">

<tr style="background:#ecfdf5;">
<td style="padding:12px; font-size:14px; color:#065f46;">
    <strong>
        {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
    </strong>
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    📍 {{ $item->hoarding->display_location ?? 'Location not specified' }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Type:</strong> {{ strtoupper($item->hoarding->hoarding_type ?? '-') }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Duration:</strong> {{ $item->duration_days ?? 'N/A' }} days
</td>
</tr>

</table>
</td>
</tr>
@endforeach

<!-- NEXT STEPS -->
<tr>
<td style="padding:10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>What happens next?</strong></p>
    <ul style="padding-left:18px;">
        <li>We verify campaign details and availability</li>
        <li>Vendor confirms campaign feasibility</li>
        <li>You receive updates and can track your booking</li>
        <li>Proceed with payment and campaign execution</li>
    </ul>
</td>
</tr>

<!-- BUTTON -->


<!-- SUPPORT -->
<tr>
<td style="padding:0 20px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>If you have any questions, contact us at <strong>support@oohapp.com</strong></p>
    <p>Thank you for choosing OOHAPP.<br><strong>Team OOHAPP</strong></p>
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
        OOHAPP connects advertisers with media owners. Pricing, availability and execution
        are managed directly by the vendor. OOHAPP acts only as a facilitating platform.
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