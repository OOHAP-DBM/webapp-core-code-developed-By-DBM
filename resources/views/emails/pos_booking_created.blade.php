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
    @php
        $recipientType = $recipientType ?? 'customer';
        $isVendorRecipient = $recipientType === 'vendor';
        $isAdminRecipient = $recipientType === 'admin';
    @endphp
    <h2 style="margin:0; color:#16a34a; font-weight:600;">
        @if($isVendorRecipient)
            POS Booking Created ✅
        @elseif($isAdminRecipient)
            New POS Booking Alert ✅
        @else
            Booking Confirmation ✅
        @endif
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        @if($isVendorRecipient)
            Hi {{ $greetingName ?? 'Vendor' }}, you have successfully created this POS booking.
        @elseif($isAdminRecipient)
            Hi {{ $greetingName ?? 'Admin' }}, a new POS booking has been created and requires visibility.
        @else
            Hi {{ $greetingName ?? ($customer->name ?? ($booking->customer_name ?? 'Customer')) }}, your booking has been created successfully.
        @endif
    </p>
</td>
</tr>

<!-- INTRO -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    @if($isVendorRecipient)
        <p>
            This booking has been recorded in your POS panel. Please track payment status and complete the required actions before the payment deadline.
        </p>
    @elseif($isAdminRecipient)
        <p>
            A new POS booking has been created in the system. Review booking and payment progress as needed.
        </p>
    @else
        <p>
            Thank you for booking with OOHAPP. Your campaign is now in process and our team will coordinate with you for the next steps.
        </p>
    @endif
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

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Payment Mode</strong></td>
    <td style="padding:10px; font-size:13px;">
        @php
            $modeLabelMap = [
                'cash' => 'Cash',
                'credit_note' => 'Credit Note',
                'bank_transfer' => 'Bank Transfer',
                'cheque' => 'Cheque',
                'online' => 'Online / UPI',
            ];
            $paymentMode = $booking->payment_mode ?? null;
        @endphp
        {{ $modeLabelMap[$paymentMode] ?? ($paymentMode ? ucwords(str_replace('_', ' ', $paymentMode)) : 'N/A') }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Payment Details</strong></td>
    <td style="padding:10px; font-size:13px;">
        @php
            $paymentReference = $booking->payment_reference ?? null;
            $paymentNotes = $booking->payment_notes ?? null;
            $paymentMode = $booking->payment_mode ?? null;
        @endphp

        @if($paymentMode === 'bank_transfer')
            @if(!empty($paymentDetail))
                <div><strong>Bank:</strong> {{ $paymentDetail->bank_name ?? 'N/A' }}</div>
                <div><strong>Account Number:</strong> {{ $paymentDetail->account_number ?? 'N/A' }}</div>
                <div><strong>Account Holder:</strong> {{ $paymentDetail->account_holder ?? 'N/A' }}</div>
                <div><strong>IFSC:</strong> {{ $paymentDetail->ifsc_code ?? 'N/A' }}</div>
            @else
                <span>Bank details not available.</span>
            @endif
        @elseif(in_array($paymentMode, ['online', 'upi']))
            @if(!empty($paymentDetail))
                <div><strong>UPI ID:</strong> {{ $paymentDetail->upi_id ?? 'N/A' }}</div>
                @if(!empty($paymentQrUrl))
                    <div style="margin-top:8px;">
                        <div style="margin-bottom:6px;"><strong>QR Code:</strong></div>
                        <img src="{{ $paymentQrUrl }}" alt="UPI QR Code" style="width:130px; height:130px; border:1px solid #e5e7eb; padding:4px; background:#fff;">
                    </div>
                @endif
            @else
                <span>UPI details not available.</span>
            @endif
        @else
            @if($paymentReference)
                <div><strong>Reference:</strong> {{ $paymentReference }}</div>
            @endif

            @if($paymentNotes)
                <div><strong>Notes:</strong> {{ $paymentNotes }}</div>
            @endif

            @if(!$paymentReference && !$paymentNotes)
                <span>N/A</span>
            @endif
        @endif
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Pay Before</strong></td>
    <td style="padding:10px; font-size:13px;">
        @if(!empty($booking->hold_expiry_at))
            {{ \Carbon\Carbon::parse($booking->hold_expiry_at)->format('d M Y, h:i A') }}
        @elseif(!empty($booking->hold_minutes))
            Within {{ (int) $booking->hold_minutes }} minute(s) from booking time
        @else
            As per platform payment timeline
        @endif
    </td>
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
    @if(!$isAdminRecipient)
        <p style="margin:0 0 10px 0; padding:10px 12px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:6px;">
            <strong>Important:</strong>
            If payment is not completed within the payment time,
            this booking will be cancelled automatically.
        </p>
    @endif

    <p><strong>What happens next?</strong></p>
    @if($isVendorRecipient)
        <ul style="padding-left:18px;">
            <li>Ensure customer payment is received within the payment timeline</li>
            <li>Keep payment reference updated in POS booking details</li>
            <li>Coordinate campaign execution once payment is confirmed</li>
            <li>Track status updates from your vendor POS dashboard</li>
        </ul>
    @elseif($isAdminRecipient)
        <ul style="padding-left:18px;">
            <li>Monitor booking and payment timeline compliance</li>
            <li>Verify operational progress if escalation is required</li>
            <li>Use admin tools for tracking and reporting</li>
        </ul>
    @else
        <ul style="padding-left:18px;">
            <li>We verify campaign details and availability</li>
            <li>Vendor confirms campaign feasibility</li>
            <li>You receive updates and can track your booking</li>
            <li>Proceed with payment and campaign execution</li>
        </ul>
    @endif
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