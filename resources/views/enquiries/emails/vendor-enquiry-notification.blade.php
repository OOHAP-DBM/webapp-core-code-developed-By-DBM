@component('mail::message')
# New Hoarding Enquiry Alert!

Hello **{{ $vendor->name }}**,

Great news! A potential client is looking for hoarding spaces in **{{ $enquiry->location_city }}** - an area you service.

## 📋 Enquiry Details

**Client Information:**
- **Name:** {{ $enquiry->name }}
- **Phone:** {{ $enquiry->formatted_phone }}
- **Email:** {{ $enquiry->email }}

**Requirements:**
- **Hoarding Type:** {{ str_replace(',', ', ', $enquiry->hoarding_type) }}
- **City:** {{ $enquiry->location_city }}
@if($enquiry->preferred_locations && count($enquiry->preferred_locations) > 0 && $enquiry->preferred_locations[0] !== 'To be discussed')
- **Preferred Locations:** {{ implode(', ', $enquiry->preferred_locations) }}
@endif

**Client's Message:**
> {{ $enquiry->remarks }}

@if($enquiry->preferred_modes && count($enquiry->preferred_modes) > 0)
**Preferred Contact Method:** {{ implode(', ', $enquiry->preferred_modes) }}
@endif

---

## 🚀 Next Steps

1. Review the enquiry details above
2. Prepare your best quote and options
3. Contact the client within 24 hours for best results

@component('mail::button', ['url' => $dashboardUrl])
View Full Enquiry & Respond
@endcomponent

---

### 💡 Pro Tips:
- **Speed matters:** Clients prefer vendors who respond quickly
- **Be specific:** Include location details, pricing, and availability
- **Add value:** Suggest alternatives or packages they might not have considered

@component('mail::panel')
**Important:** This enquiry was sent to selected vendors in your area. Quick response increases your chances of winning this business!
@endcomponent

Need help? Contact our support team at support@yourdomain.com

Best regards,<br>
**{{ config('app.name') }} Team**

---

<small style="color: #999;">
You're receiving this because you're a registered vendor with hoarding locations in {{ $enquiry->location_city }}. 
To update your service areas, visit your [vendor dashboard]({{ route('vendor.dashboard') }}).
</small>
@endcomponent