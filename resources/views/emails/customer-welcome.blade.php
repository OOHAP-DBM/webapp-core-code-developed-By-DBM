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
            <h1>Welcome to OOHApp 👋</h1>
        </div>

        <div class="content">
            <p>Hi {{ $user->name }},</p>

            <p>Welcome to OOHApp! Glad to have you with us.</p>

            <p>If you're looking to put your brand out there — hoardings, DOOH screens, prime locations — you're in the right place. OOHApp makes outdoor advertising straightforward, without the usual back-and-forth and confusion.</p>

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
                    <td><strong>Login URL</strong></td>
                    <td><a href="https://staging.oohapp.io/" target="_blank">https://staging.oohapp.io/</a></td>
                </tr>
            </table>

            <p style="text-align: center;">
                <a href="https://staging.oohapp.io/" class="button">Login to Your Dashboard</a>
            </p>

            <h3>Here's what you can do right away:</h3>
            <ul>
                <li>Browse real, verified hoardings and DOOH screens</li>
                <li>Check locations, sizes, pricing, and availability</li>
                <li>Shortlist options that fit your budget</li>
                <li>Book campaigns faster and track everything in one place</li>
            </ul>

            <p>Your dashboard is already live — feel free to explore and start planning your next campaign.</p>

            <p><strong>Security tip:</strong> Keep your password confidential. We never ask for your password via email.</p>

            <p>If you ever feel stuck or need suggestions, just reach out. We're happy to help at <strong>support@oohapp.com</strong>.</p>

            <p>Let's make sure your brand gets noticed.</p>

            <p>Cheers,<br>Team OOHApp</p>
        </div>

        <div class="footer">
            <p>&copy; 2026 OOHApp. All rights reserved.</p>
            <p>This is an automated email. Please do not reply directly.</p>
        </div>
    </div>
</body>
</html>
