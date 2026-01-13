<?php

namespace Database\Seeders;

use App\Models\CancellationRefundPolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CancellationRefundPolicySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        CancellationRefundPolicy::truncate();

        CancellationRefundPolicy::create([
            'title' => 'Cancellation-Refund-Policy',
            'content' => <<<'HTML'
<p>
At OOHAPP, we are committed to providing a transparent and fair booking experience for our customers.
We understand that in certain circumstances, it may be necessary to implement a strict
<strong>"No Refund and Cancellation"</strong> policy to ensure the integrity and availability of our hoarding booking platform.
Please read the following policy carefully before making a booking on our website.
</p>

<h4>1. No Cancellation:</h4>
<p>
Once a booking is confirmed on OOHAPP, it is considered final and cannot be cancelled or modified under any circumstances.
</p>

<h4>2. No Refunds:</h4>
<p>
We do not offer refunds for any hoarding bookings made through our platform, regardless of the reason for cancellation or non-utilization of the booked hoarding space.
</p>

<h4>3. Non-Transferable:</h4>
<p>
Bookings made on OOHAPP are non-transferable. The name, date, and location of the booking cannot be changed or transferred to another party.
</p>

<h4>4. Exceptions:</h4>
<p>
In exceptional cases where the hoarding becomes unavailable due to circumstances beyond our control, such as natural disasters or structural issues,
OOHAPP reserves the right to cancel the booking and will issue a full refund to the customer.
We will make every effort to notify the customer promptly in such cases.
</p>

<h4>5. Contact Information:</h4>
<p>
If you have any questions or concerns regarding our Refund and Cancellation Policy, please contact our customer support team at
<a href="mailto:support@oohapp.io">support@oohapp.io</a>. We are here to provide assistance and address any inquiries you may have.
</p>

<p><strong>Note:</strong><br>
This No Refund and Cancellation Policy is in place to maintain the reliability and availability of our hoarding booking platform.
By using OOHAPP, you acknowledge and agree to abide by this policy.
OOHAPP reserves the right to make changes to this policy without prior notice.
Customers are advised to review this policy periodically for any updates.
</p>
HTML,
            'effective_date' => '2025-01-01',
            'is_active' => 1,
        ]);
    }
}
