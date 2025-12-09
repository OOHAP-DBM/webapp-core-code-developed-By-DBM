<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Invoices Report</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 30px; }
        .header h1 { margin: 0; color: #333; }
        .customer-info { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; font-weight: bold; }
        .text-right { text-align: right; }
        .footer { margin-top: 30px; text-align: center; font-size: 10px; color: #666; }
    </style>
</head>
<body>
    <div class="header">
        <h1>My Invoices Report</h1>
        <p>Generated on {{ now()->format('F d, Y H:i A') }}</p>
    </div>

    <div class="customer-info">
        <strong>Customer:</strong> {{ $customer->name }}<br>
        <strong>Email:</strong> {{ $customer->email }}
    </div>

    <table>
        <thead>
            <tr>
                <th>Invoice #</th>
                <th>Booking #</th>
                <th>Invoice Date</th>
                <th>Due Date</th>
                <th>Payment Status</th>
                <th class="text-right">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $total = 0; @endphp
            @foreach($invoices as $invoice)
            <tr>
                <td>{{ $invoice->invoice_number }}</td>
                <td>{{ $invoice->booking_number }}</td>
                <td>{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('M d, Y') : 'N/A' }}</td>
                <td>{{ $invoice->due_date ? \Carbon\Carbon::parse($invoice->due_date)->format('M d, Y') : 'N/A' }}</td>
                <td>{{ ucfirst($invoice->payment_status) }}</td>
                <td class="text-right">₹{{ number_format($invoice->total_amount, 2) }}</td>
            </tr>
            @php $total += $invoice->total_amount; @endphp
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <th colspan="5" class="text-right">Total:</th>
                <th class="text-right">₹{{ number_format($total, 2) }}</th>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        <p>This is a computer-generated report. No signature required.</p>
    </div>
</body>
</html>
