<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Settlement Receipt - {{ $payoutRequest->request_reference }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 12px;
            line-height: 1.6;
            color: #333;
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #667eea;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #667eea;
            font-size: 24px;
            margin-bottom: 5px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .company-info {
            float: right;
            text-align: right;
            max-width: 250px;
        }
        .company-info h2 {
            font-size: 18px;
            color: #333;
            margin-bottom: 5px;
        }
        .receipt-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            overflow: hidden;
        }
        .info-row {
            margin-bottom: 8px;
        }
        .info-label {
            font-weight: bold;
            display: inline-block;
            width: 180px;
        }
        .vendor-details {
            margin-bottom: 30px;
        }
        .vendor-details h3 {
            color: #667eea;
            margin-bottom: 10px;
            font-size: 16px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        table th {
            background: #667eea;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        table tr:nth-child(even) {
            background: #f8f9fa;
        }
        .calculation-table {
            width: 100%;
            max-width: 500px;
            margin-left: auto;
        }
        .calculation-table td {
            padding: 8px;
        }
        .calculation-table .label {
            text-align: right;
            font-weight: bold;
            width: 60%;
        }
        .calculation-table .amount {
            text-align: right;
            width: 40%;
        }
        .calculation-table .total-row {
            background: #667eea;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .calculation-table .subtotal-row {
            border-top: 2px solid #333;
            font-weight: bold;
        }
        .bank-details {
            background: #fff3cd;
            border: 1px solid #ffc107;
            border-radius: 5px;
            padding: 15px;
            margin: 20px 0;
        }
        .bank-details h3 {
            color: #856404;
            margin-bottom: 10px;
            font-size: 14px;
        }
        .signature-section {
            margin-top: 50px;
            border-top: 2px solid #ddd;
            padding-top: 30px;
        }
        .signature-box {
            float: right;
            text-align: center;
            width: 250px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
        }
        .footer {
            margin-top: 50px;
            text-align: center;
            color: #666;
            font-size: 10px;
            border-top: 1px solid #ddd;
            padding-top: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 3px;
            font-size: 11px;
            font-weight: bold;
            background: #28a745;
            color: white;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header clearfix">
        <div class="company-info">
            <h2>{{ $company['name'] }}</h2>
            <p>{{ $company['address'] }}</p>
            <p>Phone: {{ $company['phone'] }}</p>
            <p>Email: {{ $company['email'] }}</p>
            <p>GST: {{ $company['gst'] }}</p>
        </div>
        <div>
            <h1>PAYOUT SETTLEMENT RECEIPT</h1>
            <p class="subtitle">Official Settlement Document</p>
        </div>
    </div>

    <!-- Receipt Information -->
    <div class="receipt-info">
        <div class="info-row">
            <span class="info-label">Receipt Reference:</span>
            <span>{{ $payoutRequest->request_reference }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Receipt Date:</span>
            <span>{{ $generated_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Settlement Period:</span>
            <span>{{ $payoutRequest->period_start->format('d M Y') }} to {{ $payoutRequest->period_end->format('d M Y') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Payment Date:</span>
            <span>{{ $payoutRequest->paid_at->format('d M Y, h:i A') }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Status:</span>
            <span class="status-badge">PAID</span>
        </div>
    </div>

    <!-- Vendor Details -->
    <div class="vendor-details">
        <h3>Vendor Details</h3>
        <div class="info-row">
            <span class="info-label">Vendor Name:</span>
            <span>{{ $vendor->name }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Vendor ID:</span>
            <span>{{ $vendor->id }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Email:</span>
            <span>{{ $vendor->email }}</span>
        </div>
        @if($vendor->phone)
        <div class="info-row">
            <span class="info-label">Phone:</span>
            <span>{{ $vendor->phone }}</span>
        </div>
        @endif
    </div>

    <!-- Bookings Included -->
    <h3 style="color: #667eea; margin-bottom: 10px;">Bookings Included in Settlement</h3>
    <table>
        <thead>
            <tr>
                <th>Booking ID</th>
                <th>Date</th>
                <th>Hoarding</th>
                <th style="text-align: right;">Revenue</th>
                <th style="text-align: right;">Commission</th>
                <th style="text-align: right;">Net Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($bookingPayments as $payment)
            <tr>
                <td>#{{ $payment->booking_id }}</td>
                <td>{{ $payment->created_at->format('d M Y') }}</td>
                <td>{{ $payment->booking->hoarding->title ?? 'N/A' }}</td>
                <td style="text-align: right;">₹{{ number_format($payment->gross_amount, 2) }}</td>
                <td style="text-align: right;">₹{{ number_format($payment->admin_commission_amount, 2) }}</td>
                <td style="text-align: right;">₹{{ number_format($payment->vendor_payout_amount, 2) }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Financial Calculation -->
    <h3 style="color: #667eea; margin-bottom: 10px;">Payment Calculation</h3>
    <table class="calculation-table">
        <tr>
            <td class="label">Total Booking Revenue:</td>
            <td class="amount">₹{{ number_format($payoutRequest->booking_revenue, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Platform Commission ({{ number_format($payoutRequest->commission_percentage, 2) }}%):</td>
            <td class="amount">- ₹{{ number_format($payoutRequest->commission_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="label">Payment Gateway Fees:</td>
            <td class="amount">- ₹{{ number_format($payoutRequest->pg_fees, 2) }}</td>
        </tr>
        @if($payoutRequest->adjustment_amount != 0)
        <tr>
            <td class="label">Adjustment @if($payoutRequest->adjustment_reason)({{ $payoutRequest->adjustment_reason }})@endif:</td>
            <td class="amount">{{ $payoutRequest->adjustment_amount > 0 ? '+' : '' }} ₹{{ number_format($payoutRequest->adjustment_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="subtotal-row">
            <td class="label">Net Amount (Before GST):</td>
            <td class="amount">₹{{ number_format($payoutRequest->net_amount_before_gst, 2) }}</td>
        </tr>
        @if($payoutRequest->gst_amount > 0)
        <tr>
            <td class="label">GST ({{ number_format($payoutRequest->gst_percentage, 2) }}%):</td>
            <td class="amount">- ₹{{ number_format($payoutRequest->gst_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="total-row">
            <td class="label">FINAL PAYOUT AMOUNT:</td>
            <td class="amount">₹{{ number_format($payoutRequest->final_payout_amount, 2) }}</td>
        </tr>
    </table>

    <!-- Bank Details -->
    <div class="bank-details">
        <h3>Payment Details</h3>
        <div class="info-row">
            <span class="info-label">Payment Mode:</span>
            <span>{{ strtoupper(str_replace('_', ' ', $payoutRequest->payout_mode)) }}</span>
        </div>
        <div class="info-row">
            <span class="info-label">Reference Number:</span>
            <span><strong>{{ $payoutRequest->payout_reference }}</strong></span>
        </div>
        @if($payoutRequest->bank_name)
        <div class="info-row">
            <span class="info-label">Bank Name:</span>
            <span>{{ $payoutRequest->bank_name }}</span>
        </div>
        @endif
        @if($payoutRequest->account_number)
        <div class="info-row">
            <span class="info-label">Account Number:</span>
            <span>****{{ substr($payoutRequest->account_number, -4) }}</span>
        </div>
        @endif
        @if($payoutRequest->payout_notes)
        <div class="info-row">
            <span class="info-label">Notes:</span>
            <span>{{ $payoutRequest->payout_notes }}</span>
        </div>
        @endif
    </div>

    <!-- Summary -->
    <div style="background: #e7f3ff; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <p><strong>Summary:</strong> This receipt confirms the settlement of {{ $payoutRequest->bookings_count }} booking(s) 
        for the period {{ $payoutRequest->period_start->format('d M Y') }} to {{ $payoutRequest->period_end->format('d M Y') }}. 
        Total amount of <strong>₹{{ number_format($payoutRequest->final_payout_amount, 2) }}</strong> has been transferred 
        to the vendor's registered account.</p>
    </div>

    <!-- Signature -->
    <div class="signature-section clearfix">
        <div class="signature-box">
            <div class="signature-line">
                Authorized Signatory<br>
                {{ $company['name'] }}
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <p>This is a computer-generated document and does not require a physical signature.</p>
        <p>Generated on {{ $generated_at->format('d M Y, h:i A') }} | {{ $company['name'] }} - All Rights Reserved</p>
    </div>
</body>
</html>
