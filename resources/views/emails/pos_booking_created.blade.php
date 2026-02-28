<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; background: #f4f4f4; padding: 20px; }
        .container { background: white; padding: 30px; max-width: 600px; margin: auto; border-radius: 8px; }
        .header { background: #2D5A43; color: white; padding: 15px; text-align: center; border-radius: 6px 6px 0 0; }
        .row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #eee; }
        .total { font-size: 18px; font-weight: bold; color: #2D5A43; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h2>Booking Confirmation</h2>
        </div>
        <div style="padding: 20px;">
            <p>Hello <strong>{{ $customer->name }}</strong>,</p>
            <p>Your POS booking has been created successfully!</p>

            <div class="row"><span>Booking ID</span><span><strong>{{ $booking->invoice_number ?? '#'.$booking->id }}</strong></span></div>
            <div class="row"><span>Dates</span><span>{{ $booking->start_date }} → {{ $booking->end_date }}</span></div>
            <div class="row"><span>Payment Mode</span><span>{{ ucfirst($booking->payment_mode) }}</span></div>
            <div class="row"><span>Base Amount</span><span>₹{{ number_format($booking->base_amount, 2) }}</span></div>
            <div class="row"><span>GST (18%)</span><span>₹{{ number_format($booking->tax_amount, 2) }}</span></div>
            <div class="row total"><span>Total</span><span>₹{{ number_format($booking->total_amount, 2) }}</span></div>

            <p style="margin-top: 20px; color: #666;">Thank you for using OOHAPP!</p>
        </div>
    </div>
</body>
</html>