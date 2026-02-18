@component('mail::message')
# Thank You for Your Enquiry! 🎯

Hi **{{ $enquiry->name }}**,

We've received your hoarding enquiry and our team is on it! Here's what happens next:

@component('mail::panel')
**Your Enquiry ID:** #{{ str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
@endcomponent

## 📝 Your Requirements Summary

**Location:** {{ $enquiry->location_city }}
@if($enquiry->preferred_locations && count($enquiry->preferred_locations) > 0 && $enquiry->preferred_locations[0] !== 'To be discussed')
**Preferred Areas:** {{ implode(', ', $enquiry->preferred_locations) }}
@endif
**Hoarding Type:** {{ str_replace(',', ', ', $enquiry->hoarding_type) }}

---

## ⏱️ What Happens Next?

@component('mail::table')
| Timeline | Action |
|:---------|:-------|
| **Now** | We're notifying verified vendors in your area |
| **Within 24 hours** | You'll start receiving quotes via {{ implode('/', $enquiry->preferred_modes ?? ['phone', 'email']) }} |
| **24-48 hours** | Multiple vendors will reach out with their best offers |
@endcomponent

---

## 📞 Our vendors will contact you via:
@if($enquiry->preferred_modes && count($enquiry->preferred_modes) > 0)
@foreach($enquiry->preferred_modes as $mode)
- ✅ {{ $mode }}
@endforeach
@else
- ✅ Phone Call
- ✅ Email
@endif

---

@component('mail::button', ['url' => $trackingUrl, 'color' => 'success'])
Track Your Enquiry Status
@endcomponent

---

## 💡 Pro Tips While You Wait:

1. **Keep your phone handy** - Vendors move fast!
2. **Compare multiple quotes** - Don't settle on the first offer
3. **Ask about visibility** - Request foot traffic and vehicle count data
4. **Check availability** - Popular spots get booked quickly

---

### Need to Update Your Requirements?

Contact us at:
- 📧 Email: support@yourdomain.com
- 📱 Phone: +91-1234567890
- 💬 WhatsApp: +91-1234567890

@component('mail::subcopy')
**Reference Information:**
- Enquiry ID: #{{ str_pad($enquiry->id, 6, '0', STR_PAD_LEFT) }}
- Submitted: {{ $enquiry->created_at->format('d M Y, h:i A') }}
- Phone: {{ $enquiry->formatted_phone }}
- Email: {{ $enquiry->email }}
@endcomponent

Best regards,<br>
**{{ config('app.name') }} Team**

---

<small style="color: #999;">
This is an automated confirmation. Please do not reply to this email. 
For any queries, contact us at support@yourdomain.com
</small>
@endcomponent
