<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Hoarding Status Update</title>
</head>
<body>
    <h2>Hoarding Status Update</h2>
    <p>Dear Vendor,</p>
    <p>Your hoarding (ID: {{ $hoarding->id }}, Address: {{ $hoarding->address }}) has a new status:</p>
    <p><strong>{{ $statusText }}</strong></p>
    <p>Thank you for using OohApp.</p>
    <p>Regards,<br>OohApp Team</p>
</body>
</html>
