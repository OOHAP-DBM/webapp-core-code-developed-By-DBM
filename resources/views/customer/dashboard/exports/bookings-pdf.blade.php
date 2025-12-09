<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Bookings Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .customer-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; }
        .summary { margin-top: 20px; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>My Bookings Report</h1>
        <p>Generated on {{ now()->format('F d, Y H:i A') }}</p>
    </div>

    <div class="customer-info">
        <strong>Customer:</strong> {{ $customer->name }}<br>
        <strong>Email:</strong> {{ $customer->email }}<br>
        <strong>Phone:</strong> {{ $customer->phone ?? 'N/A' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Booking #</th>
                <th>Hoarding</th>
                <th>Start Date</th>
                <th>End Date</th>
                <th>Status</th>
                <th>Payment</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($bookings as $booking)
            <tr>
                <td>{{ $booking->booking_number }}</td>
                <td>{{ $booking->hoarding->title ?? 'N/A' }}</td>
                <td>{{ $booking->start_date->format('M d, Y') }}</td>
                <td>{{ $booking->end_date->format('M d, Y') }}</td>
                <td>{{ ucfirst($booking->status) }}</td>
                <td>{{ ucfirst($booking->payment_status) }}</td>
                <td class="text-right">₹{{ number_format($booking->total_amount, 2) }}</td>
            </tr>
            @php $total += $booking->total_amount; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="6" class="text-right">Total:</th>
                <th class="text-right">₹{{ number_format($total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="summary">
        <strong>Summary:</strong><br>
        Total Bookings: {{ $bookings->count() }}<br>
        Total Amount: ₹{{ number_format($total, 2) }}
    </div>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>
