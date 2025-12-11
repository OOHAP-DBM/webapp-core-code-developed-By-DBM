<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Milestone Payment Invoice - {{ $milestone->invoice_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 12px; color: #333; }
        .container { width: 100%; max-width: 800px; margin: 0 auto; padding: 20px; }
        
        /* Header */
        .header { border-bottom: 3px solid #0d6efd; padding-bottom: 20px; margin-bottom: 20px; }
        .header h1 { color: #0d6efd; font-size: 28px; margin-bottom: 5px; }
        .header p { color: #666; font-size: 14px; }
        
        /* Company & Customer Info */
        .info-section { display: table; width: 100%; margin-bottom: 20px; }
        .info-left, .info-right { display: table-cell; width: 50%; vertical-align: top; }
        .info-box { background: #f8f9fa; padding: 15px; border-radius: 5px; }
        .info-box h3 { color: #0d6efd; font-size: 14px; margin-bottom: 10px; border-bottom: 1px solid #dee2e6; padding-bottom: 5px; }
        .info-box p { margin: 5px 0; line-height: 1.6; }
        
        /* Invoice Details */
        .invoice-details { background: #e7f3ff; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 4px solid #0d6efd; }
        .invoice-details table { width: 100%; }
        .invoice-details td { padding: 5px 10px; }
        .invoice-details .label { font-weight: bold; color: #0d6efd; width: 40%; }
        
        /* Milestone Badge */
        .milestone-badge { display: inline-block; background: #0d6efd; color: white; padding: 8px 15px; border-radius: 20px; font-size: 14px; font-weight: bold; margin-bottom: 20px; }
        
        /* Payment Schedule Table */
        .payment-schedule { margin: 20px 0; }
        .payment-schedule h3 { color: #333; font-size: 16px; margin-bottom: 10px; padding-bottom: 5px; border-bottom: 2px solid #dee2e6; }
        
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        table thead { background: #0d6efd; color: white; }
        table th { padding: 10px; text-align: left; font-weight: 600; }
        table td { padding: 10px; border-bottom: 1px solid #dee2e6; }
        table tbody tr:hover { background: #f8f9fa; }
        table tbody tr.current-milestone { background: #fff3cd; font-weight: bold; }
        table tbody tr.paid-milestone { background: #d1e7dd; }
        
        /* Amount Breakdown */
        .amount-breakdown { background: #f8f9fa; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .amount-row { display: table; width: 100%; padding: 8px 0; }
        .amount-label { display: table-cell; text-align: left; font-size: 14px; }
        .amount-value { display: table-cell; text-align: right; font-size: 14px; font-weight: bold; }
        .amount-total { border-top: 2px solid #0d6efd; margin-top: 10px; padding-top: 10px; }
        .amount-total .amount-label { font-size: 16px; color: #0d6efd; font-weight: bold; }
        .amount-total .amount-value { font-size: 18px; color: #0d6efd; }
        
        /* Payment Information */
        .payment-info { background: #d1e7dd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #198754; }
        .payment-info h3 { color: #198754; margin-bottom: 10px; font-size: 14px; }
        .payment-info p { margin: 5px 0; }
        
        /* Footer */
        .footer { margin-top: 40px; padding-top: 20px; border-top: 2px solid #dee2e6; text-align: center; color: #666; font-size: 11px; }
        .footer p { margin: 5px 0; }
        
        /* Terms */
        .terms { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; }
        .terms h4 { color: #856404; margin-bottom: 10px; font-size: 13px; }
        .terms ul { margin-left: 20px; }
        .terms li { margin: 5px 0; line-height: 1.6; }
        
        /* Status Badge */
        .status-badge { display: inline-block; padding: 5px 10px; border-radius: 3px; font-size: 11px; font-weight: bold; }
        .status-paid { background: #198754; color: white; }
        .status-pending { background: #6c757d; color: white; }
        .status-due { background: #ffc107; color: #000; }
        .status-overdue { background: #dc3545; color: white; }
        
        /* Print Specific */
        @media print {
            .container { padding: 10px; }
            .no-print { display: none; }
        }
        
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .mt-20 { margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <h1>INVOICE</h1>
            <p>Milestone Payment Receipt</p>
        </div>
        
        <!-- Milestone Badge -->
        <div class="milestone-badge">
            📌 Milestone {{ $milestone->sequence_no }} of {{ $totalMilestones }}
        </div>
        
        <!-- Company and Customer Information -->
        <div class="info-section">
            <div class="info-left">
                <div class="info-box">
                    <h3>FROM</h3>
                    <p><strong>{{ $vendor->name ?? 'OohApp' }}</strong></p>
                    @if(isset($vendor))
                    <p>{{ $vendor->address ?? '' }}</p>
                    <p>{{ $vendor->city ?? '' }}, {{ $vendor->state ?? '' }} {{ $vendor->pincode ?? '' }}</p>
                    <p>Email: {{ $vendor->email ?? '' }}</p>
                    <p>Phone: {{ $vendor->phone ?? '' }}</p>
                    @if($vendor->gst_number)
                    <p><strong>GST:</strong> {{ $vendor->gst_number }}</p>
                    @endif
                    @endif
                </div>
            </div>
            <div class="info-right" style="padding-left: 20px;">
                <div class="info-box">
                    <h3>BILL TO</h3>
                    <p><strong>{{ $customer->name ?? 'N/A' }}</strong></p>
                    @if(isset($customer))
                    <p>{{ $customer->address ?? '' }}</p>
                    <p>{{ $customer->city ?? '' }}, {{ $customer->state ?? '' }} {{ $customer->pincode ?? '' }}</p>
                    <p>Email: {{ $customer->email ?? '' }}</p>
                    <p>Phone: {{ $customer->phone ?? '' }}</p>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Invoice Details -->
        <div class="invoice-details">
            <table>
                <tr>
                    <td class="label">Invoice Number:</td>
                    <td><strong>{{ $milestone->invoice_number }}</strong></td>
                    <td class="label">Quotation ID:</td>
                    <td><strong>#{{ $milestone->quotation_id }}</strong></td>
                </tr>
                <tr>
                    <td class="label">Invoice Date:</td>
                    <td>{{ $milestone->paid_at->format('F d, Y') }}</td>
                    <td class="label">Payment Date:</td>
                    <td>{{ $milestone->paid_at->format('F d, Y h:i A') }}</td>
                </tr>
                <tr>
                    <td class="label">Milestone Title:</td>
                    <td><strong>{{ $milestone->title }}</strong></td>
                    <td class="label">Transaction ID:</td>
                    <td><code>{{ $milestone->payment_transaction_id ?? 'N/A' }}</code></td>
                </tr>
            </table>
        </div>
        
        <!-- Current Milestone Details -->
        <h3 style="margin: 20px 0 10px 0; color: #333; border-bottom: 2px solid #0d6efd; padding-bottom: 5px;">Current Milestone Details</h3>
        <table>
            <thead>
                <tr>
                    <th>Description</th>
                    <th style="text-align: center;">Type</th>
                    <th style="text-align: right;">Amount</th>
                    <th style="text-align: center;">Status</th>
                </tr>
            </thead>
            <tbody>
                <tr class="current-milestone">
                    <td>
                        <strong>{{ $milestone->title }}</strong><br>
                        @if($milestone->description)
                        <small style="color: #666;">{{ $milestone->description }}</small>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ $milestone->amount_type === 'percentage' ? $milestone->amount . '%' : 'Fixed' }}
                    </td>
                    <td class="text-right">₹{{ number_format($milestone->calculated_amount, 2) }}</td>
                    <td class="text-center">
                        <span class="status-badge status-paid">PAID</span>
                    </td>
                </tr>
            </tbody>
        </table>
        
        <!-- Amount Breakdown -->
        <div class="amount-breakdown">
            <div class="amount-row">
                <div class="amount-label">Milestone Amount:</div>
                <div class="amount-value">₹{{ number_format($milestone->calculated_amount, 2) }}</div>
            </div>
            @php
                $gst = $milestone->calculated_amount * 0.18; // 18% GST
                $total = $milestone->calculated_amount + $gst;
            @endphp
            <div class="amount-row">
                <div class="amount-label">GST (18%):</div>
                <div class="amount-value">₹{{ number_format($gst, 2) }}</div>
            </div>
            <div class="amount-row amount-total">
                <div class="amount-label">TOTAL PAID:</div>
                <div class="amount-value">₹{{ number_format($total, 2) }}</div>
            </div>
        </div>
        
        <!-- Payment Information -->
        <div class="payment-info">
            <h3>✓ Payment Confirmed</h3>
            <p><strong>Payment Method:</strong> {{ $milestone->payment_method ?? 'Razorpay' }}</p>
            <p><strong>Transaction ID:</strong> {{ $milestone->payment_transaction_id ?? 'N/A' }}</p>
            <p><strong>Payment Date:</strong> {{ $milestone->paid_at->format('F d, Y h:i A') }}</p>
            <p><strong>Status:</strong> Successfully Received</p>
        </div>
        
        <!-- Complete Payment Schedule -->
        <div class="payment-schedule">
            <h3>Complete Payment Schedule</h3>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Milestone Title</th>
                        <th style="text-align: right;">Amount</th>
                        <th style="text-align: center;">Due Date</th>
                        <th style="text-align: center;">Status</th>
                        <th style="text-align: center;">Paid On</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allMilestones as $index => $m)
                    <tr class="{{ $m->id === $milestone->id ? 'current-milestone' : ($m->status === 'paid' ? 'paid-milestone' : '') }}">
                        <td>{{ $index + 1 }}</td>
                        <td>
                            {{ $m->title }}
                            @if($m->id === $milestone->id)
                                <strong style="color: #0d6efd;">(Current Invoice)</strong>
                            @endif
                        </td>
                        <td class="text-right">₹{{ number_format($m->calculated_amount, 2) }}</td>
                        <td class="text-center">{{ $m->due_date ? $m->due_date->format('M d, Y') : '-' }}</td>
                        <td class="text-center">
                            <span class="status-badge status-{{ $m->status }}">{{ strtoupper($m->status) }}</span>
                        </td>
                        <td class="text-center">{{ $m->paid_at ? $m->paid_at->format('M d, Y') : '-' }}</td>
                    </tr>
                    @endforeach
                    <tr style="background: #e7f3ff; font-weight: bold;">
                        <td colspan="2" class="text-right">TOTAL:</td>
                        <td class="text-right">₹{{ number_format($quotation->grand_total, 2) }}</td>
                        <td colspan="3"></td>
                    </tr>
                </tbody>
            </table>
            
            <!-- Remaining Balance -->
            @php
                $totalPaid = $allMilestones->where('status', 'paid')->sum('calculated_amount');
                $remaining = $quotation->grand_total - $totalPaid;
            @endphp
            <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; margin-top: 10px;">
                <div style="display: table; width: 100%;">
                    <div style="display: table-cell; width: 50%;">
                        <strong>Total Paid:</strong> ₹{{ number_format($totalPaid, 2) }} 
                        ({{ $allMilestones->where('status', 'paid')->count() }} of {{ $totalMilestones }} milestones)
                    </div>
                    <div style="display: table-cell; width: 50%; text-align: right;">
                        <strong>Remaining Balance:</strong> 
                        <span style="color: {{ $remaining > 0 ? '#dc3545' : '#198754' }}; font-size: 16px;">
                            ₹{{ number_format($remaining, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Next Milestone (if exists) -->
        @if($nextMilestone)
        <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #ffc107;">
            <h3 style="color: #856404; margin-bottom: 10px; font-size: 14px;">⏭️ Next Milestone</h3>
            <p><strong>{{ $nextMilestone->title }}</strong></p>
            <p>Amount: <strong>₹{{ number_format($nextMilestone->calculated_amount, 2) }}</strong></p>
            <p>Due Date: <strong>{{ $nextMilestone->due_date ? $nextMilestone->due_date->format('F d, Y') : 'Not specified' }}</strong></p>
            @if($nextMilestone->description)
            <p>{{ $nextMilestone->description }}</p>
            @endif
        </div>
        @else
        <div style="background: #d1e7dd; padding: 15px; border-radius: 5px; margin: 20px 0; border-left: 4px solid #198754;">
            <h3 style="color: #0f5132; margin-bottom: 10px; font-size: 14px;">🎉 All Milestones Completed!</h3>
            <p>Congratulations! All milestone payments have been successfully completed for this quotation.</p>
        </div>
        @endif
        
        <!-- Terms and Conditions -->
        <div class="terms">
            <h4>Terms & Conditions</h4>
            <ul>
                <li>This is a computer-generated invoice and does not require a physical signature.</li>
                <li>Payment is confirmed and non-refundable as per the cancellation policy.</li>
                <li>All amounts are in Indian Rupees (INR).</li>
                <li>For any queries regarding this invoice, please contact our support team.</li>
                <li>GST will be remitted to the government as per regulations.</li>
                <li>Please retain this invoice for your records.</li>
            </ul>
        </div>
        
        <!-- Footer -->
        <div class="footer">
            <p><strong>Thank you for your business!</strong></p>
            <p>OohApp - Your Out-of-Home Advertising Platform</p>
            <p>Email: support@oohapp.com | Phone: +91-XXXXXXXXXX</p>
            <p style="margin-top: 10px;">This is an electronically generated invoice and is valid without signature.</p>
            <p>Generated on: {{ now()->format('F d, Y h:i A') }}</p>
        </div>
    </div>
</body>
</html>
