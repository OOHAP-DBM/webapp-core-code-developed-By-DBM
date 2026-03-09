<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Reminder - OOHAPP</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
    <tr>
        <td align="center">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                @include('emails.partials.header')

                <tr>
                    <td align="center" style="padding:20px 40px 0 40px;">
                        <h2 style="margin:0; color:#dc2626; font-weight:600;">Payment Reminder ⏰</h2>
                        <p style="margin-top:8px; color:#666; font-size:14px;">
                            Hi {{ $greetingName ?? ($customer->name ?? ($booking->customer_name ?? 'Customer')) }}, this is a gentle reminder for your pending booking payment.
                        </p>
                    </td>
                </tr>

                <tr>
                    <td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
                        Please review the details below and clear the outstanding amount at the earliest.
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
                                <td style="padding:10px; font-size:13px;">{{ $booking->invoice_number ?? $booking->id }}</td>
                            </tr>
                            <tr>
                                <td style="padding:10px; font-size:13px;"><strong>Booking Status</strong></td>
                                <td style="padding:10px; font-size:13px;">{{ ucfirst($booking->status ?? 'N/A') }}</td>
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
                                <td style="padding:10px; font-size:13px;"><strong>Outstanding Balance</strong></td>
                                <td style="padding:10px; font-size:13px;"><strong>₹{{ $remainingAmount }}</strong></td>
                            </tr>
                            <tr>
                                <td style="padding:10px; font-size:13px;"><strong>Reminder Count</strong></td>
                                <td style="padding:10px; font-size:13px;">{{ $reminderCount }}/3</td>
                            </tr>
                        </table>
                    </td>
                </tr>

                <tr>
                    <td align="center" style="padding:24px 40px 10px 40px;">
                        @if(!empty($actionUrl))
                            <a href="{{ $actionUrl }}" style="display:inline-block; background:#16a34a; color:#ffffff; padding:10px 22px; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">
                                View Booking Details
                            </a>
                        @endif
                    </td>
                </tr>

                <tr>
                    <td style="padding:15px 40px 25px 40px; color:#444; font-size:13px; line-height:20px;">
                        Thank you for your business with OOHAPP.
                    </td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</body>
</html>
