<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #1dbf73; color: white; padding: 20px; border-radius: 5px; text-align: center; }
        .content { padding: 20px 0; }
        .section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #1dbf73; border-radius: 3px; }
        .section h3 { margin-top: 0; color: #1dbf73; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .item-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        .item-table tr { border-bottom: 1px solid #ddd; }
        .item-table td { padding: 12px; vertical-align: top; }
        .item-table tr:last-child { border-bottom: none; }
        .hoarding-name { font-weight: bold; color: #1dbf73; font-size: 16px; }
        .amount { font-weight: bold; color: #333; font-size: 18px; }
        .total-section { background-color: #f0f0f0; padding: 15px; border-radius: 5px; text-align: right; margin: 20px 0; }
        .total-amount { font-size: 24px; font-weight: bold; color: #1dbf73; }
        .button { display: inline-block; background-color: #1dbf73; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px; }
        .status-badge { display: inline-block; background-color: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Enquiry Confirmation ✅</h1>
        </div>

        <div class="content">
            <p>Hi {{ $customer->name }},</p>

            <p>Thank you for submitting your enquiry! We've received your request and our team is reviewing your campaign details.</p>

            <div class="section">
                <h3>📋 Enquiry Details</h3>
                <table>
                    <tr>
                        <td><strong>Enquiry ID</strong></td>
                        <td>#{{ $enquiry->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Status</strong></td>
                        <td><span class="status-badge">{{ ucfirst($enquiry->status) }}</span></td>
                    </tr>
                    <tr>
                        <td><strong>Campaign Start Date</strong></td>
                        <td>
                            @php
                                $startDate = $enquiry->items->first()?->preferred_start_date;
                                $formattedDate = is_string($startDate) ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : ($startDate?->format('M d, Y') ?? 'N/A');
                            @endphp
                            {{ $formattedDate }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Number of Hoardings</strong></td>
                        <td>{{ $enquiry->items->count() }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>🎯 Your Campaign Hoardings</h3>
                @foreach($enquiry->items as $item)
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                    <div class="hoarding-name">
                        <a href="{{ route('hoardings.show', $item->hoarding_id) }}" style="color: #1dbf73; text-decoration: underline;">
                            {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                        </a>
                    </div>
                    <p style="margin: 8px 0; color: #666;">
                        📍 {{ $item->hoarding->location ?? 'Location not specified' }}
                    </p>
                    <table style="margin-top: 10px;">
                        <tr>
                            <td><strong>Type</strong></td>
                            <td>{{ ucfirst($item->hoarding_type) }}</td>
                        </tr>
                        @if($item->package_type === 'package')
                        <tr>
                            <td><strong>Package Selected</strong></td>
                            <td>{{ $item->package_label ?? 'Standard Package' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Package Duration</strong></td>
                            <td>
                                @php
                                    $start = is_string($item->preferred_start_date) ? \Carbon\Carbon::parse($item->preferred_start_date) : $item->preferred_start_date;
                                    $end = is_string($item->preferred_end_date) ? \Carbon\Carbon::parse($item->preferred_end_date) : $item->preferred_end_date;
                                    $months = $start->diffInMonths($end);
                                @endphp
                                {{ $months }} month{{ $months !== 1 ? 's' : '' }}
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td><strong>Duration</strong></td>
                            <td>{{ $item->expected_duration ?? 'Custom Duration' }}</td>
                        </tr>
                        @endif
                    </table>
                    @if($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs']))
                    <div style="margin-top: 10px; padding: 10px; background-color: #e8f5e9; border-radius: 3px;">
                        <strong>📺 DOOH Specifications:</strong>
                        <ul style="margin: 8px 0 0 20px; padding: 0;">
                            <li>Video Duration: {{ $item->meta['dooh_specs']['video_duration'] ?? 15 }} seconds</li>
                            <li>Slots per Day: {{ $item->meta['dooh_specs']['slots_per_day'] ?? 120 }}</li>
                            <li>Total Days: {{ $item->meta['dooh_specs']['total_days'] ?? 0 }}</li>
                        </ul>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="section">
                <h3>⏭️ What Happens Next?</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Our team will review your enquiry and validate the availability</li>
                    <li>Vendor will confirm their acceptance of your campaign</li>
                    <li>We'll send you a quotation once everything is verified</li>
                    <li>You'll receive email updates at every step</li>
                </ul>
            </div>

            <p style="text-align: center; margin-top: 25px;">
                <a href="https://staging.oohapp.io/customer/enquiries" class="button">View Your Enquiries</a>
            </p>

            <div class="section" style="border-left-color: #17a2b8; background-color: #e7f3ff;">
                <strong>📧 Questions?</strong>
                <p style="margin: 8px 0 0 0;">Reach out to us at <strong>support@oohapp.com</strong> or check your dashboard for updates. We're here to help!</p>
            </div>

            <p>Thanks for choosing OOHApp.</p>
            <p>Best,<br><strong>Team OOHApp</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2026 OOHApp. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
