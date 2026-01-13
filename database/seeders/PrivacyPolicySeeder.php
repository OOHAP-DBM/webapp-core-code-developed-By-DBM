<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PrivacyPolicySeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PrivacyPolicy::truncate();

        PrivacyPolicy::create([
            'title' => 'Privacy Policy',
            'content' => <<<'HTML'
<p>
OOHAPP ("OOHAPP", "we", "us", or "our") values your privacy and is committed to protecting your personal data.
This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you use our website,
mobile application, and services.
</p>

<h3><strong>1. Information We Collect</strong></h3>

<p><strong>Personal Data</strong></p>
<ul>
    <li>Name, email address, phone number</li>
    <li>Billing address and payment information</li>
    <li>Company details (if applicable)</li>
</ul>

<p><strong>Usage Data</strong></p>
<ul>
    <li>IP address, browser type, device information</li>
    <li>Pages visited, time spent, referring URLs</li>
</ul>

<h3><strong>2. How We Use Your Information</strong></h3>
<ul>
    <li>To provide and manage our services</li>
    <li>To process transactions and bookings</li>
    <li>To communicate updates, offers, and support</li>
    <li>To improve platform performance and security</li>
</ul>

<h3><strong>3. Sharing Your Information</strong></h3>
<p>
We do not sell your personal information. We may share data with:
</p>
<ul>
    <li>Service providers and payment partners</li>
    <li>Legal authorities when required by law</li>
    <li>Business partners with confidentiality obligations</li>
</ul>

<h3><strong>4. Data Security</strong></h3>
<p>
We use administrative, technical, and physical security measures to protect your personal data.
However, no system is completely secure.
</p>

<h3><strong>5. Cookies and Tracking</strong></h3>
<p>
We use cookies and similar tracking technologies to enhance user experience, analyze traffic,
and personalize content.
</p>

<h3><strong>6. Third-Party Services</strong></h3>
<p>
Our services may contain links to third-party websites. We are not responsible for their privacy practices.
</p>

<h3><strong>7. Your Privacy Rights</strong></h3>
<ul>
    <li>Access, update, or delete your personal data</li>
    <li>Opt-out of marketing communications</li>
    <li>Withdraw consent where applicable</li>
</ul>

<h3><strong>8. Changes to This Policy</strong></h3>
<p>
We may update this Privacy Policy from time to time. Changes will be posted on this page.
</p>

<h3><strong>9. Contact Us</strong></h3>
<p>
If you have any questions about this Privacy Policy, contact us at:
<br>
<strong>Email:</strong> support@oohapp.io
</p>
HTML,
            'effective_date' => '2025-01-01',
            'is_active' => 1,
        ]);
    }
}
