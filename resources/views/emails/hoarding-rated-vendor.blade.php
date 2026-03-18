<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Your Hoarding Has Been Rated</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        @include('emails.partials.header')

        <!-- Title -->
        <tr>
            <td align="center" style="padding:10px 40px 0 40px;">
                <h2 style="margin:0; color:#222; font-weight:600;">New Rating Received ⭐</h2>
                <p style="margin-top:8px; color:#666; font-size:14px;">
                    Hello {{ $notifiable->name ?? 'Vendor' }}, your hoarding has been rated!
                </p>
            </td>
        </tr>

        <!-- Hoarding + Rating Details -->
        <tr>
        <td style="padding:20px 40px 10px 40px;">
            <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
                <tr style="background:#f3f4f6;">
                    <th align="left" style="padding:10px; font-size:13px;">Field</th>
                    <th align="left" style="padding:10px; font-size:13px;">Details</th>
                </tr>
                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Hoarding</strong></td>
                    <td style="padding:10px; font-size:13px;">{{ $hoarding->title ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Rating</strong></td>
                    <td style="padding:10px; font-size:13px;">{{ $rating }} ⭐</td>
                </tr>
                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Review</strong></td>
                    <td style="padding:10px; font-size:13px;">{{ $review ?: 'No review provided' }}</td>
                </tr>
                <tr>
                    <td style="padding:10px; font-size:13px;"><strong>Customer</strong></td>
                    <td style="padding:10px; font-size:13px;">{{ $customer->name ?? 'N/A' }}</td>
                </tr>
            </table>
        </td>
        </tr>

        <!-- Button -->
        <tr>
        <td align="center" style="padding:25px 40px;">
            <a href="{{ $actionUrl }}"
               style="background:#16a34a;
                      color:#ffffff;
                      padding:12px 24px;
                      font-size:14px;
                      text-decoration:none;
                      border-radius:6px;
                      display:inline-block;">
                View Hoarding
            </a>
        </td>
        </tr>

        <!-- Message -->
        <tr>
        <td style="padding:0 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
            <p>Thank you for being a part of <strong>OOHApp</strong>!</p>
            <p>If you have any questions, contact us at <strong>support@oohapp.com</strong>.</p>
            <p>Cheers,<br><strong>Team OOHAPP</strong></p>
        </td>
        </tr>

        @include('emails.partials.footer')

    </table>
</td>
</tr>
</table>

</body>
</html>