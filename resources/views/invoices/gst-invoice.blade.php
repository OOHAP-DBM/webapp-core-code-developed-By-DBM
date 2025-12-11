<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 10px;
            color: #333;
            line-height: 1.4;
        }
        .invoice-container {
            max-width: 800px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            border: 2px solid #000;
            padding: 10px;
            margin-bottom: 10px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            margin-bottom: 5px;
        }
        .header .subtitle {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 3px;
        }
        .company-info {
            text-align: center;
            font-size: 9px;
            margin-bottom: 10px;
        }
        .invoice-details {
            display: table;
            width: 100%;
            border: 1px solid #000;
            margin-bottom: 10px;
        }
        .invoice-row {
            display: table-row;
        }
        .invoice-cell {
            display: table-cell;
            border: 1px solid #000;
            padding: 5px;
            vertical-align: top;
        }
        .invoice-cell.half {
            width: 50%;
        }
        .label {
            font-weight: bold;
            font-size: 9px;
        }
        .value {
            font-size: 10px;
        }
        .party-details {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        .party-details td {
            border: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .party-details .label-cell {
            font-weight: bold;
            font-size: 9px;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
            font-size: 9px;
        }
        .items-table th {
            background-color: #f0f0f0;
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }
        .items-table td {
            border: 1px solid #000;
            padding: 5px;
        }
        .items-table .text-right {
            text-align: right;
        }
        .items-table .text-center {
            text-align: center;
        }
        .tax-summary {
            width: 50%;
            float: right;
            border-collapse: collapse;
            font-size: 9px;
        }
        .tax-summary td {
            border: 1px solid #000;
            padding: 5px;
        }
        .tax-summary .label-cell {
            font-weight: bold;
            width: 60%;
        }
        .tax-summary .amount-cell {
            text-align: right;
            width: 40%;
        }
        .tax-summary .total-row {
            font-weight: bold;
            background-color: #f0f0f0;
        }
        .amount-words {
            clear: both;
            border: 1px solid #000;
            padding: 8px;
            margin-top: 10px;
            margin-bottom: 10px;
        }
        .amount-words .label {
            font-weight: bold;
        }
        .terms-section {
            border: 1px solid #000;
            padding: 8px;
            margin-bottom: 10px;
            font-size: 8px;
        }
        .terms-section h3 {
            font-size: 10px;
            margin-bottom: 5px;
        }
        .footer {
            display: table;
            width: 100%;
            border: 1px solid #000;
        }
        .footer-cell {
            display: table-cell;
            padding: 15px;
            vertical-align: top;
        }
        .footer-cell.qr {
            width: 25%;
            text-align: center;
            border-right: 1px solid #000;
        }
        .footer-cell.signature {
            width: 75%;
            text-align: right;
        }
        .signature-line {
            margin-top: 40px;
            border-top: 1px solid #000;
            display: inline-block;
            width: 200px;
            text-align: center;
            padding-top: 5px;
            font-size: 9px;
            font-weight: bold;
        }
        .qr-code {
            max-width: 120px;
            margin: 0 auto;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }
        .badge-paid {
            background-color: #28a745;
            color: white;
        }
        .badge-unpaid {
            background-color: #dc3545;
            color: white;
        }
        .badge-cancelled {
            background-color: #6c757d;
            color: white;
        }
        .gst-note {
            font-size: 8px;
            font-style: italic;
            color: #666;
            text-align: center;
            margin-top: 10px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <h1>TAX INVOICE</h1>
            <div class="subtitle">GST Compliant Invoice</div>
        </div>

        <!-- Company Information -->
        <div class="company-info">
            <strong>{{ $invoice->seller_name }}</strong><br>
            {{ $invoice->seller_address }}, {{ $invoice->seller_city }}, {{ $invoice->seller_state }} - {{ $invoice->seller_pincode }}<br>
            <strong>GSTIN:</strong> {{ $invoice->seller_gstin }} | 
            @if($invoice->seller_pan)
                <strong>PAN:</strong> {{ $invoice->seller_pan }} | 
            @endif
            <strong>State Code:</strong> {{ $invoice->seller_state_code }}
        </div>

        <!-- Invoice Details -->
        <div class="invoice-details">
            <div class="invoice-row">
                <div class="invoice-cell half">
                    <div class="label">Invoice Number</div>
                    <div class="value">{{ $invoice->invoice_number }}</div>
                </div>
                <div class="invoice-cell half">
                    <div class="label">Invoice Date</div>
                    <div class="value">{{ $invoice->invoice_date->format('d-m-Y') }}</div>
                </div>
            </div>
            <div class="invoice-row">
                <div class="invoice-cell half">
                    <div class="label">Place of Supply</div>
                    <div class="value">{{ $invoice->place_of_supply }}</div>
                </div>
                <div class="invoice-cell half">
                    <div class="label">Reverse Charge</div>
                    <div class="value">{{ $invoice->is_reverse_charge ? 'Yes' : 'No' }}</div>
                </div>
            </div>
            @if($invoice->due_date)
            <div class="invoice-row">
                <div class="invoice-cell half">
                    <div class="label">Payment Terms</div>
                    <div class="value">{{ $invoice->payment_terms ?? 'Due on Receipt' }}</div>
                </div>
                <div class="invoice-cell half">
                    <div class="label">Due Date</div>
                    <div class="value">{{ $invoice->due_date->format('d-m-Y') }}</div>
                </div>
            </div>
            @endif
        </div>

        <!-- Billing Details -->
        <table class="party-details">
            <tr>
                <td colspan="2" style="background-color: #f0f0f0; font-weight: bold; text-align: center;">
                    BILLING DETAILS
                </td>
            </tr>
            <tr>
                <td class="label-cell">Customer Name</td>
                <td>{{ $invoice->buyer_name }}</td>
            </tr>
            <tr>
                <td class="label-cell">Billing Address</td>
                <td>
                    {{ $invoice->buyer_address }}<br>
                    {{ $invoice->buyer_city }}, {{ $invoice->buyer_state }} - {{ $invoice->buyer_pincode }}
                </td>
            </tr>
            <tr>
                <td class="label-cell">GSTIN</td>
                <td>{{ $invoice->buyer_gstin ?? 'N/A (Unregistered)' }}</td>
            </tr>
            @if($invoice->buyer_pan)
            <tr>
                <td class="label-cell">PAN</td>
                <td>{{ $invoice->buyer_pan }}</td>
            </tr>
            @endif
            <tr>
                <td class="label-cell">State Code</td>
                <td>{{ $invoice->buyer_state_code }}</td>
            </tr>
            <tr>
                <td class="label-cell">Contact</td>
                <td>
                    @if($invoice->buyer_email)
                        <strong>Email:</strong> {{ $invoice->buyer_email }}<br>
                    @endif
                    @if($invoice->buyer_phone)
                        <strong>Phone:</strong> {{ $invoice->buyer_phone }}
                    @endif
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 35%;">Description</th>
                    <th style="width: 10%;">HSN/SAC</th>
                    <th style="width: 8%;">Qty</th>
                    <th style="width: 8%;">Unit</th>
                    <th style="width: 12%;">Rate (₹)</th>
                    <th style="width: 10%;">Discount</th>
                    <th style="width: 12%;">Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td class="text-center">{{ $item->line_number }}</td>
                    <td>
                        {{ $item->description }}
                        @if($item->service_start_date && $item->service_end_date)
                            <br><small>({{ $item->service_start_date->format('d/m/Y') }} to {{ $item->service_end_date->format('d/m/Y') }})</small>
                        @endif
                    </td>
                    <td class="text-center">{{ $item->hsn_sac_code ?? 'N/A' }}</td>
                    <td class="text-right">{{ number_format($item->quantity, 2) }}</td>
                    <td class="text-center">{{ $item->unit }}</td>
                    <td class="text-right">{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">
                        @if($item->discount_amount > 0)
                            {{ number_format($item->discount_amount, 2) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right"><strong>{{ number_format($item->taxable_amount, 2) }}</strong></td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Tax Summary -->
        <table class="tax-summary">
            <tr>
                <td class="label-cell">Subtotal</td>
                <td class="amount-cell">₹{{ number_format($invoice->subtotal, 2) }}</td>
            </tr>
            @if($invoice->discount_amount > 0)
            <tr>
                <td class="label-cell">Discount</td>
                <td class="amount-cell">(₹{{ number_format($invoice->discount_amount, 2) }})</td>
            </tr>
            @endif
            <tr>
                <td class="label-cell">Taxable Amount</td>
                <td class="amount-cell">₹{{ number_format($invoice->taxable_amount, 2) }}</td>
            </tr>
            @if($invoice->is_intra_state)
                <tr>
                    <td class="label-cell">CGST @ {{ number_format($invoice->cgst_rate, 2) }}%</td>
                    <td class="amount-cell">₹{{ number_format($invoice->cgst_amount, 2) }}</td>
                </tr>
                <tr>
                    <td class="label-cell">SGST @ {{ number_format($invoice->sgst_rate, 2) }}%</td>
                    <td class="amount-cell">₹{{ number_format($invoice->sgst_amount, 2) }}</td>
                </tr>
            @else
                <tr>
                    <td class="label-cell">IGST @ {{ number_format($invoice->igst_rate, 2) }}%</td>
                    <td class="amount-cell">₹{{ number_format($invoice->igst_amount, 2) }}</td>
                </tr>
            @endif
            <tr>
                <td class="label-cell">Total Tax</td>
                <td class="amount-cell">₹{{ number_format($invoice->total_tax, 2) }}</td>
            </tr>
            @if($invoice->round_off != 0)
            <tr>
                <td class="label-cell">Round Off</td>
                <td class="amount-cell">₹{{ number_format($invoice->round_off, 2) }}</td>
            </tr>
            @endif
            <tr class="total-row">
                <td class="label-cell">GRAND TOTAL</td>
                <td class="amount-cell">₹{{ number_format($invoice->grand_total, 2) }}</td>
            </tr>
        </table>

        <!-- Amount in Words -->
        <div class="amount-words">
            <span class="label">Amount in Words:</span> 
            {{ ucwords(\NumberFormatter::create('en_IN', \NumberFormatter::SPELLOUT)->format($invoice->grand_total)) }} Rupees Only
        </div>

        <!-- Terms and Conditions -->
        @if($invoice->terms_conditions)
        <div class="terms-section">
            <h3>Terms & Conditions:</h3>
            {!! nl2br(e($invoice->terms_conditions)) !!}
        </div>
        @endif

        <!-- Notes -->
        @if($invoice->notes)
        <div class="terms-section">
            <h3>Notes:</h3>
            {{ $invoice->notes }}
        </div>
        @endif

        <!-- Footer with QR Code and Signature -->
        <div class="footer">
            <div class="footer-cell qr">
                @if($invoice->hasQRCode())
                    <img src="{{ public_path('storage/' . $invoice->qr_code_path) }}" alt="QR Code" class="qr-code">
                    <div style="font-size: 8px; margin-top: 5px;">Scan for Invoice Details</div>
                @endif
            </div>
            <div class="footer-cell signature">
                <div style="font-size: 9px; margin-bottom: 10px;">
                    <strong>For {{ $invoice->seller_name }}</strong>
                </div>
                <div class="signature-line">
                    Authorized Signatory
                </div>
            </div>
        </div>

        <!-- GST Note -->
        <div class="gst-note">
            This is a computer-generated invoice and does not require a physical signature.<br>
            @if($invoice->is_intra_state)
                This is an intra-state supply (within {{ $invoice->seller_state }}) - CGST & SGST applicable.
            @else
                This is an inter-state supply - IGST applicable.
            @endif
            @if($invoice->is_reverse_charge)
                <br><strong>Reverse Charge Applicable:</strong> Tax liability lies with the recipient.
            @endif
        </div>
    </div>
</body>
</html>
