<div style="font-family: sans-serif; padding: 20px; border: 1px solid #eee;">
    <h2 style="color: #009A5C;">New Direct Enquiry Received</h2>
    <hr>
    <p><strong>Name:</strong> {{ $enquiry->name }}</p>
    <p><strong>Phone:</strong> {{ $enquiry->phone }}</p>
    <p><strong>Email:</strong> {{ $enquiry->email }}</p>
    <p><strong>City:</strong> {{ $enquiry->location_city }}</p>
    <p><strong>Hoarding:</strong> {{ $enquiry->hoarding_type }} at {{ $enquiry->hoarding_location }}</p>
    <p><strong>Remarks:</strong> {{ $enquiry->remarks }}</p>
</div>