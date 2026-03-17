<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Received - OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

@php
    $invoiceNumber = $booking->invoice_number ?? $booking->id;
    $bookingStatusLabel = ucfirst(str_replace('_', ' ', (string) ($booking->status ?? 'confirmed')));
    $paymentStatusLabel = $isFullyPaid ? 'Paid' : 'Partial';
    $statusBadgeBg = $isFullyPaid ? '#dcfce7' : '#fef3c7';
    $statusBadgeText = $isFullyPaid ? '#166534' : '#92400e';
@endphp

<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#16a34a; font-weight:600;">
        {{ $isFullyPaid ? 'POS Booking Confirmed ✅' : 'POS Payment Received ✅' }}
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $greetingName ?? ($booking->customer_name ?? 'Customer') }},
        {{ $isFullyPaid ? 'payment has been received and your booking is confirmed.' : 'partial payment has been received for your booking.' }}
    </p>
</td>
</tr>

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    Please find the updated booking and payment details below.
</td>
</tr>

<tr>
<td style="padding:10px 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:13px;">Information</th>
            <th align="left" style="padding:10px; font-size:13px;">Details</th>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Booking ID</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $booking->id }}</td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Invoice No.</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $invoiceNumber }}</td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Booking Status</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $bookingStatusLabel }}</td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Payment Status</strong></td>
            <td style="padding:10px; font-size:13px;">
                <span style="background:{{ $statusBadgeBg }}; color:{{ $statusBadgeText }}; padding:4px 8px; font-weight:700; border-radius:4px;">
                    {{ $paymentStatusLabel }}
                </span>
            </td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Total Amount</strong></td>
            <td style="padding:10px; font-size:13px;">₹{{ $totalAmount }}</td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Paid Amount</strong></td>
            <td style="padding:10px; font-size:13px;">₹{{ $paidAmount }}</td>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Remaining Amount</strong></td>
            <td style="padding:10px; font-size:13px;">₹{{ $remainingAmount }}</td>
        </tr>
    </table>
</td>
</tr>

@if(!empty($paidMilestones) && $paidMilestones->isNotEmpty())
<tr>
<td style="padding:16px 40px 0 40px;">
    <p style="font-size:14px; color:#444; margin:0 0 8px 0;"><strong>Milestone Payment Details</strong></p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:12px;">#</th>
            <th align="left" style="padding:10px; font-size:12px;">Title</th>
            <th align="left" style="padding:10px; font-size:12px;">Due Date</th>
            <th align="left" style="padding:10px; font-size:12px;">Amount</th>
            <th align="left" style="padding:10px; font-size:12px;">Status</th>
        </tr>
        @foreach($paidMilestones as $ms)
        <tr>
            <td style="padding:10px; font-size:12px;">{{ $ms->sequence_no }}</td>
            <td style="padding:10px; font-size:12px;">{{ $ms->title ?? ('Milestone ' . $loop->iteration) }}</td>
            <td style="padding:10px; font-size:12px;">
                {{ $ms->due_date ? \Carbon\Carbon::parse($ms->due_date)->format('d M Y') : 'N/A' }}
            </td>
            <td style="padding:10px; font-size:12px;">
                ₹{{ number_format((float) ($ms->calculated_amount ?? $ms->amount ?? 0), 2) }}
            </td>
            <td style="padding:10px; font-size:12px;">Paid</td>
        </tr>
        @endforeach
    </table>
</td>
</tr>
@endif

@if(!empty($nextMilestone))
<tr>
<td style="padding:16px 40px 0 40px;">
    <p style="font-size:14px; color:#444; margin:0 0 8px 0;"><strong>Next Milestone</strong></p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:12px;">Title</th>
            <th align="left" style="padding:10px; font-size:12px;">Due Date</th>
            <th align="left" style="padding:10px; font-size:12px;">Amount</th>
            <th align="left" style="padding:10px; font-size:12px;">Status</th>
        </tr>
        <tr>
            <td style="padding:10px; font-size:12px;">{{ $nextMilestone->title ?? ('Milestone ' . ($nextMilestone->sequence_no ?? '')) }}</td>
            <td style="padding:10px; font-size:12px;">{{ $nextMilestone->due_date ? \Carbon\Carbon::parse($nextMilestone->due_date)->format('d M Y') : 'N/A' }}</td>
            <td style="padding:10px; font-size:12px;">₹{{ number_format((float) ($nextMilestone->calculated_amount ?? $nextMilestone->amount ?? 0), 2) }}</td>
            <td style="padding:10px; font-size:12px;">{{ ucfirst((string) ($nextMilestone->status ?? 'pending')) }}</td>
        </tr>
    </table>
</td>
</tr>
@endif

<tr>
<td align="center" style="padding:24px 40px 10px 40px;">
    @if(!empty($actionUrl))
        <a href="{{ $actionUrl }}" style="display:inline-block; background:#16a34a; color:#ffffff; padding:10px 22px; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">
            View Booking
        </a>
    @endif
</td>
</tr>

<tr>
<td style="padding:10px 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p style="margin:0 0 8px 0;">If you have any questions, contact us at <strong>support@oohapp.com</strong></p>
    <p style="margin:0;">Thank you for choosing OOHAPP.<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

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
