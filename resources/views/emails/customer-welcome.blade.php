<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Welcome to OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

    <!-- Main Container -->
    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        @include('emails.partials.header')

        <!-- Title -->
        <tr>
            <td align="center" style="padding:10px 40px 0 40px;">
                <h2 style="margin:0; color:#222; font-weight:600;">Welcome to OOHAPP 👋</h2>
                <p style="margin-top:8px; color:#666; font-size:14px;">
                    Glad to have you with us, {{ $user->name }}!
                </p>
            </td>
        </tr>

        <!-- Intro -->
        <tr>
            <td style="padding:20px 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
                <p>
                    If you're looking to put your brand out there — hoardings, DOOH screens, prime locations —
                    you're in the right place. OOHAPP makes outdoor advertising straightforward,
                    without the usual back-and-forth and confusion.
                </p>
            </td>
        </tr>

        <!-- LOGIN DETAILS TABLE -->
        <tr>
        <td style="padding:0 40px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
                <tr style="background:#f3f4f6;">
                    <th align="left" style="padding:10px; font-size:13px;">Field</th>
                    <th align="left" style="padding:10px; font-size:13px;">Details</th>
                </tr>

                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Email / User ID</strong></td>
                    <td style="padding:10px; font-size:13px;">{{ $user->email }}</td>
                </tr>

                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Login URL</strong></td>
                    <td style="padding:10px; font-size:13px;">
                        <a href="https://staging.oohapp.io/" target="_blank">
                            https://staging.oohapp.io/
                        </a>
                    </td>
                </tr>
            </table>
        </td>
        </tr>

        <!-- BUTTON -->
        <tr>
        <td align="center" style="padding:25px 40px;">
            <a href="https://staging.oohapp.io/"
               style="background:#16a34a;
                      color:#ffffff;
                      padding:12px 24px;
                      font-size:14px;
                      text-decoration:none;
                      border-radius:6px;
                      display:inline-block;">
                Login to Your Dashboard
            </a>
        </td>
        </tr>

        <!-- Features -->
        <tr>
        <td style="padding:0 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
            <p><strong>Here's what you can do right away:</strong></p>
            <ul style="padding-left:18px; margin:8px 0;">
                <li>Browse verified hoardings and DOOH screens</li>
                <li>Check locations, sizes, pricing and availability</li>
                <li>Shortlist options as per your budget</li>
                <li>Book campaigns faster and track everything in one place</li>
            </ul>

            <p>Your dashboard is already live — feel free to explore and start planning your next campaign.</p>

            <p><strong>Security tip:</strong> Keep your password confidential. We never ask for your password via email.</p>

            <p>If you need help, contact us at <strong>support@oohapp.com</strong>.</p>

            <p>Cheers,<br><strong>Team OOHAPP</strong></p>
        </td>
        </tr>

        <!-- CUSTOMER DISCLAIMER (FOOTER KE JUST UPAR) -->
        <tr>
        <td style="padding:10px 40px 0 40px;">
            <div style="
                margin-top: 25px;
                padding-top: 12px;
                border-top: 1px dashed #ddd;
                font-size: 9px;
                color: #777;
                line-height: 1.5;
            ">
                <strong style="color:#555;">Disclaimer:</strong>
                OOHAPP helps you discover and connect with verified advertising space owners.
                All pricing, availability, and campaign details are shared directly by the vendor.
                Any discussions or confirmations take place between you and the selected vendor.
                OOHAPP remains a facilitating platform and does not take part in deal discussions or execution.
            </div>
        </td>
        </tr>

        @include('emails.partials.footer')

    </table>
</td>
</tr>
</table>

</body>
</html>
