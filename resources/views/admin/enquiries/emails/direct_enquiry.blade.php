<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>New Direct Enquiry Received</title>
</head>
<body style="margin:0; padding:0; background-color:#f5f7fb; font-family:Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0">
    <tr>
        <td align="center" style="padding:30px 15px;">
            <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden;">

                <!-- Header -->
                <tr>
                    <td style="background:#0f172a; padding:20px; text-align:center; color:#ffffff;">
                        <h2 style="margin:0;">OOHAPP</h2>
                        <p style="margin:5px 0 0;">New Direct Enquiry Notification</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:24px; color:#333333;">
                        <p>
                            Hello Admin,
                        </p>

                        <p>
                            A new <strong>Direct Enquiry</strong> has been submitted.  
                            Below are the details:
                        </p>

                        <table width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0; font-size:14px;">
                            <tr>
                                <td width="40%" style="padding:6px 0;"><strong>Name</strong></td>
                                <td>{{ $enquiry->name }}</td>
                            </tr>

                            <tr>
                                <td style="padding:6px 0;"><strong>Email</strong></td>
                                <td>{{ $enquiry->email }}</td>
                            </tr>

                            <tr>
                                <td style="padding:6px 0;"><strong>Phone</strong></td>
                                <td>{{ $enquiry->phone }}</td>
                            </tr>

                            <tr>
                                <td style="padding:6px 0;"><strong>City</strong></td>
                                <td>{{ $enquiry->location_city ?? '-' }}</td>
                            </tr>

                            <tr>
                                <td style="padding:6px 0;"><strong>Hoarding Type</strong></td>
                                <td>{{ ucfirst($enquiry->hoarding_type) }}</td>
                            </tr>

                            <tr>
                                <td style="padding:6px 0;"><strong>Preferred Locations</strong></td>
                                <td>
                                    {{ 
                                        !empty($enquiry->preferred_locations)
                                            ? implode(', ', (array) $enquiry->preferred_locations)
                                            : 'Location needs to be discussed'
                                    }}
                                </td>
                            </tr>

                            @if($enquiry->remarks)
                            <tr>
                                <td style="padding:6px 0;"><strong>Remarks</strong></td>
                                <td>{{ $enquiry->remarks }}</td>
                            </tr>
                            @endif
                        </table>

                        <p style="margin-top:20px;">
                            Please follow up with the user at the earliest.
                        </p>

                        <p style="margin-top:24px;">
                            Regards,<br>
                            <strong>OOHAPP System</strong>
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
