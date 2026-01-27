<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Direct Enquiry Confirmation</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:30px 15px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#1B84FF; padding:20px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0;">OOHAPP</h2>
                        <p style="margin:5px 0 0;">Direct Enquiry Confirmation</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:24px; color:#333333;">
                        <p>Hi <strong>{{ $enquiry->name }}</strong>,</p>

                        <p>
                            Thank you for contacting <strong>OOHAPP</strong>.
                            We have successfully received your enquiry.
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; font-size:14px;">
                            <tr>
                                <td width="40%" style="padding:6px 0;"><strong>City</strong></td>
                                <td>{{ $enquiry->location_city }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;"><strong>Hoarding Type</strong></td>
                                <td>{{ ucfirst($enquiry->hoarding_type) }}</td>
                            </tr>
                            <tr>
                                <td style="padding:6px 0;"><strong>Location</strong></td>
                                <td>{{ $enquiry->hoarding_location }}</td>
                            </tr>
                            @if($enquiry->remarks)
                            <tr>
                                <td style="padding:6px 0;"><strong>Remarks</strong></td>
                                <td>{{ $enquiry->remarks }}</td>
                            </tr>
                            @endif
                        </table>

                        <p>
                            Our team will review your request and get back to you shortly.
                        </p>

                        <p style="margin-top:24px;">
                            Regards,<br>
                            <strong>OOHAPP Team</strong>
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f1f5f9; padding:16px; text-align:center; font-size:12px; color:#666;">
                        © {{ date('Y') }} OOHAPP. All rights reserved.
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>

</body>
</html>
