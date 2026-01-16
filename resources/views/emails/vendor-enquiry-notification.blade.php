<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
        .header { background-color: #FF6B35; color: white; padding: 20px; border-radius: 5px; text-align: center; }
        .content { padding: 20px 0; }
        .section { margin: 20px 0; padding: 15px; background-color: #f9f9f9; border-left: 4px solid #FF6B35; border-radius: 3px; }
        .section h3 { margin-top: 0; color: #FF6B35; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; font-weight: bold; }
        .customer-info { background-color: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 4px solid #ffc107; }
        .hoarding-name { font-weight: bold; color: #FF6B35; font-size: 16px; }
        .package-badge { display: inline-block; background-color: #FF6B35; color: white; padding: 5px 10px; border-radius: 3px; font-size: 12px; font-weight: bold; }
        .amount { font-weight: bold; color: #333; font-size: 18px; }
        .total-section { background-color: #f0f0f0; padding: 15px; border-radius: 5px; text-align: right; margin: 20px 0; }
        .total-amount { font-size: 24px; font-weight: bold; color: #FF6B35; }
        .button { display: inline-block; background-color: #FF6B35; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .footer { text-align: center; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px; }
        .alert { background-color: #e7f3ff; padding: 15px; border-left: 4px solid #17a2b8; border-radius: 3px; margin: 15px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 New Enquiry Received!</h1>
        </div>

        <div class="content">
            <p>Hi {{ $vendor->name }},</p>

            <p>Great news! You have received a new enquiry from an advertiser. Here are the details:</p>

            <div class="customer-info">
                <strong>👤 Customer Information</strong>
                <table style="margin-top: 10px; border: none;">
                    <tr style="border: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Name</strong></td>
                        <td style="border: none; padding: 5px 0;">{{ $enquiry->customer->name ?? 'Not provided' }}</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Email</strong></td>
                        <td style="border: none; padding: 5px 0;">{{ $enquiry->customer->email ?? 'Not provided' }}</td>
                    </tr>
                    <tr style="border: none;">
                        <td style="border: none; padding: 5px 0;"><strong>Contact</strong></td>
                        <td style="border: none; padding: 5px 0;">{{ $enquiry->contact_number ?? 'Not provided' }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>📋 Enquiry Details</h3>
                <table>
                    <tr>
                        <td><strong>Enquiry ID</strong></td>
                        <td>#{{ $enquiry->id }}</td>
                    </tr>
                    <tr>
                        <td><strong>Campaign Start Date</strong></td>
                        <td>
                            @php
                                $startDate = $items->first()?->preferred_start_date;
                                $formattedDate = is_string($startDate) ? \Carbon\Carbon::parse($startDate)->format('M d, Y') : ($startDate?->format('M d, Y') ?? 'N/A');
                            @endphp
                            {{ $formattedDate }}
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Number of Your Hoardings</strong></td>
                        <td>{{ $items->count() }}</td>
                    </tr>
                </table>
            </div>

            <div class="section">
                <h3>🏢 Your Hoardings in This Enquiry</h3>
                @foreach($items as $item)
                <div style="margin-bottom: 20px; padding-bottom: 15px; border-bottom: 1px solid #ddd;">
                    <div class="hoarding-name">
                        {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
                    </div>
                    <p style="margin: 8px 0; color: #666;">
                        📍 {{ $item->hoarding->location ?? 'Location: N/A' }}
                    </p>
                    <table style="margin-top: 10px;">
                        <tr>
                            <td><strong>Type</strong></td>
                            <td>{{ ucfirst($item->hoarding_type) }}</td>
                        </tr>
                        @if($item->package_type === 'package')
                        <tr>
                            <td><strong>Package</strong></td>
                            <td><span class="package-badge">{{ $item->package_label ?? 'Standard Package' }}</span></td>
                        </tr>
                        <tr>
                            <td><strong>Duration</strong></td>
                            <td>
                                @php
                                    $start = is_string($item->preferred_start_date) ? \Carbon\Carbon::parse($item->preferred_start_date) : $item->preferred_start_date;
                                    $end = is_string($item->preferred_end_date) ? \Carbon\Carbon::parse($item->preferred_end_date) : $item->preferred_end_date;
                                    $months = $start->diffInMonths($end);
                                @endphp
                                {{ $months }} month{{ $months !== 1 ? 's' : '' }} ({{ $start->format('M d') }} - {{ $end->format('M d, Y') }})
                            </td>
                        </tr>
                        @else
                        <tr>
                            <td><strong>Duration</strong></td>
                            <td>{{ $item->expected_duration ?? 'Custom Period' }}</td>
                        </tr>
                        @endif
                    </table>
                    @if($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs']))
                    <div style="margin-top: 10px; padding: 10px; background-color: #e8f5e9; border-radius: 3px;">
                        <strong>📺 DOOH Specifications Requested:</strong>
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

            @if($enquiry->customer_note)
            <div class="section" style="border-left-color: #17a2b8;">
                <h3 style="color: #17a2b8;">💬 Customer Message</h3>
                <p>{{ $enquiry->customer_note }}</p>
            </div>
            @endif

            <div class="alert">
                <strong>⚡ Action Required</strong>
                <p style="margin: 8px 0 0 0;">Please review this enquiry and confirm your availability. Once confirmed, a quotation will be generated.</p>
            </div>

            <p style="text-align: center; margin-top: 25px;">
                <a href="https://staging.oohapp.io/vendor/enquiries" class="button">Review Enquiry & Respond</a>
            </p>

            <div class="section">
                <h3>📌 Quick Tips</h3>
                <ul style="margin: 10px 0; padding-left: 20px;">
                    <li>Confirm quickly to show the customer you're interested</li>
                    <li>Add any additional details or special offers if applicable</li>
                    <li>Check hoarding availability before confirming</li>
                    <li>Your response time matters - faster responses get priority</li>
                </ul>
            </div>

            <p>Looking forward to growing your business through OOHApp.</p>
            <p>Best,<br><strong>Team OOHApp</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2026 OOHApp. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
