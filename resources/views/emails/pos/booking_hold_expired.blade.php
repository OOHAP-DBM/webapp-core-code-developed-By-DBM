@include('emails.partials.header')

<table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width:600px;margin:auto;background:#fff;border-radius:8px;overflow:hidden;">
    <tr>
        <td style="padding:32px 32px 16px 32px;">
            <h2 style="margin:0 0 16px 0;font-size:22px;font-weight:700;color:#222;">POS Booking Hold Expired</h2>
            <p style="margin:0 0 16px 0;font-size:16px;color:#333;">{!! $greeting ?? '' !!}</p>
            <p style="margin:0 0 16px 0;font-size:15px;color:#444;">{!! $body ?? '' !!}</p>
            <table style="margin:16px 0 24px 0;font-size:15px;color:#444;">
                <tr><td><strong>Booking ID:</strong></td><td>#{{ $booking_id }}</td></tr>
                <tr><td><strong>Invoice Number:</strong></td><td>{{ $invoice_number }}</td></tr>
                <tr><td><strong>Amount:</strong></td><td>{{ $amount }}</td></tr>
                <tr><td><strong>Payment Mode:</strong></td><td>{{ $payment_mode }}</td></tr>
                <tr><td><strong>Hold Expired At:</strong></td><td>{{ $hold_expired_at }}</td></tr>
            </table>
            <p style="margin:0 0 24px 0;font-size:15px;color:#444;">This booking has been automatically cancelled and the hoarding hold has been released.</p>
            <div style="margin-bottom:24px;">
                <a href="{{ $action_url }}" style="display:inline-block;padding:12px 28px;background:#2563eb;color:#fff;border-radius:6px;text-decoration:none;font-weight:600;font-size:16px;">{{ $action_text ?? 'View Booking' }}</a>
            </div>
            <p style="margin:0 0 0 0;font-size:14px;color:#888;">You can create a new booking if you still want to proceed.</p>
        </td>
    </tr>
</table>

@include('emails.partials.footer')
