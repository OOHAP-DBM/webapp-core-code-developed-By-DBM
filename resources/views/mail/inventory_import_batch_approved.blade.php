<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Inventory Import Batch Approved</title>
</head>
<body>
    <h2>Inventory Import Batch Approved</h2>
    <p>Dear Vendor,</p>
    <p>Your inventory import batch (Batch ID: {{ $batch->id }}) has been <strong>approved</strong> and
    @php $autoApproval = \App\Models\Setting::get('auto_hoarding_approval', false); @endphp
    @if($autoApproval)
        all valid hoardings have been published and are now <strong>active</strong> , please check.
    @else
        all valid hoardings are <strong>pending approval</strong>.
    @endif
    </p>
    <p>Total Hoardings Created: {{ $batch->valid_rows }}</p>
    <p>Thank you for using our platform.</p>
    <p>Regards,<br>OohApp Team</p>
</body>
</html>
