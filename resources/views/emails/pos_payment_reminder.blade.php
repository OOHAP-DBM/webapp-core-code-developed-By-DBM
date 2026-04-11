<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Payment Reminder - OOHAPP</title>
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">

<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 2px 8px rgba(0,0,0,0.08);">

@include('emails.partials.header')

@php
    $resolvedMilestone = null;

    if (isset($booking) && method_exists($booking, 'milestones')) {
        $milestoneRows = $booking->milestones()
            ->whereNull('deleted_at')
            ->orderBy('sequence_no')
            ->get();

        if ($milestoneRows->isNotEmpty()) {
            $currentMilestoneId = (int) ($booking->current_milestone_id ?? 0);
            if ($currentMilestoneId > 0) {
                $resolvedMilestone = $milestoneRows->first(function ($item) use ($currentMilestoneId) {
                    return (int) $item->id === $currentMilestoneId
                        && !in_array($item->status, ['paid', 'cancelled'], true);
                });
            }

            if (!$resolvedMilestone) {
                $resolvedMilestone = $milestoneRows->first(function ($item) {
                    return in_array($item->status, ['due', 'overdue'], true);
                })
                ?? $milestoneRows->first(function ($item) {
                    return $item->status === 'pending';
                })
                ?? $milestoneRows->first(function ($item) {
                    return !in_array($item->status, ['paid', 'cancelled'], true);
                });
            }
        }
    }

    $effectiveHasMilestoneReminder = isset($hasMilestoneReminder)
        ? (bool) $hasMilestoneReminder
        : ($resolvedMilestone !== null);

    if (!$effectiveHasMilestoneReminder && $resolvedMilestone !== null) {
        $effectiveHasMilestoneReminder = true;
    }

    $effectiveMilestoneName = $milestoneName
        ?? ($resolvedMilestone->title ?? 'Milestone Payment');

    $effectiveMilestoneAmountDue = $milestoneAmountDue
        ?? ($resolvedMilestone
            ? number_format((float) ($resolvedMilestone->calculated_amount ?? $resolvedMilestone->amount ?? 0), 2)
            : ($remainingAmount ?? '0.00'));

    $effectiveMilestoneDueDate = $milestoneDueDate
        ?? (($resolvedMilestone && $resolvedMilestone->due_date)
            ? $resolvedMilestone->due_date->format('d M, y')
            : 'N/A');

    $effectiveBookingDateLabel = $bookingDateLabel
        ?? ((isset($booking->created_at) && $booking->created_at)
            ? $booking->created_at->format('d M Y, h:i A')
            : 'N/A');

    $effectiveDurationLabel = $durationLabel ?? null;
    if ($effectiveDurationLabel === null && isset($booking)) {
        $startDate = $booking->start_date ?? null;
        $endDate = $booking->end_date ?? null;

        if ($startDate && $endDate) {
            $days = max(1, (int) $startDate->diffInDays($endDate) + 1);
            $dayLabel = $days === 1 ? 'day' : 'days';
            $effectiveDurationLabel = $startDate->format('d M Y') . ' - ' . $endDate->format('d M Y') . " ({$days} {$dayLabel})";
        } else {
            $durationDays = (int) ($booking->duration_days ?? 0);
            $effectiveDurationLabel = $durationDays > 0
                ? ($durationDays . ' day' . ($durationDays === 1 ? '' : 's'))
                : 'N/A';
        }
    }

    $effectiveHoardingName = $hoardingName ?? null;
    if ($effectiveHoardingName === null && isset($booking) && method_exists($booking, 'bookingHoardings')) {
        if (!$booking->relationLoaded('bookingHoardings')) {
            $booking->load('bookingHoardings.hoarding');
        }

        $hoardingNames = $booking->bookingHoardings
            ->map(function ($bookingHoarding) {
                return trim((string) ($bookingHoarding->hoarding->title ?? ''));
            })
            ->filter()
            ->unique()
            ->values();

        if ($hoardingNames->isEmpty()) {
            $effectiveHoardingName = 'N/A';
        } elseif ($hoardingNames->count() === 1) {
            $effectiveHoardingName = $hoardingNames->first();
        } else {
            $previewNames = $hoardingNames->take(2)->implode(', ');
            $remainingCount = $hoardingNames->count() - 2;
            $effectiveHoardingName = $remainingCount > 0
                ? $previewNames . ' +' . $remainingCount . ' more'
                : $previewNames;
        }
    }

    if ($effectiveHoardingName === null) {
        $effectiveHoardingName = 'N/A';
    }

    $invoiceNumber = $booking->invoice_number ?? $booking->id;
    $statusRaw = (string) ($booking->status ?? 'N/A');
    $statusLabel = ucfirst(str_replace('_', ' ', $statusRaw));
    $statusBadgeBg = '#e5e7eb';
    $statusBadgeText = '#374151';

    if (in_array($statusRaw, ['pending_payment', 'hold', 'on_hold', 'draft'], true)) {
        $statusBadgeBg = '#fef3c7';
        $statusBadgeText = '#92400e';
    } elseif (in_array($statusRaw, ['confirmed', 'active'], true)) {
        $statusBadgeBg = '#dcfce7';
        $statusBadgeText = '#166534';
    } elseif (in_array($statusRaw, ['cancelled'], true)) {
        $statusBadgeBg = '#fee2e2';
        $statusBadgeText = '#991b1b';
    }
