<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Enquiry Confirmation - OOHAPP</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

<!-- TITLE -->
<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#16a34a; font-weight:600;">
        Enquiry Confirmation ✅
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $customer->name }}, we’ve received your campaign request.
    </p>
</td>
</tr>

<!-- INTRO -->
<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>
        Thank you for submitting your enquiry on OOHAPP.
        Our team is reviewing your campaign and verifying availability with vendors.
    </p>
</td>
</tr>

<!-- ENQUIRY DETAILS -->
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
<tr style="background:#f3f4f6;">
    <th align="left" style="padding:10px; font-size:13px;">Information</th>
    <th align="left" style="padding:10px; font-size:13px;">Details</th>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Enquiry ID</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->formatted_id }}</td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
    <td style="padding:10px; font-size:13px;">
        <span style="background:#fef3c7; padding:4px 8px; font-weight:bold;">
            {{ ucfirst($enquiry->status) }}
        </span>
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Campaign Start Date</strong></td>
    <td style="padding:10px; font-size:13px;">
        @php
            $startDate = $enquiry->items->first()?->preferred_start_date;
            $formattedDate = $startDate
                ? \Carbon\Carbon::parse($startDate)->format('d M Y')
                : 'N/A';
        @endphp
        {{ $formattedDate }}
    </td>
</tr>

<tr>
    <td style="padding:10px; font-size:13px;"><strong>Number of Hoardings</strong></td>
    <td style="padding:10px; font-size:13px;">{{ $enquiry->items->count() }}</td>
</tr>
</table>
</td>
</tr>

<!-- Vendor Details Section -->
@php
    // Collect all vendors from enquiry items
    $vendors = collect($enquiry->items)->map(function($item) {
        return optional($item->hoarding)->vendor;
    })->filter()->unique('id')->values();
@endphp

<!-- VENDOR DETAILS -->
@if($vendors->count())
<tr>
    <td style="padding:20px 40px 0 40px;">
        <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
            
            <tr style="background:#f3f4f6;">
                <th align="left" style="padding:10px; font-size:13px;" colspan="2">
                    Vendor Details
                </th>
            </tr>

                @foreach($vendors as $vendor)
            <tr>
                <td colspan="2" style="padding:0;">
                    
                    <!-- Each Vendor Card -->
                    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border-top:1px solid #e5e7eb;">
                        
                        <tr style="background:#ecfdf5;">
                            <td colspan="2" style="padding:10px; font-size:14px; color:#065f46;">
                                <strong>{{ $vendor->name ?? '-' }}</strong>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; font-size:13px; width:35%;"><strong>Business Name</strong></td>
                            <td style="padding:8px 10px; font-size:13px;">
                                {{ $vendor->vendorProfile->company_name ?? $vendor->company_name ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; font-size:13px;"><strong>GSTIN</strong></td>
                            <td style="padding:8px 10px; font-size:13px;">
                                {{ $vendor->vendorProfile->gstin ?? $vendor->gstin ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; font-size:13px;"><strong>Mobile</strong></td>
                            <td style="padding:8px 10px; font-size:13px;">
                                {{ $vendor->phone ?? '-' }}
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; font-size:13px;"><strong>Email</strong></td>
                            <td style="padding:8px 10px; font-size:13px;">
                                <a href="mailto:{{ $vendor->email }}" style="color:#16a34a; text-decoration:none;">
                                    {{ $vendor->email ?? '-' }}
                                </a>
                            </td>
                        </tr>

                        <tr>
                            <td style="padding:8px 10px; font-size:13px;"><strong>Address</strong></td>
                            <td style="padding:8px 10px; font-size:13px;">
                                {{ $vendor->vendorProfile->registered_address ?? $vendor->address ?? '-' }}
                            </td>
                        </tr>

                    </table>

                </td>
            </tr>
            @endforeach

        </table>
    </td>
    </tr>
    @endif


<!-- HOARDING LIST -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="font-size:14px; color:#444;"><strong>Your Selected Hoardings</strong></p>
</td>
</tr>

@foreach($enquiry->items as $item)
<tr>
<td style="padding:10px 40px;">
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb; margin-bottom:12px;">

<tr style="background:#ecfdf5;">
<td style="padding:12px; font-size:14px; color:#065f46;">
    <strong>
        {{ $item->hoarding->title ?? 'Hoarding #' . $item->hoarding_id }}
    </strong>
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    📍 {{ $item->hoarding->display_location ?? 'Location not specified' }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Type:</strong> {{ strtoupper($item->hoarding_type) }}
</td>
</tr>

@if($item->package_type === 'package')
<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Package:</strong> {{ $item->package_label ?? 'Standard Package' }}
</td>
</tr>

<tr>
<td style="padding:10px; font-size:13px;">
    @php
        $start = \Carbon\Carbon::parse($item->preferred_start_date);
        $end = \Carbon\Carbon::parse($item->preferred_end_date);
        $months = $start->diffInMonths($end);
    @endphp

    <strong>Campain Duration:</strong> {{ $months }} month{{ $months !== 1 ? 's' : '' }}
</td>
</tr>
@else
<tr>
<td style="padding:10px; font-size:13px;">
    <strong>Duration:</strong> {{ $item->expected_duration ?? 'Custom Duration' }}
</td>
</tr>
@endif

@if($item->hoarding_type === 'dooh' && isset($item->meta['dooh_specs']))
<tr>
<td style="padding:10px; font-size:13px; background:#f0fdf4;">
    <strong>DOOH Specifications:</strong><br>
    Video: {{ $item->meta['dooh_specs']['video_duration'] ?? 15 }} sec<br>
    Slots/Day: {{ $item->meta['dooh_specs']['slots_per_day'] ?? 120 }}<br>
    Total Days: {{ $item->meta['dooh_specs']['total_days'] ?? 0 }}
</td>
</tr>
@endif

</table>
</td>
</tr>
@endforeach

<!-- NEXT STEPS -->
<tr>
<td style="padding:10px 40px; font-size:14px; color:#444; line-height:22px;">
    <p><strong>What happens next?</strong></p>
    <ul style="padding-left:18px;">
        <li>We verify availability with media owners</li>
        <li>Vendor confirms campaign feasibility</li>
        <li>You receive quotation</li>
        <li>You approve & proceed with booking</li>
    </ul>
</td>
</tr>

<!-- BUTTON -->
<tr>
<td align="center" style="padding:10px 40px;">
    <a href="{{ route('customer.enquiries.show', $enquiry->id) }}"
       style="background:#16a34a; color:#ffffff; padding:12px 26px; font-size:14px; text-decoration:none; border-radius:6px; display:inline-block;">
        View My Enquiry
    </a>
</td>
</tr>

<!-- SUPPORT -->
<tr>
<td style="padding:0 20px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p>If you have any questions, contact us at <strong>support@oohapp.com</strong></p>
    <p>Thank you for choosing OOHAPP.<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

<!-- DISCLAIMER -->
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
        OOHAPP connects advertisers with media owners. Pricing, availability and execution
        are managed directly by the vendor. OOHAPP acts only as a facilitating platform.
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
