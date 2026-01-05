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
        .button { display: inline-block; background-color: #1dbf73; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin: 10px 0; }
        .success-box { background-color: #f0f9ff; border-left: 4px solid #1dbf73; padding: 15px; margin: 20px 0; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .footer { text-align: center; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px; }
        .highlight { background-color: #fffacd; padding: 2px 5px; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🎉 Your Profile Has Been Approved!</h1>
        </div>

        <div class="content">
            <p>Hi {{ $user->name }},</p>

            <p>Great news! Your vendor profile on <strong>OOHApp</strong> has been <span class="highlight">officially approved</span> by our admin team.</p>

            <div class="success-box">
                <h3 style="margin-top: 0; color: #1dbf73;">Your Profile is Now Live ✓</h3>
                <p>Your hoardings, DOOH screens, and media placements are now <strong>visible to advertisers</strong> on the platform.</p>
            </div>

            <h3>Key Details:</h3>
            <table>
                <tr>
                    <th>Information</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td><strong>Company Name</strong></td>
                    <td>{{ $user->vendor_profile->company_name ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td><strong>Commission Rate</strong></td>
                    <td><span class="highlight">{{ $commissionPercentage }}%</span></td>
                </tr>
                <tr>
                    <td><strong>Status</strong></td>
                    <td><span style="color: #1dbf73; font-weight: bold;">Active</span></td>
                </tr>
                <tr>
                    <td><strong>Dashboard URL</strong></td>
                    <td><a href="https://staging.oohapp.io/vendor/dashboard">https://staging.oohapp.io/vendor/dashboard</a></td>
                </tr>
            </table>

            <h3>What's Next?</h3>
            <ul>
                <li>Login to your vendor dashboard to manage your media inventory</li>
                <li>Update media details, pricing, and availability as needed</li>
                <li>Receive booking requests from advertisers in real-time</li>
                <li>Track earnings and manage your commission payouts</li>
            </ul>

            <p style="text-align: center;">
                <a href="https://staging.oohapp.io/vendor/dashboard" class="button">Go to Dashboard</a>
            </p>

            <h3>Commission Structure:</h3>
            <p>Your approved commission rate is <span class="highlight">{{ $commissionPercentage }}%</span>. This is the commission you'll earn on successful bookings made through the OOHApp platform.</p>

            <p><strong>Need Help?</strong><br>
            If you have any questions or need support, our team is here to help. Reach out to us at <strong>support@oohapp.com</strong> or use the support chat in your dashboard.</p>

            <p style="margin-top: 30px;">Happy booking!<br>
            <strong>Team OOHApp</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2026 OOHApp. All rights reserved.</p>
            <p>This is an automated email notification. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
