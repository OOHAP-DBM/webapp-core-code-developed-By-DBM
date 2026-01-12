<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto;">

    <!-- Header -->
    <div style="background-color: #16a34a; padding: 20px; border-radius: 8px 8px 0 0; margin-bottom: 20px; text-align: center;">
        <h2 style="color: #ffffff; margin: 0;">Your Hoarding Has Been Approved & Published! 🎉</h2>
    </div>

    <!-- Main Content -->
    <div style="padding: 20px; background-color: #ffffff; border: 1px solid #e5e7eb;">
        
        <p>Dear <strong>{{ $vendor_name }}</strong>,</p>

        <p style="margin-bottom: 15px;">We're pleased to inform you that your hoarding <strong>"{{ $hoarding->title }}"</strong> has been reviewed, approved, and successfully published on the OOHAPP platform.</p>

        <p style="margin-bottom: 20px;">Your hoarding is now live and visible to advertisers searching for outdoor advertising opportunities. You may start receiving enquiries shortly.</p>

        <!-- Hoarding Details Box -->
        <div style="background-color: #f0f9ff; border-left: 4px solid #16a34a; padding: 15px; margin: 20px 0;">
            <h3 style="color: #2e7d32; margin-top: 0; margin-bottom: 12px;">Hoarding Details:</h3>
            
            <ul style="margin: 0; padding-left: 20px; list-style-type: none;">
                <li style="padding: 6px 0;">
                    <strong style="color: #555;">Title:</strong> 
                    <span style="color: #333;">{{ $hoarding->title }}</span>
                </li>
                <li style="padding: 6px 0;">
                    <strong style="color: #555;">Location:</strong> 
                    <span style="color: #333;">{{ $hoarding->address ?? 'N/A' }}</span>
                </li>
                <li style="padding: 6px 0;">
                    <strong style="color: #555;">Category:</strong> 
                    <span style="color: #333;">{{ ucfirst($hoarding->hoarding_type ?? 'N/A') }}</span>
                </li>
                <li style="padding: 6px 0;">
                    <strong style="color: #555;">Hoarding ID:</strong> 
                    <span style="color: #16a34a; font-weight: bold;">{{ $hoarding_id }}</span>
                </li>
                <li style="padding: 6px 0;">
                    <strong style="color: #555;">Commission Rate:</strong> 
                    <span style="color: #16a34a; font-weight: bold;">{{ $hoarding_commission ?? 'Not Set' }}%</span>
                </li>
            </ul>
        </div>

        <!-- Next Steps -->
        <p style="margin-top: 20px;">If you wish to update pricing, packages, availability, or images, you can do so anytime from your vendor dashboard.</p>

        <!-- Appreciation -->
        <div style="background-color: #fef3c7; border-left: 4px solid #f59e0b; padding: 15px; margin: 20px 0;">
            <p style="margin: 0;">We appreciate your partnership and look forward to helping you maximize your hoarding's reach.</p>
        </div>

        <!-- Support -->
        <p>For any assistance, feel free to contact our support team.</p>

        <!-- Footer -->
        <p style="margin-top: 30px; margin-bottom: 0;">Best regards,<br><strong>Team OOHAPP</strong></p>
    </div>

    <!-- Bottom Footer -->
    <div style="background-color: #f3f4f6; padding: 15px; text-align: center; border-radius: 0 0 8px 8px;">
        <p style="margin: 5px 0; font-size: 13px; color: #666;">
            <strong>Email:</strong> <a href="mailto:support@oohapp.in" style="color: #16a34a; text-decoration: none;">support@oohapp.in</a>
        </p>
        <p style="margin: 5px 0; font-size: 13px; color: #666;">
            <strong>Website:</strong> <a href="https://www.oohapp.in" style="color: #16a34a; text-decoration: none;">www.oohapp.in</a>
        </p>
    </div>

</div>
