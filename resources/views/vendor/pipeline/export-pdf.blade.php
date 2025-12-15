<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Booking Pipeline Report</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
        }
        h1 {
            color: #0d6efd;
            font-size: 24px;
            margin-bottom: 10px;
        }
        .header {
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #0d6efd;
        }
        .summary {
            background: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
        }
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 15px;
        }
        .summary-item {
            text-align: center;
        }
        .summary-label {
            color: #666;
            font-size: 10px;
            text-transform: uppercase;
        }
        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        .stage-section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        .stage-header {
            background: #0d6efd;
            color: white;
            padding: 8px 12px;
            font-weight: bold;
            margin-bottom: 10px;
            border-radius: 3px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th {
            background: #e9ecef;
            padding: 8px;
            text-align: left;
            font-weight: bold;
            border-bottom: 2px solid #dee2e6;
        }
        td {
            padding: 6px 8px;
            border-bottom: 1px solid #dee2e6;
        }
        tr:hover {
            background: #f8f9fa;
        }
        .footer {
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
            font-size: 10px;
            color: #666;
            padding: 10px 0;
            border-top: 1px solid #dee2e6;
        }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
        }
        .badge-success { background: #d1e7dd; color: #0a3622; }
        .badge-warning { background: #fff3cd; color: #664d03; }
        .badge-danger { background: #f8d7da; color: #58151c; }
        .badge-info { background: #cff4fc; color: #055160; }
    </style>
</head>
<body>
    <div class="header">
        <h1>Booking Pipeline Report</h1>
        <p><strong>Generated:</strong> {{ $generated_at }}</p>
    </div>

    <div class="summary">
        <div class="summary-grid">
            <div class="summary-item">
                <div class="summary-label">Total Bookings</div>
                <div class="summary-value">{{ $summary['total_bookings'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Total Value</div>
                <div class="summary-value">{{ $summary['total_value_formatted'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Active Bookings</div>
                <div class="summary-value">{{ $summary['active_bookings'] }}</div>
            </div>
            <div class="summary-item">
                <div class="summary-label">Conversion Rate</div>
                <div class="summary-value">{{ $summary['conversion_rate'] }}%</div>
            </div>
        </div>
    </div>

    @foreach($stages as $stageKey => $stage)
        @if(count($stage['bookings']) > 0)
            <div class="stage-section">
                <div class="stage-header">
                    {{ $stage['label'] }} ({{ $stage['count'] }} bookings - ₹{{ number_format($stage['total_value']) }})
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>Booking ID</th>
                            <th>Customer</th>
                            <th>Hoarding</th>
                            <th>Location</th>
                            <th>Start Date</th>
                            <th>Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($stage['bookings'] as $booking)
                            <tr>
                                <td>{{ $booking['booking_id'] }}</td>
                                <td>{{ $booking['customer_name'] }}</td>
                                <td>{{ Str::limit($booking['hoarding_title'], 30) }}</td>
                                <td>{{ $booking['hoarding_city'] }}</td>
                                <td>{{ $booking['start_date'] ?? 'TBD' }}</td>
                                <td>{{ $booking['total_amount_formatted'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    @endforeach

    <div class="footer">
        <p>© {{ date('Y') }} OOHAPP - Booking Pipeline Report - Page <span class="pagenum"></span></p>
    </div>
</body>
</html>
