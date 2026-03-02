{{-- resources/views/invoices/gst-invoice.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        /* ─── Reset & Base ─────────────────────────────────── */
        * { margin: 0; padding: 0; box-sizing: border-box; }

        @page { margin: 0; size: A4; }

        body {
            font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
            font-size: 11px;
            color: #1a1a2e;
            background: #fff;
            line-height: 1.5;
        }

        /* ─── Page Wrapper ─────────────────────────────────── */
        .page {
            width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: #fff;
            position: relative;
            overflow: hidden;
        }

        /* ─── Decorative Accent Bar ─────────────────────────── */
        .accent-bar {
            height: 6px;
            background: linear-gradient(90deg, #0f4c81 0%, #1a7fc1 50%, #00b4d8 100%);
        }

        /* ─── Header ────────────────────────────────────────── */
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            padding: 24px 32px 20px;
            border-bottom: 1px solid #e8ecf0;
            background: #f8fafc;
        }

        .company-block {}

        .company-name {
            font-size: 20px;
            font-weight: 800;
            color: #0f4c81;
            letter-spacing: -0.5px;
            margin-bottom: 2px;
        }

        .company-tagline {
            font-size: 9px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            margin-bottom: 8px;
        }

        .company-meta {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.7;
        }

        .company-meta strong { color: #1a1a2e; }

        .invoice-badge-block {
            text-align: right;
        }

        .invoice-label {
            font-size: 26px;
            font-weight: 900;
            color: #0f4c81;
            letter-spacing: -1px;
            line-height: 1;
        }

        .invoice-sub {
            font-size: 10px;
            color: #6b7280;
            margin-bottom: 10px;
        }

        .invoice-number-pill {
            display: inline-block;
            background: #0f4c81;
            color: #fff;
            font-size: 11px;
            font-weight: 700;
            padding: 4px 14px;
            border-radius: 20px;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
        }

        .status-badge {
            display: inline-block;
            font-size: 9px;
            font-weight: 700;
            padding: 3px 10px;
            border-radius: 12px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .status-paid     { background: #d1fae5; color: #065f46; }
        .status-issued   { background: #fef3c7; color: #92400e; }
        .status-sent     { background: #dbeafe; color: #1e40af; }
        .status-overdue  { background: #fee2e2; color: #991b1b; }
        .status-partial  { background: #ede9fe; color: #5b21b6; }
        .status-cancelled{ background: #f3f4f6; color: #374151; }
        .status-draft    { background: #f3f4f6; color: #374151; }

        /* ─── Meta Strip ────────────────────────────────────── */
        .meta-strip {
            display: flex;
            gap: 0;
            border-bottom: 1px solid #e8ecf0;
        }

        .meta-cell {
            flex: 1;
            padding: 10px 16px;
            border-right: 1px solid #e8ecf0;
        }
        .meta-cell:last-child { border-right: none; }

        .meta-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .meta-value {
            font-size: 11px;
            font-weight: 700;
            color: #1a1a2e;
        }

        /* ─── Parties Section ───────────────────────────────── */
        .parties {
            display: flex;
            gap: 0;
            padding: 0;
            border-bottom: 2px solid #e8ecf0;
        }

        .party-block {
            flex: 1;
            padding: 18px 32px;
            border-right: 1px solid #e8ecf0;
        }
        .party-block:last-child { border-right: none; }

        .party-title {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.2px;
            color: #0f4c81;
            margin-bottom: 8px;
            padding-bottom: 4px;
            border-bottom: 2px solid #0f4c81;
            display: inline-block;
        }

        .party-name {
            font-size: 13px;
            font-weight: 800;
            color: #1a1a2e;
            margin-bottom: 4px;
        }

        .party-detail {
            font-size: 10px;
            color: #4b5563;
            line-height: 1.8;
        }

        .party-detail strong { color: #1a1a2e; font-weight: 600; }

        .gstin-badge {
            display: inline-block;
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            color: #1e40af;
            font-size: 9.5px;
            font-weight: 700;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
            margin-top: 4px;
        }

        /* ─── Items Table ───────────────────────────────────── */
        .items-section { padding: 0; }

        .section-heading {
            background: #0f4c81;
            color: #fff;
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 6px 32px;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
        }

        .items-table thead tr {
            background: #f0f5ff;
        }

        .items-table thead th {
            font-size: 9px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #374151;
            padding: 8px 10px;
            border-bottom: 2px solid #dbeafe;
            text-align: left;
        }

        .items-table thead th.text-right { text-align: right; }
        .items-table thead th.text-center { text-align: center; }

        .items-table tbody tr {
            border-bottom: 1px solid #f3f4f6;
        }

        .items-table tbody tr:hover { background: #fafbff; }

        .items-table tbody td {
            padding: 10px 10px;
            font-size: 10.5px;
            color: #374151;
            vertical-align: top;
        }

        .items-table tbody td.text-right { text-align: right; }
        .items-table tbody td.text-center { text-align: center; }

        .item-description { font-weight: 600; color: #1a1a2e; }
        .item-sub { font-size: 9.5px; color: #6b7280; margin-top: 2px; }

        .hsn-chip {
            display: inline-block;
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            color: #166534;
            font-size: 8.5px;
            padding: 1px 6px;
            border-radius: 3px;
            font-family: monospace;
            font-weight: 600;
        }

        /* ─── Totals Area ───────────────────────────────────── */
        .totals-area {
            display: flex;
            border-top: 2px solid #e8ecf0;
        }

        .words-section {
            flex: 1;
            padding: 16px 32px;
            border-right: 1px solid #e8ecf0;
        }

        .words-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .words-value {
            font-size: 11px;
            font-weight: 600;
            color: #1a1a2e;
            font-style: italic;
        }

        .totals-table-wrap {
            width: 260px;
            padding: 12px 32px 12px 16px;
        }

        .totals-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 3px 0;
            font-size: 10.5px;
            color: #4b5563;
        }

        .totals-row .t-label { color: #6b7280; }
        .totals-row .t-value { font-weight: 600; color: #1a1a2e; font-family: 'Courier New', monospace; }

        .totals-divider {
            border: none;
            border-top: 1px solid #e8ecf0;
            margin: 6px 0;
        }

        .grand-total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: #0f4c81;
            color: #fff;
            padding: 8px 12px;
            border-radius: 6px;
            margin-top: 6px;
        }

        .grand-total-row .gt-label {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
        }

        .grand-total-row .gt-value {
            font-size: 14px;
            font-weight: 900;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        /* ─── Tax Breakdown ─────────────────────────────────── */
        .tax-section {
            padding: 12px 32px;
            border-top: 1px solid #e8ecf0;
            background: #f8fafc;
        }

        .tax-table {
            width: 100%;
            border-collapse: collapse;
        }

        .tax-table th {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            color: #6b7280;
            padding: 4px 8px;
            background: #f0f5ff;
            border: 1px solid #e8ecf0;
            text-align: center;
        }

        .tax-table td {
            font-size: 10px;
            color: #374151;
            padding: 5px 8px;
            border: 1px solid #e8ecf0;
            text-align: center;
        }

        /* ─── Payment Status Block ──────────────────────────── */
        .payment-block {
            display: flex;
            gap: 12px;
            padding: 14px 32px;
            border-top: 1px solid #e8ecf0;
            background: #fff;
            align-items: center;
        }

        .payment-info {
            flex: 1;
        }

        .payment-info-label {
            font-size: 8.5px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #9ca3af;
            margin-bottom: 2px;
            font-weight: 600;
        }

        .payment-row {
            display: flex;
            gap: 24px;
            flex-wrap: wrap;
        }

        .payment-item { }
        .payment-item-label { font-size: 9px; color: #6b7280; margin-bottom: 1px; }
        .payment-item-value { font-size: 11px; font-weight: 700; color: #1a1a2e; }

        .paid-stamp {
            text-align: center;
            padding: 8px 18px;
            border: 3px solid #059669;
            border-radius: 8px;
            transform: rotate(-6deg);
        }

        .paid-stamp-text {
            font-size: 16px;
            font-weight: 900;
            color: #059669;
            text-transform: uppercase;
            letter-spacing: 3px;
            line-height: 1;
        }

        .paid-stamp-date {
            font-size: 8px;
            color: #059669;
            margin-top: 2px;
        }

        .pending-stamp {
            text-align: center;
            padding: 8px 18px;
            border: 3px solid #d97706;
            border-radius: 8px;
            transform: rotate(-6deg);
        }

        .pending-stamp-text {
            font-size: 14px;
            font-weight: 900;
            color: #d97706;
            text-transform: uppercase;
            letter-spacing: 2px;
            line-height: 1;
        }

        .pending-stamp-sub {
            font-size: 8px;
            color: #d97706;
            margin-top: 2px;
        }

        /* ─── QR + Sign Block ───────────────────────────────── */
        .qr-sign-block {
            display: flex;
            justify-content: space-between;
            align-items: flex-end;
            padding: 16px 32px;
            border-top: 1px solid #e8ecf0;
        }

        .qr-wrap {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .qr-img {
            width: 70px;
            height: 70px;
            border: 1px solid #e8ecf0;
            border-radius: 6px;
            padding: 4px;
        }

        .qr-label {
            font-size: 8.5px;
            color: #6b7280;
            line-height: 1.5;
        }

        .qr-label strong { color: #1a1a2e; font-size: 9px; }

        .sign-block {
            text-align: right;
        }

        .sign-line {
            width: 160px;
            border-top: 1.5px solid #374151;
            margin-left: auto;
            margin-bottom: 4px;
        }

        .sign-label {
            font-size: 9px;
            color: #6b7280;
        }

        .sign-company {
            font-size: 10px;
            font-weight: 700;
            color: #0f4c81;
        }

        /* ─── Terms & Footer ────────────────────────────────── */
        .terms-section {
            padding: 12px 32px;
            border-top: 1px solid #e8ecf0;
            background: #f8fafc;
        }

        .terms-title {
            font-size: 8.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #374151;
            margin-bottom: 4px;
        }

        .terms-text {
            font-size: 9px;
            color: #6b7280;
            line-height: 1.6;
        }

        .footer-bar {
            height: 5px;
            background: linear-gradient(90deg, #0f4c81 0%, #1a7fc1 50%, #00b4d8 100%);
            margin-top: auto;
        }

        /* ─── Reverse Charge Notice ─────────────────────────── */
        .rc-notice {
            background: #fffbeb;
            border: 1px solid #fde68a;
            padding: 5px 32px;
            font-size: 9.5px;
            color: #92400e;
            font-weight: 600;
        }

        /* ─── Utilities ─────────────────────────────────────── */
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .fw-700 { font-weight: 700; }
        .text-muted { color: #9ca3af; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Accent Top Bar ── --}}
    <div class="accent-bar"></div>

    {{-- ── Header: Company + Invoice Badge ── --}}
    <div class="header">
        <div class="company-block">
            <div class="company-name">{{ $invoice->seller_name }}</div>
            <div class="company-tagline">Outdoor Advertising Solutions</div>
            <div class="company-meta">
                {{ $invoice->seller_address }}, {{ $invoice->seller_city }}<br>
                {{ $invoice->seller_state }} – {{ $invoice->seller_pincode }}<br>
                <strong>GSTIN:</strong> <span style="font-family:monospace">{{ $invoice->seller_gstin }}</span>
                @if($invoice->seller_pan)
                    &nbsp;&nbsp;<strong>PAN:</strong> <span style="font-family:monospace">{{ $invoice->seller_pan }}</span>
                @endif
            </div>
        </div>

        <div class="invoice-badge-block">
            <div class="invoice-label">TAX INVOICE</div>
            <div class="invoice-sub">GST INVOICE · {{ $invoice->supply_type === 'services' ? 'SAC' : 'HSN' }} Applicable</div>
            <div>
                <div class="invoice-number-pill">{{ $invoice->invoice_number }}</div>
            </div>
            <div style="margin-top:4px">
                @php
                    $statusClass = match($invoice->status) {
                        'paid'           => 'status-paid',
                        'partially_paid' => 'status-partial',
                        'issued'         => 'status-issued',
                        'sent'           => 'status-sent',
                        'overdue'        => 'status-overdue',
                        'cancelled'      => 'status-cancelled',
                        default          => 'status-draft',
                    };
                    $statusLabel = match($invoice->status) {
                        'paid'           => 'PAID',
                        'partially_paid' => 'PARTIALLY PAID',
                        'issued'         => 'PAYMENT DUE',
                        'sent'           => 'SENT',
                        'overdue'        => 'OVERDUE',
                        'cancelled'      => 'CANCELLED',
                        default          => strtoupper($invoice->status),
                    };
                @endphp
                <span class="status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
        </div>
    </div>

    {{-- ── Reverse Charge Notice ── --}}
    @if($invoice->is_reverse_charge)
        <div class="rc-notice">
            ⚠ Reverse Charge Applicable – Tax to be paid by recipient of service
        </div>
    @endif

    {{-- ── Meta Strip ── --}}
    <div class="meta-strip">
        <div class="meta-cell">
            <div class="meta-label">Invoice Date</div>
            <div class="meta-value">{{ $invoice->invoice_date->format('d M Y') }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Due Date</div>
            <div class="meta-value" style="{{ $invoice->due_date && $invoice->due_date->isPast() && !$invoice->isPaid() ? 'color:#dc2626' : '' }}">
                {{ $invoice->due_date ? $invoice->due_date->format('d M Y') : '—' }}
            </div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Financial Year</div>
            <div class="meta-value">{{ $invoice->financial_year }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Place of Supply</div>
            <div class="meta-value">{{ $invoice->place_of_supply }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">GST Type</div>
            <div class="meta-value">{{ $invoice->is_intra_state ? 'Intra-State (CGST+SGST)' : 'Inter-State (IGST)' }}</div>
        </div>
        <div class="meta-cell">
            <div class="meta-label">Payment Terms</div>
            <div class="meta-value">{{ $invoice->payment_terms ?? 'Net 30 Days' }}</div>
        </div>
    </div>

    {{-- ── Bill From / Bill To ── --}}
    <div class="parties">
        <div class="party-block">
            <div class="party-title">Bill From (Seller)</div>
            <div class="party-name">{{ $invoice->seller_name }}</div>
            <div class="party-detail">
                {{ $invoice->seller_address }}<br>
                {{ $invoice->seller_city }}, {{ $invoice->seller_state }} – {{ $invoice->seller_pincode }}
            </div>
            <div class="gstin-badge">GSTIN: {{ $invoice->seller_gstin }}</div>
        </div>

        <div class="party-block">
            <div class="party-title">Bill To (Buyer)</div>
            <div class="party-name">{{ $invoice->buyer_name }}</div>
            <div class="party-detail">
                @if($invoice->buyer_address){{ $invoice->buyer_address }}<br>@endif
                @if($invoice->buyer_city){{ $invoice->buyer_city }}, @endif
                @if($invoice->buyer_state){{ $invoice->buyer_state }}@endif
                @if($invoice->buyer_pincode) – {{ $invoice->buyer_pincode }}@endif
                @if($invoice->buyer_email)<br><strong>Email:</strong> {{ $invoice->buyer_email }}@endif
                @if($invoice->buyer_phone)<br><strong>Phone:</strong> {{ $invoice->buyer_phone }}@endif
                @if($invoice->buyer_pan)<br><strong>PAN:</strong> <span style="font-family:monospace">{{ $invoice->buyer_pan }}</span>@endif
            </div>
            @if($invoice->buyer_gstin)
                <div class="gstin-badge">GSTIN: {{ $invoice->buyer_gstin }}</div>
            @else
                <div style="margin-top:4px;font-size:9px;color:#9ca3af;font-style:italic">Unregistered Dealer</div>
            @endif
        </div>
    </div>

    {{-- ── Items Table ── --}}
    <div class="items-section">
        <div class="section-heading">Description of Services</div>
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width:30px">#</th>
                    <th>Description of Service</th>
                    <th class="text-center" style="width:52px">SAC Code</th>
                    <th class="text-center" style="width:60px">Period</th>
                    <th class="text-right" style="width:45px">Qty</th>
                    <th class="text-right" style="width:70px">Rate</th>
                    <th class="text-right" style="width:65px">Amount</th>
                    <th class="text-right" style="width:55px">Discount</th>
                    <th class="text-right" style="width:72px">Taxable Amt</th>
                    @if($invoice->is_intra_state)
                        <th class="text-right" style="width:58px">CGST</th>
                        <th class="text-right" style="width:58px">SGST</th>
                    @else
                        <th class="text-right" style="width:68px">IGST</th>
                    @endif
                    <th class="text-right" style="width:72px">Total</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $item)
                <tr>
                    <td class="text-center" style="color:#9ca3af">{{ $item->line_number }}</td>
                    <td>
                        <div class="item-description">{{ $item->description }}</div>
                        @if($item->service_start_date && $item->service_end_date)
                            <div class="item-sub">{{ $item->service_start_date->format('d M Y') }} to {{ $item->service_end_date->format('d M Y') }}</div>
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->hsn_sac_code)
                            <span class="hsn-chip">{{ $item->hsn_sac_code }}</span>
                        @else —
                        @endif
                    </td>
                    <td class="text-center">
                        @if($item->duration_days)
                            {{ $item->duration_days }}<br>
                            <span class="text-muted">{{ $item->unit ?? 'days' }}</span>
                        @else —
                        @endif
                    </td>
                    <td class="text-right">{{ number_format($item->quantity, 0) }}</td>
                    <td class="text-right">₹{{ number_format($item->rate, 2) }}</td>
                    <td class="text-right">₹{{ number_format($item->amount, 2) }}</td>
                    <td class="text-right">
                        @if($item->discount_amount > 0)
                            ₹{{ number_format($item->discount_amount, 2) }}
                            @if($item->discount_percent > 0)
                                <br><span class="text-muted">({{ $item->discount_percent }}%)</span>
                            @endif
                        @else
                            —
                        @endif
                    </td>
                    <td class="text-right fw-700">₹{{ number_format($item->taxable_amount, 2) }}</td>
                    @if($invoice->is_intra_state)
                        <td class="text-right">
                            ₹{{ number_format($item->cgst_amount, 2) }}<br>
                            <span class="text-muted">({{ $item->cgst_rate }}%)</span>
                        </td>
                        <td class="text-right">
                            ₹{{ number_format($item->sgst_amount, 2) }}<br>
                            <span class="text-muted">({{ $item->sgst_rate }}%)</span>
                        </td>
                    @else
                        <td class="text-right">
                            ₹{{ number_format($item->igst_amount, 2) }}<br>
                            <span class="text-muted">({{ $item->igst_rate }}%)</span>
                        </td>
                    @endif
                    <td class="text-right fw-700">₹{{ number_format($item->total_amount, 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Tax Summary Table ── --}}
    <div class="tax-section">
        <div style="font-size:8.5px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:#374151;margin-bottom:6px;">
            Tax Summary
        </div>
        <table class="tax-table">
            <thead>
                <tr>
                    <th>HSN/SAC</th>
                    <th>Taxable Value</th>
                    @if($invoice->is_intra_state)
                        <th>CGST Rate</th><th>CGST Amt</th>
                        <th>SGST Rate</th><th>SGST Amt</th>
                    @else
                        <th>IGST Rate</th><th>IGST Amt</th>
                    @endif
                    <th>Total Tax</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items->groupBy('hsn_sac_code') as $hsnCode => $groupItems)
                <tr>
                    <td><span class="hsn-chip">{{ $hsnCode ?: '998599' }}</span></td>
                    <td>₹{{ number_format($groupItems->sum('taxable_amount'), 2) }}</td>
                    @if($invoice->is_intra_state)
                        <td>{{ $groupItems->first()->cgst_rate }}%</td>
                        <td>₹{{ number_format($groupItems->sum('cgst_amount'), 2) }}</td>
                        <td>{{ $groupItems->first()->sgst_rate }}%</td>
                        <td>₹{{ number_format($groupItems->sum('sgst_amount'), 2) }}</td>
                    @else
                        <td>{{ $groupItems->first()->igst_rate }}%</td>
                        <td>₹{{ number_format($groupItems->sum('igst_amount'), 2) }}</td>
                    @endif
                    <td>₹{{ number_format($groupItems->sum('total_tax'), 2) }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ── Totals Area ── --}}
    <div class="totals-area">
        <div class="words-section">
            <div class="words-label">Amount in Words</div>
            <div class="words-value">{{ \App\Helpers\NumberToWords::convert($invoice->grand_total) }} Only</div>

            @if($invoice->notes)
                <div style="margin-top:12px">
                    <div class="words-label">Notes</div>
                    <div style="font-size:10px;color:#4b5563;margin-top:2px">{{ $invoice->notes }}</div>
                </div>
            @endif
        </div>

        <div class="totals-table-wrap">
            <div class="totals-row">
                <span class="t-label">Sub Total</span>
                <span class="t-value">₹{{ number_format($invoice->subtotal, 2) }}</span>
            </div>
            @if($invoice->discount_amount > 0)
            <div class="totals-row">
                <span class="t-label">Discount</span>
                <span class="t-value" style="color:#dc2626">- ₹{{ number_format($invoice->discount_amount, 2) }}</span>
            </div>
            @endif
            <div class="totals-row">
                <span class="t-label">Taxable Amount</span>
                <span class="t-value">₹{{ number_format($invoice->taxable_amount, 2) }}</span>
            </div>
            <hr class="totals-divider">
            @if($invoice->is_intra_state)
                <div class="totals-row">
                    <span class="t-label">CGST ({{ $invoice->cgst_rate }}%)</span>
                    <span class="t-value">₹{{ number_format($invoice->cgst_amount, 2) }}</span>
                </div>
                <div class="totals-row">
                    <span class="t-label">SGST ({{ $invoice->sgst_rate }}%)</span>
                    <span class="t-value">₹{{ number_format($invoice->sgst_amount, 2) }}</span>
                </div>
            @else
                <div class="totals-row">
                    <span class="t-label">IGST ({{ $invoice->igst_rate }}%)</span>
                    <span class="t-value">₹{{ number_format($invoice->igst_amount, 2) }}</span>
                </div>
            @endif
            <div class="totals-row">
                <span class="t-label">Total Tax</span>
                <span class="t-value">₹{{ number_format($invoice->total_tax, 2) }}</span>
            </div>
            @if($invoice->round_off != 0)
            <div class="totals-row">
                <span class="t-label">Round Off</span>
                <span class="t-value">{{ $invoice->round_off >= 0 ? '+' : '' }}₹{{ number_format($invoice->round_off, 2) }}</span>
            </div>
            @endif
            <hr class="totals-divider">
            <div class="grand-total-row">
                <span class="gt-label">Grand Total</span>
                <span class="gt-value">₹{{ number_format($invoice->grand_total, 2) }}</span>
            </div>

            {{-- Balance Due --}}
            @if(!$invoice->isPaid() && !$invoice->isCancelled())
                @php $balance = $invoice->getBalanceDue(); @endphp
                @if($invoice->paid_amount > 0)
                <div class="totals-row" style="margin-top:6px">
                    <span class="t-label">Paid Amount</span>
                    <span class="t-value" style="color:#059669">₹{{ number_format($invoice->paid_amount, 2) }}</span>
                </div>
                @endif
                <div class="totals-row" style="background:#fff7ed;border-radius:4px;padding:4px 8px;margin-top:4px">
                    <span style="font-weight:700;color:#92400e;font-size:11px">Balance Due</span>
                    <span style="font-weight:800;color:#92400e;font-size:12px;font-family:monospace">₹{{ number_format($balance, 2) }}</span>
                </div>
            @endif
        </div>
    </div>

    {{-- ── Payment Status Block ── --}}
    <div class="payment-block">
        <div class="payment-info">
            <div class="payment-info-label">Payment Information</div>
            <div class="payment-row">
                <div class="payment-item">
                    <div class="payment-item-label">Payment Mode</div>
                    <div class="payment-item-value">{{ ucwords(str_replace('_', ' ', $invoice->booking?->payment_mode ?? 'N/A')) }}</div>
                </div>
                @if($invoice->paid_at)
                <div class="payment-item">
                    <div class="payment-item-label">Paid On</div>
                    <div class="payment-item-value">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</div>
                </div>
                @endif
                @if($invoice->paid_amount > 0)
                <div class="payment-item">
                    <div class="payment-item-label">Amount Paid</div>
                    <div class="payment-item-value" style="color:#059669">₹{{ number_format($invoice->paid_amount, 2) }}</div>
                </div>
                @endif
                @if(!$invoice->isPaid() && $invoice->due_date)
                <div class="payment-item">
                    <div class="payment-item-label">Due Date</div>
                    <div class="payment-item-value" style="{{ $invoice->due_date->isPast() ? 'color:#dc2626' : '' }}">
                        {{ $invoice->due_date->format('d M Y') }}
                        @if($invoice->due_date->isPast()) (Overdue) @endif
                    </div>
                </div>
                @endif
            </div>
        </div>

        {{-- Stamp --}}
        <div>
            @if($invoice->isPaid())
                <div class="paid-stamp">
                    <div class="paid-stamp-text">✓ PAID</div>
                    @if($invoice->paid_at)
                        <div class="paid-stamp-date">{{ \Carbon\Carbon::parse($invoice->paid_at)->format('d M Y') }}</div>
                    @endif
                </div>
            @elseif($invoice->isOverdue())
                <div class="pending-stamp" style="border-color:#dc2626">
                    <div class="pending-stamp-text" style="color:#dc2626">OVERDUE</div>
                    <div class="pending-stamp-sub" style="color:#dc2626">Immediate payment required</div>
                </div>
            @elseif($invoice->isPartiallyPaid())
                <div class="pending-stamp" style="border-color:#7c3aed">
                    <div class="pending-stamp-text" style="color:#7c3aed;font-size:11px">PARTIAL</div>
                    <div class="pending-stamp-sub" style="color:#7c3aed">Balance: ₹{{ number_format($invoice->getBalanceDue(), 2) }}</div>
                </div>
            @else
                <div class="pending-stamp">
                    <div class="pending-stamp-text">UNPAID</div>
                    <div class="pending-stamp-sub">Due: {{ $invoice->due_date?->format('d M Y') ?? 'N/A' }}</div>
                </div>
            @endif
        </div>
    </div>

    {{-- ── QR Code + Signature ── --}}
    <div class="qr-sign-block">
        <div class="qr-wrap">
            @if($invoice->qr_code_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($invoice->qr_code_path))
                <img src="{{ 'data:image/png;base64,' . base64_encode(\Illuminate\Support\Facades\Storage::disk('public')->get($invoice->qr_code_path)) }}"
                     class="qr-img" alt="Invoice QR Code">
                <div class="qr-label">
                    <strong>Scan to Verify</strong><br>
                    Invoice: {{ $invoice->invoice_number }}<br>
                    GSTIN: {{ $invoice->seller_gstin }}
                </div>
            @endif
        </div>

        <div class="sign-block">
            <div class="sign-line"></div>
            <div class="sign-label">Authorised Signatory</div>
            <div class="sign-company">{{ $invoice->seller_name }}</div>
        </div>
    </div>

    {{-- ── Terms & Conditions ── --}}
    @if($invoice->terms_conditions)
    <div class="terms-section">
        <div class="terms-title">Terms & Conditions</div>
        <div class="terms-text">{{ $invoice->terms_conditions }}</div>
    </div>
    @endif

    {{-- ── Footer Bar ── --}}
    <div style="text-align:center;font-size:8.5px;color:#9ca3af;padding:8px 32px;border-top:1px solid #e8ecf0;background:#f8fafc">
        This is a computer-generated invoice. No physical signature required. &nbsp;|&nbsp;
        Invoice generated on {{ now()->format('d M Y, h:i A') }} &nbsp;|&nbsp;
        {{ $invoice->invoice_number }}
    </div>
    <div class="footer-bar"></div>

</div>
</body>
</html>