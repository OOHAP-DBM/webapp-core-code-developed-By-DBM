<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Multi-Vendor Reminder — Admin Action Required</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

    <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

        @include('emails.partials.header')

        {{-- TITLE --}}
        <tr>
            <td align="center" style="padding:10px 40px 0 40px;">
                <h2 style="margin:0; color:#222; font-weight:600;">🔔 Multi-Vendor Reminder</h2>
                <p style="margin-top:8px; color:#666; font-size:14px;">
                    Enquiry <strong>{{ $enquiry_id }}</strong> &nbsp;•&nbsp; {{ $vendor_count }} Vendors Involved
                </p>
            </td>
        </tr>

        {{-- INTRO --}}
        <tr>
            <td style="padding:20px 40px 10px 40px; font-size:14px; color:#444; line-height:22px;">
                <p>Dear Admin,</p>
                <p style="margin-top:8px;">
                    <strong>{{ $customer_name }}</strong> has sent a reminder for enquiry
                    <strong>{{ $enquiry_id }}</strong> which involves <strong>{{ $vendor_count }} vendors</strong>.
                    Please coordinate and ensure all vendors respond promptly.
                </p>
            </td>
        </tr>

        {{-- CUSTOMER INFO --}}
        <tr>
            <td style="padding:0 40px;">
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
                    <tr style="background:#f3f4f6;">
                        <th align="left" style="padding:10px; font-size:13px;">Field</th>
                        <th align="left" style="padding:10px; font-size:13px;">Details</th>
                    </tr>
                    <tr>
                        <td style="padding:10px; font-size:13px;"><strong>Customer Name</strong></td>
                        <td style="padding:10px; font-size:13px;">{{ $customer_name }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px; font-size:13px;"><strong>Phone</strong></td>
                        <td style="padding:10px; font-size:13px;">{{ $customer_phone }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px; font-size:13px;"><strong>Email</strong></td>
                        <td style="padding:10px; font-size:13px;">{{ $customer_email }}</td>
                    </tr>
                    <tr>
                        <td style="padding:10px; font-size:13px;"><strong>Enquiry Date</strong></td>
                        <td style="padding:10px; font-size:13px;">{{ $enquiry_date }}</td>
                    </tr>
                    @if($customer_note)
                    <tr>
                        <td style="padding:10px; font-size:13px;"><strong>Requirement</strong></td>
                        <td style="padding:10px; font-size:13px; color:#b45309;">{{ $customer_note }}</td>
                    </tr>
                    @endif
                </table>
            </td>
        </tr>

        {{-- VENDORS TABLE --}}
        <tr>
            <td style="padding:20px 40px 0 40px;">
                <p style="font-size:13px; font-weight:700; margin:0 0 8px 0; color:#111;">
                    Vendors Involved ({{ $vendor_count }})
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
                    <tr style="background:#f3f4f6;">
                        <th align="left" style="padding:10px; font-size:12px;">#</th>
                        <th align="left" style="padding:10px; font-size:12px;">Vendor Name</th>
                        <th align="left" style="padding:10px; font-size:12px;">Email</th>
                        <th align="left" style="padding:10px; font-size:12px;">Phone</th>
                    </tr>
                    @foreach($vendors as $i => $v)
                    <tr style="{{ $loop->even ? 'background:#f9fafb;' : '' }}">
                        <td style="padding:10px; font-size:13px; color:#64748b;">{{ $i + 1 }}</td>
                        <td style="padding:10px; font-size:13px; font-weight:600;">{{ $v['name'] }}</td>
                        <td style="padding:10px; font-size:13px;">{{ $v['email'] }}</td>
                        <td style="padding:10px; font-size:13px;">{{ $v['phone'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>

        {{-- HOARDINGS TABLE --}}
        <tr>
            <td style="padding:20px 40px 0 40px;">
                <p style="font-size:13px; font-weight:700; margin:0 0 8px 0; color:#111;">
                    Hoardings in Enquiry ({{ count($hoardings) }})
                </p>
                <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
                    <tr style="background:#f3f4f6;">
                        <th align="left" style="padding:10px; font-size:12px;">#</th>
                        <th align="left" style="padding:10px; font-size:12px;">Hoarding</th>
                        <th align="left" style="padding:10px; font-size:12px;">Vendor</th>
                        <th align="left" style="padding:10px; font-size:12px;">Location</th>
                        <th align="left" style="padding:10px; font-size:12px;">Rental</th>
                    </tr>
                    @foreach($hoardings as $i => $h)
                    <tr style="{{ $loop->even ? 'background:#f9fafb;' : '' }}">
                        <td style="padding:10px; font-size:13px; color:#64748b;">{{ $i + 1 }}</td>
                        <td style="padding:10px; font-size:13px;">
                            <strong>{{ $h['title'] }}</strong><br>
                            <span style="font-size:11px; background:{{ strtolower($h['type']) === 'dooh' ? '#dbeafe' : '#dcfce7' }}; color:{{ strtolower($h['type']) === 'dooh' ? '#1e40af' : '#166534' }}; padding:2px 8px; border-radius:20px;">
                                {{ $h['type'] }}
                            </span>
                        </td>
                        <td style="padding:10px; font-size:13px; color:#475569;">{{ $h['vendor'] }}</td>
                        <td style="padding:10px; font-size:13px; color:#475569;">{{ $h['location'] }}</td>
                        <td style="padding:10px; font-size:13px; font-weight:700;">₹{{ $h['price'] }}</td>
                    </tr>
                    @endforeach
                </table>
            </td>
        </tr>

        {{-- CTA BUTTON --}}
        <tr>
            <td align="center" style="padding:25px 40px;">
                <a href=""
                   style="background:#16a34a; color:#ffffff; padding:12px 28px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block; font-weight:600;">
                    View Enquiry
                </a>
            </td>
        </tr>

        {{-- NOTE --}}
        <tr>
            <td style="padding:0 40px 10px 40px; font-size:13px; color:#444; line-height:22px;">
                <p>Please ensure all vendors are contacted and respond to the customer's enquiry.</p>
                <p style="margin-top:12px;">Regards,<br><strong>Team OOHAPP</strong></p>
            </td>
        </tr>

        {{-- DISCLAIMER --}}
        <tr>
            <td style="padding:10px 40px 7px 40px;">
                <div style="margin-top:20px; padding-top:12px; border-top:1px dashed #ddd; font-size:9px; color:#777; line-height:1.5;">
                    <strong style="color:#555;">Disclaimer:</strong>
                    This is an automated multi-vendor reminder generated by OOHAPP.
                    The customer has multiple vendors in this enquiry and requires coordinated follow-up.
                    Please take appropriate action from your admin panel.
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