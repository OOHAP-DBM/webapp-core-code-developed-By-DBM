@php
    $createdCount = $createdCount ?? 0;
    $failedCount = $failedCount ?? 0;
    $greeting = $greeting ?? '';
    $batchUrl = $batchUrl ?? null;
    $batchId = $batchId ?? null;
    $hoardings = $hoardings ?? collect();
     $isLive = $isLive ?? false; // 
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Bulk Import Status - OOHAPP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            @if($isLive)
                🚀 Your Hoardings Are Live!
            @else
                  Your Hoardings Are Created & Waiting for Admin Approval
            @endif
        </h2>
        <p style="margin-top:8px; color:#666; font-size:14px;">
            {{ $greeting }}
        </p>
    </td>
</tr>

<!-- SUCCESS BOX -->
<tr>
<td style="padding:20px 40px 0 40px;">
    @if($isLive)
    <div style="background:#ecfdf5; border-left:4px solid #16a34a; padding:15px; font-size:14px; color:#065f46;">
        <strong> Your {{ $createdCount }} {{ Str::plural('hoarding', $createdCount) }}</strong> are now <strong>LIVE on the platform</strong>.<br>
        You can start receiving bookings immediately.
    </div>
    @else
        <div style="background:#eff6ff; border-left:4px solid #2563eb; padding:15px; font-size:14px; color:#1e3a8a;">
            <strong>{{ $createdCount }} {{ Str::plural('hoarding', $createdCount) }}</strong> successfully created and awaiting final activation by our team. <br>
            They will go live after final activation.
        </div>
    @endif
</td>
</tr>

<!-- ERROR BOX (if any) -->
@if($failedCount > 0)
<tr>
<td style="padding:20px 40px 0 40px;">
    <div style="background:#fef2f2; border-left:4px solid #dc2626; padding:15px; font-size:14px; color:#991b1b;">
        <strong>{{ $failedCount }} record{{ $failedCount > 1 ? 's' : '' }}</strong> failed to import. Please review the inventory for details.
    </div>
</td>
</tr>
@endif

<!-- SUMMARY ROW -->
<tr>
<td style="padding:20px 40px 0 40px;">
    <table width="100%" cellpadding="0" cellspacing="0">
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Inventory ID:</strong> {{ $batchId }}
            </td>
        </tr>
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Created Hoardings:</strong> {{ $createdCount }}
            </td>
        </tr>
        <tr>
            <td style="font-size:15px; color:#222; padding-bottom:8px;">
                <strong>Failed Records:</strong> {{ $failedCount }}
            </td>
        </tr>
    </table>
</td>
</tr>

<!-- HOARDINGS TABLE (limit 10) -->
@if($createdCount > 0 && $hoardings->count())
<tr>
<td style="padding:20px 40px 0 40px;">
    <p style="margin:0 0 10px 0; font-size:15px; font-weight:600; color:#333;">
        Created Hoardings (showing up to 10):
    </p>
    <table width="100%" cellpadding="0" cellspacing="0"
           style="border-collapse:collapse; font-size:13px;">
        <thead>
            <tr style="background:#f0fdf4;">
                <th align="left"
                    style="padding:10px 12px; border:1px solid #d1fae5; color:#065f46; font-weight:600; width:30px;">
                    #
                </th>
                <th align="left"
                    style="padding:10px 12px; border:1px solid #d1fae5; color:#065f46; font-weight:600;">
                    Title
                </th>
                <th align="left"
                    style="padding:10px 12px; border:1px solid #d1fae5; color:#065f46; font-weight:600; width:80px;">
                    Type
                </th>
            </tr>
        </thead>
        <tbody>
            @foreach($hoardings->take(10) as $hoarding)
            <tr style="background: {{ $loop->even ? '#f9fafb' : '#ffffff' }};">
                <td style="padding:10px 12px; border:1px solid #e5e7eb; color:#555;">
                    {{ $loop->iteration }}
                </td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb;">
                    <a href="{{ route('vendor.myHoardings.show', $hoarding->id) }}"
                       style="color:#16a34a; text-decoration:none; font-weight:500;">
                        {{ $hoarding->title ?? $hoarding->name }}
                    </a>
                </td>
                <td style="padding:10px 12px; border:1px solid #e5e7eb; color:#555;">
                    {{ strtoupper($hoarding->hoarding_type) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</td>
</tr>
@endif

<!-- ACTION BUTTON -->
<tr>
<td align="center" style="padding:30px 40px 40px 40px;">
    <a href="{{ route('vendor.hoardings.myHoardings')  }}"
       style="display:inline-block; background:#16a34a; color:#fff; font-weight:600; padding:12px 32px; border-radius:6px; text-decoration:none; font-size:16px;">
            View All Hoardings
    </a>
</td>
</tr>

<!-- DISCLAIMER -->
<tr>
    <td style="padding:10px 40px 7px 40px;">
        <div style="
            margin-top: 25px;
            padding-top: 12px;
            border-top: 1px dashed #ddd;
            font-size: 9px;
            color: #777;
            line-height: 1.5;
        ">
            <strong style="color:#555;">Disclaimer:</strong>
            OOHAPP provides a platform to connect you with interested advertisers.
            All quotations, pricing, timelines, and execution details are shared by you directly.
            Any discussion or confirmation happens between you and the advertiser.
            OOHAPP acts as a facilitator and does not participate in pricing or execution decisions.
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
