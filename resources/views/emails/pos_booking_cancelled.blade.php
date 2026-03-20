<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>POS Booking Cancelled - OOHAPP</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

@php
    $titles = collect($hoardingTitles ?? [])
        ->map(function ($title) {
            return trim((string) $title);
        })
        ->filter()
        ->unique()
        ->values();

    if ($titles->isEmpty() && isset($booking) && method_exists($booking, 'bookingHoardings')) {
        if (!$booking->relationLoaded('bookingHoardings')) {
            $booking->load('bookingHoardings.hoarding');
        }

        $titles = $booking->bookingHoardings
            ->map(function ($bookingHoarding) {
                return trim((string) ($bookingHoarding->hoarding->title ?? ''));
            })
            ->filter()
            ->unique()
            ->values();
    }

    if ($titles->isEmpty() && !empty($booking->hoarding?->title)) {
        $titles = collect([trim((string) $booking->hoarding->title)]);
    }

    $invoiceNumber = $booking->invoice_number ?? $booking->id;
    $effectiveReason = trim((string) ($reason ?? $booking->cancellation_reason ?? ''));
    $cancelledAt = $booking->cancelled_at
        ? \Carbon\Carbon::parse($booking->cancelled_at)->format('d M Y, h:i A')
        : 'N/A';
@endphp

<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#dc2626; font-weight:600;">Booking Cancelled</h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $greetingName ?? ($customer->name ?? ($booking->customer_name ?? 'Customer')) }}, your POS booking has been cancelled.
    </p>
</td>
</tr>

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    The booking has been moved to cancelled status and the reserved hoarding inventory has been released.
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
            <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
            <td style="padding:10px; font-size:13px;">
                <span style="background:#fee2e2; color:#991b1b; padding:4px 8px; font-weight:700; border-radius:4px;">
                    Cancelled
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Cancelled At</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $cancelledAt }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Total Amount</strong></td>
            <td style="padding:10px; font-size:13px;">Rs. {{ number_format((float) ($booking->total_amount ?? 0), 2) }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Hoarding</strong></td>
            <td style="padding:10px; font-size:13px;">
                @if($titles->isNotEmpty())
                    {{ $titles->implode(', ') }}
                @else
                    N/A
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Cancellation Reason</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveReason !== '' ? $effectiveReason : 'Not specified' }}</td>
        </tr>
    </table>
</td>
</tr>

<tr>
<td style="padding:0 40px 24px 40px; font-size:14px; color:#444; line-height:22px;">
    <p style="margin:14px 0 0 0;">If this cancellation was made in error, please contact your vendor to create a new booking.</p>
    <p style="margin:12px 0 0 0;">For support, write to <strong>support@oohapp.com</strong>.</p>
</td>
</tr>

@include('emails.partials.footer')

</table>
</td>
</tr>
</table>

</body>
</html>
