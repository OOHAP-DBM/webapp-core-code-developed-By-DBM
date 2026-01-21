<?php

namespace Database\Seeders;

use App\Models\PrivacyPolicy;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class PrivacyPolicySeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        PrivacyPolicy::truncate();

        PrivacyPolicy::create([
            'title' => 'Privacy Policy',
            'content' => <<<'HTML'

<h2><strong>Privacy Policy</strong></h2>

<p>
This Privacy Policy (“Policy”) describes how OOHAPP (“OOHAPP” or “we”) collects, uses, discloses, and stores
information about you when you use our website, services, and products (together, the “Services”).
</p>

<p>
This policy is part of and incorporated by reference into OOHAPP’s Terms of Service.
If you use the Services, you agree to the terms of this Policy and you consent to the collection,
use, disclosure, and storage of your information as described in this Policy.
</p>

<hr>

<h3><strong>1. INFORMATION WE COLLECT</strong></h3>

<p>
We collect information about you when you use the Services, when you contact us,
and when other sources provide information about you.
</p>

<p><strong>The types of information we collect include:</strong></p>

<p><strong>Personal Information:</strong></p>
<p>
Personal information is any information that can be used to identify you,
including your name, address, email address, phone number, and other contact information.
We may also collect other information that we consider personal information if it is combined with personal information.
</p>

<p><strong>Usage Information:</strong></p>
<p>
We collect information about your interactions with our Services,
including the content you view, the features you use, and the links you click.
We also collect information about how you access our Services,
such as the frequency and duration of your visits.
</p>

<p><strong>Device and Technical Information:</strong></p>
<p>
We collect information about the device you use to access our Services,
such as your hardware model, IP address, operating system version,
web browser type, and mobile device identifier.
</p>

<p><strong>Profile Information:</strong></p>
<p>
We may collect information about your profile, such as your username,
profile picture, and any other information you choose to provide.
</p>

<p><strong>Third-Party Information:</strong></p>
<p>
We may also collect information about you from third parties,
such as social media networks, analytics providers, and advertising networks.
</p>

<hr>

<h3><strong>2. HOW WE USE INFORMATION</strong></h3>

<p>
We use the information we collect for a variety of purposes, including:
</p>

<ul>
    <li>To provide and improve our Services;</li>
    <li>To personalize your experience;</li>
    <li>To understand how our Services are used;</li>
    <li>To detect, investigate, and prevent fraudulent and illegal activities;</li>
    <li>To respond to customer service requests and support needs;</li>
    <li>To send you notifications, updates, security alerts, and support and administrative messages;</li>
    <li>To provide advertising and marketing communications;</li>
    <li>To measure the effectiveness of our advertising and marketing campaigns;</li>
    <li>To conduct research and analysis;</li>
    <li>To develop new products and services;</li>
    <li>To protect the rights and safety of our users and third parties;</li>
    <li>To comply with legal requirements; and</li>
    <li>To comply with our policies and terms.</li>
</ul>

<hr>

<h3><strong>3. HOW WE SHARE INFORMATION</strong></h3>

<p>
We may share information about you with third parties for the following purposes:
</p>

<p><strong>To Provide our Services:</strong></p>
<p>
We may share information about you with third parties to provide our Services.
This includes sharing information with service providers that help us provide our Services,
such as hosting providers, analytics providers, and payment processors.
</p>

<p><strong>To Comply with Legal Requirements:</strong></p>
<p>
We may share information about you if we believe it is necessary to:
</p>

<ul>
    <li>Comply with applicable law, regulation, legal process, or governmental request;</li>
    <li>Enforce our policies, including our Terms of Service;</li>
    <li>Protect the security or integrity of our Services; or</li>
    <li>Protect the rights, property, or safety of OOHAPP, our users, or others.</li>
</ul>

<p><strong>For Other Reasons:</strong></p>
<p>
We may share information about you with third parties for any other purpose with your consent.
We may also share aggregated or de-identified information that cannot be used to identify you.
</p>

<hr>

<h3><strong>4. DATA SECURITY</strong></h3>

<p>
We take reasonable measures to protect your information from unauthorized access,
disclosure, alteration, or destruction.
However, no security measures are perfect or impenetrable,
and we cannot guarantee the security of your information.
</p>

<hr>

<h3><strong>5. CHILDREN’S PRIVACY</strong></h3>

<p>
Our Services are not intended for use by children under the age of 13.
If you are under the age of 13, please do not provide any information through our Services.
If we become aware that we have collected information from a child under the age of 13,
we will delete such information.
</p>

<hr>

<h3><strong>6. COOKIES & THIS POLICY</strong></h3>

<p>
We may use cookies and other technologies, such as web beacons,
to collect information about your use of our Services.
We may also use third-party analytics services that use cookies and similar technologies.
</p>

<hr>

<h3><strong>7. CHANGES AND AMENDMENTS</strong></h3>

<p>
We may modify this Privacy Policy from time to time.
If we make material changes to this Policy,
we will notify you by posting the updated Policy on our website or by other means.
We encourage you to review this Policy periodically for changes.
If you continue to use our Services after we have posted an updated Policy,
you consent to the updated Policy.
</p>

<hr>

<h3><strong>8. CONTACT US</strong></h3>

<p>
If you have any questions about this Policy, please contact us at:
</p>

<p>
<strong>Email:</strong> <a href="mailto:support@oohapp.io">support@oohapp.io</a>
</p>

HTML,
            'effective_date' => '2025-01-01',
            'is_active' => 1,
        ]);
    }
}
