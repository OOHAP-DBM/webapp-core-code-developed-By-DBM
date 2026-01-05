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
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        table, th, td { border: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f0f0f0; }
        .footer { text-align: center; color: #666; font-size: 12px; padding-top: 20px; border-top: 1px solid #ddd; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Welcome to OOHApp — Let's Get Your Media Booked 👋</h1>
        </div>

        <div class="content">
            <p>Hi {{ $user->name }},</p>

            <p>Welcome to <strong>OOHApp</strong>! Great to have you on board.</p>

            <p>OOHApp is built to help media owners like you get better visibility and more bookings — without the usual calls, follow-ups, and paperwork.</p>

            <h3>Your Login Details:</h3>
            <table>
                <tr>
                    <th>Field</th>
                    <th>Details</th>
                </tr>
                <tr>
                    <td><strong>Email / User ID</strong></td>
                    <td>{{ $user->email }}</td>
                </tr>
                <tr>
                    <td><strong>Password</strong></td>
                    <td>[Your Password]</td>
                </tr>
                <tr>
                    <td><strong>Login URL</strong></td>
                    <td><a href="https://staging.oohapp.io/" target="_blank">https://staging.oohapp.io/</a></td>
                </tr>
            </table>

            <p style="text-align: center;">
                <a href="https://staging.oohapp.io/" class="button">Login Now</a>
            </p>

            <h3>Here's what you need to do next:</h3>
            <ul>
                <li>Add your hoardings or DOOH screens</li>
                <li>Set pricing and availability</li>
                <li>Start receiving booking requests from advertisers</li>
            </ul>

            <p>Once your profile is approved, your media will be visible to brands actively looking to book.</p>

            <p><strong>Quick tip:</strong> Clear photos and accurate details help you get approved faster and attract better enquiries.</p>

            <p><strong>Security reminder:</strong> Keep your password safe. We never ask for your password via email.</p>

            <p>If you need help at any stage, just drop us a message — we're here to support you at <strong>support@oohapp.com</strong>.</p>

            <p>Looking forward to growing together.</p>

            <p>Best,<br><strong>Team OOHApp</strong></p>
        </div>

        <div class="footer">
            <p>&copy; 2026 OOHApp. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