@endphp

<tr>
<td align="center" style="padding:20px 40px 0 40px;">
    <h2 style="margin:0; color:#dc2626; font-weight:600;">
        {{ $effectiveHasMilestoneReminder ? 'Milestone Payment Reminder ⏰' : 'Payment Reminder ⏰' }}
    </h2>
    <p style="margin-top:8px; color:#666; font-size:14px;">
        Hi {{ $greetingName ?? ($customer->name ?? ($booking->customer_name ?? 'Customer')) }},
        @if($effectiveHasMilestoneReminder)
            this reminder is for your milestone payment.
        @else
            this is a gentle reminder for your pending booking payment.
        @endif
    </p>
</td>
</tr>

<tr>
<td style="padding:15px 40px; font-size:14px; color:#444; line-height:22px;">
    @if($effectiveHasMilestoneReminder)
        This booking is associated with Invoice <strong>{{ $invoiceNumber }}</strong>. Please complete the due milestone payment to avoid booking release.
    @else
        Please review the details below and clear the outstanding amount at the earliest.
    @endif
</td>
</tr>

@if($effectiveHasMilestoneReminder)
<tr>
<td style="padding:0 40px 10px 40px;">
    <p style="margin:0; padding:10px 12px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412; border-radius:6px; font-size:13px; line-height:20px;">
        <strong>Important:</strong>
        Your booking is on hold and may be <strong>released automatically</strong> if payment is not completed within the hold duration.
    </p>
</td>
</tr>
@endif

<tr>
<td style="padding:10px 40px;">
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:13px;">Information</th>
            <th align="left" style="padding:10px; font-size:13px;">Details</th>
        </tr>

        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Booking ID</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $booking->id }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Invoice No.</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $invoiceNumber }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Status</strong></td>
            <td style="padding:10px; font-size:13px;">
                <span style="background:{{ $statusBadgeBg }}; color:{{ $statusBadgeText }}; padding:4px 8px; font-weight:700; border-radius:4px;">
                    {{ $statusLabel }}
                </span>
            </td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Booking Date</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveBookingDateLabel }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Hoarding</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveHoardingName }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Duration</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveDurationLabel ?? 'N/A' }}</td>
        </tr>
    </table>
</td>
</tr>

@if($effectiveHasMilestoneReminder)
<tr>
<td style="padding:16px 40px 0 40px;">
    <p style="font-size:14px; color:#444; margin:0 0 8px 0;"><strong>Milestone Payment Details</strong></p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:13px;">Field</th>
            <th align="left" style="padding:10px; font-size:13px;">Details</th>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Milestone</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveMilestoneName }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Amount Due</strong></td>
            <td style="padding:10px; font-size:13px;"><strong>₹{{ $effectiveMilestoneAmountDue }}</strong></td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Due Date</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $effectiveMilestoneDueDate }}</td>
        </tr>
        {{-- <tr>
            <td style="padding:10px; font-size:13px;"><strong>Reminder Count</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $reminderCount }}</td>
        </tr> --}}
    </table>
</td>
</tr>
@else
<tr>
<td style="padding:16px 40px 0 40px;">
    <p style="font-size:14px; color:#444; margin:0 0 8px 0;"><strong>Payment Summary</strong></p>
    <table width="100%" cellpadding="0" cellspacing="0" border="0" style="border:1px solid #e5e7eb;">
        <tr style="background:#f3f4f6;">
            <th align="left" style="padding:10px; font-size:13px;">Field</th>
            <th align="left" style="padding:10px; font-size:13px;">Details</th>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Total Amount</strong></td>
            <td style="padding:10px; font-size:13px;">₹{{ $totalAmount }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Paid Amount</strong></td>
            <td style="padding:10px; font-size:13px;">₹{{ $paidAmount }}</td>
        </tr>
        <tr>
            <td style="padding:10px; font-size:13px;"><strong>Outstanding Balance</strong></td>
            <td style="padding:10px; font-size:13px;"><strong>₹{{ $remainingAmount }}</strong></td>
        </tr>
        {{-- <tr>
            <td style="padding:10px; font-size:13px;"><strong>Reminder Count</strong></td>
            <td style="padding:10px; font-size:13px;">{{ $reminderCount }}/3</td>
        </tr> --}}
    </table>
</td>
</tr>
@endif

<tr>
<td align="center" style="padding:24px 40px 10px 40px;">
    @if(!empty($actionUrl))
        <a href="{{ $actionUrl }}" style="display:inline-block; background:#16a34a; color:#ffffff; padding:10px 22px; text-decoration:none; border-radius:6px; font-size:14px; font-weight:600;">
            View Booking Details
        </a>
    @endif
</td>
</tr>

<tr>
<td style="padding:10px 40px 20px 40px; font-size:14px; color:#444; line-height:22px;">
    <p style="margin:0 0 8px 0;">If you have any questions, contact us at <strong>support@oohapp.com</strong></p>
    <p style="margin:0;">Thank you for your business with OOHAPP.<br><strong>Team OOHAPP</strong></p>
</td>
</tr>

@include('emails.partials.footer')

</table>
</td>
</tr>
</table>

</body>
</html>
