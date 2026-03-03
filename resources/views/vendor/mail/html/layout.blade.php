<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ config('app.name') }}</title>
<style>
body {
    margin: 0;
    padding: 0;
    background-color: #f4f6f8;
    font-family: Arial, Helvetica, sans-serif;
}
</style>
</head>
<body>
<table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f4f6f8; padding:40px 0;">
<tr>
<td align="center">
<table">
    @include('emails.partials.header')
<tr>
<td style="padding:20px 40px; font-size:14px; color:#444; line-height:22px;">
    {{ Illuminate\Mail\Markdown::parse($slot) }}
    @if (isset($subcopy))
    {{ Illuminate\Mail\Markdown::parse($subcopy) }}
@endif
</td>
</tr>
@include('emails.partials.footer')
</table>
</td>
</tr>
</table>
</body>
</html>
