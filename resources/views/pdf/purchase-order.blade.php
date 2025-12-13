<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Order - {{ $po->po_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'DejaVu Sans', Arial, sans-serif;
            font-size: 11px;
            line-height: 1.5;
            color: #333;
        }
        
        .container {
            padding: 20px;
        }
        
        .header {
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        
        .logo-section {
            text-align: center;
            margin-bottom: 10px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        
        .po-title {
            font-size: 18px;
            font-weight: bold;
            text-align: center;
            color: #1e40af;
            margin: 15px 0;
        }
        
        .po-number {
            text-align: center;
            font-size: 14px;
            color: #666;
            margin-bottom: 5px;
        }
        
        .po-date {
            text-align: center;
            font-size: 11px;
            color: #666;
        }
        
        .parties-section {
            display: table;
            width: 100%;
            margin: 20px 0;
        }
        
        .party-box {
            display: table-cell;
            width: 50%;
            padding: 15px;
            vertical-align: top;
        }
        
        .party-title {
            font-weight: bold;
            color: #2563eb;
            font-size: 12px;
            margin-bottom: 8px;
            text-transform: uppercase;
        }
        
        .party-details {
            background: #f8fafc;
            padding: 10px;
            border-left: 3px solid #2563eb;
        }
        
        .party-details p {
            margin-bottom: 4px;
        }
        
        .info-grid {
            margin: 20px 0;
            background: #f8fafc;
            padding: 15px;
            border-radius: 5px;
        }
        
        .info-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .info-label {
            display: table-cell;
            width: 35%;
            font-weight: bold;
            color: #555;
        }
        
        .info-value {
            display: table-cell;
            width: 65%;
            color: #333;
        }
        
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
        }
        
        .items-table th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-size: 11px;
            font-weight: bold;
        }
        
        .items-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        
        .items-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        
        .text-right {
            text-align: right;
        }
        
        .text-center {
            text-align: center;
        }
        
        .summary-section {
            float: right;
            width: 40%;
            margin-top: 20px;
        }
        
        .summary-table {
            width: 100%;
        }
        
        .summary-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        
        .summary-label {
            display: table-cell;
            text-align: right;
            padding-right: 15px;
            color: #555;
        }
        
        .summary-value {
            display: table-cell;
            text-align: right;
            font-weight: bold;
        }
        
        .grand-total-row {
            background: #2563eb;
            color: white;
            padding: 10px;
            margin-top: 10px;
        }
        
        .grand-total-row .summary-label,
        .grand-total-row .summary-value {
            color: white;
            font-size: 13px;
        }
        
        .milestone-section {
            clear: both;
            margin: 30px 0;
            padding: 15px;
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
        }
        
        .milestone-title {
            font-weight: bold;
            color: #1e40af;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .milestone-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        
        .milestone-table th {
            background: #dbeafe;
            color: #1e40af;
            padding: 8px;
            text-align: left;
            font-size: 10px;
        }
        
        .milestone-table td {
            padding: 8px;
            border-bottom: 1px solid #bfdbfe;
            font-size: 10px;
        }
        
        .terms-section {
            margin: 30px 0;
            padding: 15px;
            background: #fef3c7;
            border-left: 4px solid #f59e0b;
        }
        
        .terms-title {
            font-weight: bold;
            color: #92400e;
            margin-bottom: 10px;
            font-size: 12px;
        }
        
        .terms-list {
            margin-left: 20px;
        }
        
        .terms-list li {
            margin-bottom: 6px;
            color: #78350f;
        }
        
        .footer {
            margin-top: 40px;
            padding-top: 20px;
            border-top: 2px solid #e5e7eb;
        }
        
        .signature-section {
            display: table;
            width: 100%;
            margin-top: 40px;
        }
        
        .signature-box {
            display: table-cell;
            width: 50%;
            padding: 15px;
        }
        
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 5px;
            text-align: center;
        }
        
        .notes-section {
            margin: 20px 0;
            padding: 10px;
            background: #f3f4f6;
            border-left: 3px solid #6b7280;
        }
        
        .badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 10px;
            font-weight: bold;
        }
        
        .badge-success {
            background: #dcfce7;
            color: #166534;
        }
        
        .badge-info {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo-section">
                <div class="company-name">{{ config('app.name', 'OOH Platform') }}</div>
                <div style="font-size: 10px; color: #666;">Out-of-Home Advertising Platform</div>
            </div>
        </div>

        <!-- PO Title and Number -->
        <div class="po-title">PURCHASE ORDER</div>
        <div class="po-number">PO Number: <strong>{{ $po->po_number }}</strong></div>
        <div class="po-date">Date: {{ $po->created_at->format('d M Y, h:i A') }}</div>

        <!-- Parties Information -->
        <div class="parties-section">
            <div class="party-box">
                <div class="party-title">Bill To (Customer)</div>
                <div class="party-details">
                    <p><strong>{{ $po->customer->name }}</strong></p>
                    <p>{{ $po->customer->email }}</p>
                    <p>{{ $po->customer->phone }}</p>
                    @if($po->customer->company_name)
                        <p>Company: {{ $po->customer->company_name }}</p>
                    @endif
                </div>
            </div>
            <div class="party-box">
                <div class="party-title">Ship To / Vendor</div>
                <div class="party-details">
                    <p><strong>{{ $po->vendor->name }}</strong></p>
                    <p>{{ $po->vendor->email }}</p>
                    <p>{{ $po->vendor->phone }}</p>
                    @if($po->vendor->company_name)
                        <p>Company: {{ $po->vendor->company_name }}</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Order Information -->
        <div class="info-grid">
            <div class="info-row">
                <div class="info-label">Quotation ID:</div>
                <div class="info-value">#{{ $po->quotation_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Enquiry ID:</div>
                <div class="info-value">#{{ $po->enquiry_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Offer ID:</div>
                <div class="info-value">#{{ $po->offer_id }}</div>
            </div>
            <div class="info-row">
                <div class="info-label">Payment Mode:</div>
                <div class="info-value">
                    <span class="badge badge-info">{{ ucfirst($po->payment_mode ?? 'full') }}</span>
                </div>
            </div>
            @if($po->has_milestones)
                <div class="info-row">
                    <div class="info-label">Milestone Count:</div>
                    <div class="info-value">{{ $po->milestone_count }} milestones</div>
                </div>
            @endif
            <div class="info-row">
                <div class="info-label">Status:</div>
                <div class="info-value">
                    <span class="badge badge-success">{{ $po->getStatusLabel() }}</span>
                </div>
            </div>
        </div>

        <!-- Line Items -->
        @if($po->items && count($po->items) > 0)
            <h3 style="margin: 20px 0 10px; color: #1e40af; font-size: 13px;">Order Details</h3>
            <table class="items-table">
                <thead>
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 50%;">Description</th>
                        <th style="width: 15%;" class="text-center">Quantity</th>
                        <th style="width: 15%;" class="text-right">Rate (₹)</th>
                        <th style="width: 15%;" class="text-right">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($po->items as $index => $item)
                        <tr>
                            <td>{{ $index + 1 }}</td>
                            <td>{{ $item['description'] ?? 'N/A' }}</td>
                            <td class="text-center">{{ $item['quantity'] ?? 0 }}</td>
                            <td class="text-right">{{ number_format($item['rate'] ?? 0, 2) }}</td>
                            <td class="text-right">{{ number_format(($item['quantity'] ?? 0) * ($item['rate'] ?? 0), 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <!-- Summary -->
        <div class="summary-section">
            <div class="summary-table">
                <div class="summary-row">
                    <div class="summary-label">Subtotal:</div>
                    <div class="summary-value">₹ {{ number_format($po->total_amount, 2) }}</div>
                </div>
                @if($po->tax > 0)
                    <div class="summary-row">
                        <div class="summary-label">Tax (GST):</div>
                        <div class="summary-value">₹ {{ number_format($po->tax, 2) }}</div>
                    </div>
                @endif
                @if($po->discount > 0)
                    <div class="summary-row">
                        <div class="summary-label">Discount:</div>
                        <div class="summary-value">- ₹ {{ number_format($po->discount, 2) }}</div>
                    </div>
                @endif
                <div class="summary-row grand-total-row">
                    <div class="summary-label">Grand Total:</div>
                    <div class="summary-value">₹ {{ number_format($po->grand_total, 2) }}</div>
                </div>
            </div>
        </div>

        <div style="clear: both;"></div>

        <!-- Milestone Details -->
        @if($po->has_milestones && $po->milestone_summary)
            <div class="milestone-section">
                <div class="milestone-title">📊 Milestone Payment Schedule</div>
                <p style="margin-bottom: 10px; font-size: 10px;">Payment will be released in {{ $po->milestone_count }} milestone(s) as described below:</p>
                <table class="milestone-table">
                    <thead>
                        <tr>
                            <th>Milestone</th>
                            <th>Description</th>
                            <th class="text-center">Percentage</th>
                            <th class="text-right">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($po->milestone_summary as $index => $milestone)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $milestone['name'] ?? 'Milestone ' . ($index + 1) }}</td>
                                <td class="text-center">{{ $milestone['percentage'] ?? 0 }}%</td>
                                <td class="text-right">{{ number_format($milestone['amount'] ?? 0, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif

        <!-- Notes -->
        @if($po->notes)
            <div class="notes-section">
                <strong style="color: #374151;">Notes:</strong>
                <p style="margin-top: 5px; color: #6b7280;">{{ $po->notes }}</p>
            </div>
        @endif

        <!-- Terms and Conditions -->
        <div class="terms-section">
            <div class="terms-title">📋 Terms and Conditions</div>
            <ol class="terms-list">
                @foreach(explode("\n", $po->terms_and_conditions) as $term)
                    @if(trim($term))
                        <li>{{ str_replace(['1. ', '2. ', '3. ', '4. ', '5. ', '6. ', '7. ', '8. '], '', $term) }}</li>
                    @endif
                @endforeach
            </ol>
        </div>

        <!-- Footer -->
        <div class="footer">
            <p style="font-size: 10px; color: #666; text-align: center;">
                This is a system-generated Purchase Order. No signature is required.
            </p>
            <p style="font-size: 10px; color: #666; text-align: center; margin-top: 5px;">
                Generated on {{ now()->format('d M Y, h:i A') }} | {{ config('app.url') }}
            </p>
        </div>

        <!-- Signature Section -->
        <div class="signature-section">
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Customer Confirmation</strong><br>
                    <span style="font-size: 9px; color: #666;">{{ $po->customer->name }}</span>
                </div>
            </div>
            <div class="signature-box">
                <div class="signature-line">
                    <strong>Vendor Acknowledgement</strong><br>
                    <span style="font-size: 9px; color: #666;">{{ $po->vendor->name }}</span>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
