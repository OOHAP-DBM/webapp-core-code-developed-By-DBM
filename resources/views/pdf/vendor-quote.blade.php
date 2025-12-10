<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Quote - {{ $quote->quote_number }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.4;
        }
        .container {
            padding: 20px;
        }
        .header {
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
            margin-bottom: 20px;
        }
        .logo {
            font-size: 24px;
            font-weight: bold;
            color: #2563eb;
            margin-bottom: 5px;
        }
        .quote-number {
            font-size: 14px;
            color: #666;
        }
        .info-section {
            margin-bottom: 20px;
        }
        .info-grid {
            display: table;
            width: 100%;
        }
        .info-column {
            display: table-cell;
            width: 50%;
            vertical-align: top;
        }
        .info-box {
            margin-bottom: 15px;
        }
        .info-label {
            font-size: 9px;
            color: #666;
            text-transform: uppercase;
            margin-bottom: 3px;
        }
        .info-value {
            font-size: 11px;
            font-weight: 600;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #f3f4f6;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: 600;
            border-bottom: 2px solid #d1d5db;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #e5e7eb;
        }
        .text-right {
            text-align: right;
        }
        .pricing-table {
            margin-top: 20px;
        }
        .pricing-row {
            display: table;
            width: 100%;
            margin-bottom: 5px;
        }
        .pricing-label {
            display: table-cell;
            width: 70%;
            text-align: right;
            padding-right: 10px;
        }
        .pricing-value {
            display: table-cell;
            width: 30%;
            text-align: right;
            font-weight: 600;
        }
        .total-row {
            border-top: 2px solid #333;
            padding-top: 8px;
            margin-top: 8px;
            font-size: 13px;
        }
        .total-row .pricing-label,
        .total-row .pricing-value {
            font-weight: bold;
            color: #2563eb;
        }
        .terms-section {
            margin-top: 30px;
            page-break-inside: avoid;
        }
        .terms-title {
            font-size: 12px;
            font-weight: bold;
            margin-bottom: 10px;
            color: #2563eb;
        }
        .terms-category {
            font-weight: 600;
            margin-top: 8px;
            margin-bottom: 3px;
        }
        .terms-list {
            margin-left: 15px;
            margin-bottom: 8px;
        }
        .terms-list li {
            margin-bottom: 3px;
        }
        .footer {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #e5e7eb;
            font-size: 9px;
            color: #666;
            text-align: center;
        }
        .status-badge {
            display: inline-block;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-draft { background: #f3f4f6; color: #6b7280; }
        .status-sent { background: #dbeafe; color: #1e40af; }
        .status-accepted { background: #d1fae5; color: #065f46; }
        .vendor-notes {
            background: #fef3c7;
            padding: 10px;
            border-left: 3px solid #f59e0b;
            margin: 15px 0;
        }
        .highlight {
            background: #fef9c3;
            padding: 2px 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="logo">OOH App</div>
            <div class="quote-number">Quote #{{ $quote->quote_number }} 
                @if($quote->version > 1) (Version {{ $quote->version }}) @endif
            </div>
        </div>

        <!-- Quote Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-label">Quote Date</div>
                        <div class="info-value">{{ $quote->created_at->format('d M Y') }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Valid Until</div>
                        <div class="info-value">{{ $quote->expires_at->format('d M Y') }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Status</div>
                        <div class="info-value">
                            <span class="status-badge status-{{ $quote->status }}">{{ ucfirst($quote->status) }}</span>
                        </div>
                    </div>
                </div>
                <div class="info-column">
                    <div class="info-box">
                        <div class="info-label">Customer</div>
                        <div class="info-value">{{ $quote->customer->name }}</div>
                        <div style="font-size: 10px; color: #666;">{{ $quote->customer->email }}</div>
                    </div>
                    <div class="info-box">
                        <div class="info-label">Vendor</div>
                        <div class="info-value">{{ $quote->vendor->name }}</div>
                        <div style="font-size: 10px; color: #666;">{{ $quote->vendor->email }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Hoarding Details -->
        <div class="info-section">
            <h3 style="margin-bottom: 10px; color: #2563eb;">Hoarding Details</h3>
            <table>
                <tr>
                    <th style="width: 30%;">Property</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td><strong>Title</strong></td>
                    <td>{{ $quote->hoarding->title ?? $quote->hoarding_snapshot['title'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Location</strong></td>
                    <td>{{ $quote->hoarding->location ?? $quote->hoarding_snapshot['location'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Type</strong></td>
                    <td>{{ ucfirst($quote->hoarding->type ?? $quote->hoarding_snapshot['type'] ?? 'N/A') }}</td>
                </tr>
                <tr>
                    <td><strong>Dimensions</strong></td>
                    <td>{{ $quote->hoarding->dimensions ?? $quote->hoarding_snapshot['dimensions'] ?? 'N/A' }}</td>
                </tr>
            </table>
        </div>

        <!-- Campaign Duration -->
        <div class="info-section">
            <h3 style="margin-bottom: 10px; color: #2563eb;">Campaign Duration</h3>
            <table>
                <tr>
                    <th style="width: 30%;">Period</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td><strong>Start Date</strong></td>
                    <td>{{ $quote->start_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td><strong>End Date</strong></td>
                    <td>{{ $quote->end_date->format('d M Y') }}</td>
                </tr>
                <tr>
                    <td><strong>Duration</strong></td>
                    <td>{{ $quote->duration_days }} {{ ucfirst($quote->duration_type) }}</td>
                </tr>
            </table>
        </div>

        <!-- Pricing Breakdown -->
        <div class="info-section">
            <h3 style="margin-bottom: 10px; color: #2563eb;">Pricing Breakdown</h3>
            <table>
                <tr>
                    <th>Item</th>
                    <th class="text-right">Amount (₹)</th>
                </tr>
                <tr>
                    <td>Base Price</td>
                    <td class="text-right">{{ number_format($quote->base_price, 2) }}</td>
                </tr>
                @if($quote->printing_cost > 0)
                <tr>
                    <td>Printing Cost</td>
                    <td class="text-right">{{ number_format($quote->printing_cost, 2) }}</td>
                </tr>
                @endif
                @if($quote->mounting_cost > 0)
                <tr>
                    <td>Mounting Cost</td>
                    <td class="text-right">{{ number_format($quote->mounting_cost, 2) }}</td>
                </tr>
                @endif
                @if($quote->survey_cost > 0)
                <tr>
                    <td>Survey Cost</td>
                    <td class="text-right">{{ number_format($quote->survey_cost, 2) }}</td>
                </tr>
                @endif
                @if($quote->lighting_cost > 0)
                <tr>
                    <td>Lighting Cost</td>
                    <td class="text-right">{{ number_format($quote->lighting_cost, 2) }}</td>
                </tr>
                @endif
                @if($quote->maintenance_cost > 0)
                <tr>
                    <td>Maintenance Cost</td>
                    <td class="text-right">{{ number_format($quote->maintenance_cost, 2) }}</td>
                </tr>
                @endif
                @if($quote->other_charges > 0)
                <tr>
                    <td>Other Charges @if($quote->other_charges_description) ({{ $quote->other_charges_description }}) @endif</td>
                    <td class="text-right">{{ number_format($quote->other_charges, 2) }}</td>
                </tr>
                @endif
                <tr style="border-top: 2px solid #d1d5db;">
                    <td><strong>Subtotal</strong></td>
                    <td class="text-right"><strong>{{ number_format($quote->subtotal, 2) }}</strong></td>
                </tr>
                @if($quote->discount_amount > 0)
                <tr>
                    <td>Discount @if($quote->discount_percentage > 0) ({{ $quote->discount_percentage }}%) @endif</td>
                    <td class="text-right" style="color: #059669;">- {{ number_format($quote->discount_amount, 2) }}</td>
                </tr>
                @endif
                <tr>
                    <td>Tax ({{ $quote->tax_percentage }}% GST)</td>
                    <td class="text-right">{{ number_format($quote->tax_amount, 2) }}</td>
                </tr>
                <tr style="border-top: 3px solid #333; background: #f3f4f6;">
                    <td style="font-size: 13px;"><strong>Grand Total</strong></td>
                    <td class="text-right" style="font-size: 14px; color: #2563eb;"><strong>₹ {{ number_format($quote->grand_total, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- Vendor Notes -->
        @if($quote->vendor_notes)
        <div class="vendor-notes">
            <strong>Vendor Notes:</strong><br>
            {{ $quote->vendor_notes }}
        </div>
        @endif

        <!-- Terms and Conditions -->
        @if($quote->terms_and_conditions)
        <div class="terms-section">
            <div class="terms-title">Terms & Conditions</div>
            @foreach($quote->terms_and_conditions as $category => $terms)
                <div class="terms-category">{{ ucfirst($category) }}:</div>
                <ul class="terms-list">
                    @foreach($terms as $term)
                        <li>{{ $term }}</li>
                    @endforeach
                </ul>
            @endforeach
        </div>
        @endif

        <!-- Footer -->
        <div class="footer">
            <p>This quote was generated on {{ now()->format('d M Y H:i') }}</p>
            <p>For any queries, please contact {{ $quote->vendor->name }} at {{ $quote->vendor->email }}</p>
            <p style="margin-top: 5px;">OOH App - Your Outdoor Advertising Platform</p>
        </div>
    </div>
</body>
</html>
