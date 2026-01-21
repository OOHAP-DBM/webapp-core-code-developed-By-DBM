<?php

namespace Database\Seeders;

use App\Models\TermsAndCondition;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TermsAndConditionsSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        TermsAndCondition::truncate();

        TermsAndCondition::create([
            'section_title' => 'Terms and Conditions',
            'content' => <<<'HTML'

<style>
    .tc-section h2 {
        margin-bottom: 6px;
    }
    .tc-section .section-content {
        margin-top: 8px;
    }
</style>

<div class="tc-section">

<h2><strong>1. ACCEPTANCE OF TERMS</strong></h2>
<div class="section-content">
<p>Welcome to OOHAPP.</p>

<p>
These Terms and Conditions (“Legal Terms,” “Terms”) constitute a legally binding agreement between:
</p>

<ul>
    <li>You, whether personally or on behalf of an entity (“User,” “You”), and</li>
    <li><strong>OOHAPP ADSPACE NETWORK LLP</strong>, a limited liability partnership registered in India (“OOHAPP,” “Company,” “We,” “Us,” “Our”).</li>
</ul>

<p>
By accessing, downloading, installing, registering, or using the OOHAPP mobile application, website, or any related services (collectively, the “Services”), you confirm that you have read, understood, and agreed to be bound by these Legal Terms.
</p>

<p><strong>If you do not agree with any part of these terms, you must immediately discontinue use of the services.</strong></p>
</div>

<hr>

<h2><strong>2. COMPANY INFORMATION</strong></h2>
<div class="section-content">
<p><strong>Legal Entity Name:</strong> OOHAPP ADSPACE NETWORK LLP</p>

<p><strong>Registered Address:</strong><br>
B-25, Vibhuti Khand Road,<br>
Vibhuti Khand, Gomti Nagar,<br>
Lucknow, Uttar Pradesh – 226010, India
</p>

<p><strong>Email:</strong> support@oohapp.io</p>
<p><strong>Platform:</strong> OOHAPP (mobile app & web)</p>
</div>

<hr>

<h2><strong>3. DEFINITIONS (CLARITY SECTION)</strong></h2>
<div class="section-content">
<ul>
    <li><strong>Advertiser/brand/agency</strong> means any individual or entity seeking to book outdoor advertising inventory.</li>
    <li><strong>Vendor/media owner</strong> means any third party listing outdoor advertising inventory on the platform.</li>
    <li><strong>Campaign</strong> means any outdoor advertising booking, inquiry, or execution initiated through OOHAPP.</li>
    <li><strong>Content</strong> means all text, images, videos, data, listings, designs, software, and material available on the Services.</li>
    <li><strong>User</strong> includes advertisers, vendors, visitors, and any registered or unregistered user of the platform.</li>
</ul>
</div>

<hr>

<h2><strong>4. NATURE OF PLATFORM & ROLE OF OOHAPP (CRITICAL)</strong></h2>
<div class="section-content">
<p>OOHAPP is a technology-enabled marketplace and aggregation platform for outdoor and digital-out-of-home (DOOH) advertising.</p>

<h3>4.1 Platform Role</h3>
<ul>
    <li>OOHAPP does not own, operate, control, or manage any advertising inventory.</li>
    <li>All hoardings, billboards, media units, and locations are owned and managed by independent vendors.</li>
    <li>OOHAPP only facilitates discovery, inquiry, communication, and optional payment enablement.</li>
</ul>

<h3>4.2 No Agency or Partnership</h3>
<p>Nothing in these Terms shall be deemed to create:</p>
<ul>
    <li>An agency relationship</li>
    <li>A partnership</li>
    <li>A joint venture</li>
    <li>An employment relationship</li>
</ul>
</div>

<hr>

<h2><strong>5. ELIGIBILITY & USER RESPONSIBILITY</strong></h2>
<div class="section-content">
<ul>
    <li>You must be at least 18 years old to use the Services.</li>
    <li>If you are using the Services on behalf of an entity, you confirm that you have legal authority to bind such entity.</li>
    <li>You agree to use the Services only for lawful purposes.</li>
</ul>
</div>

<hr>

<h2><strong>6. USER REGISTRATION & ACCOUNT SECURITY</strong></h2>
<div class="section-content">
<h3>6.1 Account Creation</h3>
<ul>
    <li>Certain features require account registration.</li>
    <li>You must provide accurate, complete, and current information.</li>
</ul>

<h3>6.2 Account Responsibility</h3>
<ul>
    <li>You are solely responsible for safeguarding your login credentials.</li>
    <li>Any activity performed through your account shall be deemed to be performed by you.</li>
</ul>

<h3>6.3 Account Suspension</h3>
<p>OOHAPP reserves the right to suspend, restrict, or terminate accounts without notice if misuse, fraud, policy violation, or legal risk is detected.</p>
</div>

<hr>

<h2><strong>7. INTELLECTUAL PROPERTY RIGHTS</strong></h2>
<div class="section-content">
<p>All intellectual property related to the Services including software, UI/UX, trademarks, logos, and databases are owned by or licensed to OOHAPP ADSPACE NETWORK LLP.</p>
</div>

<hr>

<h2><strong>8. USER CONTENT & CONTRIBUTIONS</strong></h2>
<div class="section-content">
<p>Any feedback, suggestions, ideas, or content submitted becomes the exclusive property of OOHAPP and may be used without compensation.</p>
</div>

<hr>

<h2><strong>9. VENDOR & ADVERTISER OBLIGATIONS</strong></h2>
<div class="section-content">
<p>Vendors and advertisers are solely responsible for legal compliance, approvals, accuracy, and execution of campaigns.</p>
</div>

<hr>

<h2><strong>10. PRICING, PAYMENTS & COMMISSIONS</strong></h2>
<div class="section-content">
<p>Prices are determined by vendors. OOHAPP is not responsible for disputes, refunds, or offline transactions.</p>
</div>

<hr>

<h2><strong>11. NO REFUND & CANCELLATION POLICY</strong></h2>
<div class="section-content">
<p>All bookings are final unless explicitly stated otherwise. Refund responsibility lies solely with the vendor.</p>
</div>

<hr>

<h2><strong>12. PROHIBITED ACTIVITIES</strong></h2>
<div class="section-content">
<p>Violation of platform rules may result in immediate termination and legal action.</p>
</div>

<hr>

<h2><strong>13. THIRD-PARTY SERVICES</strong></h2>
<div class="section-content">
<p>OOHAPP is not responsible for third-party content or platforms.</p>
</div>

<hr>

<h2><strong>14. PRIVACY & DATA PROTECTION</strong></h2>
<div class="section-content">
<p>Privacy Policy: <a href="https://www.oohapp.io/privacy-policy" target="_blank">https://www.oohapp.io/privacy-policy</a></p>
</div>

<hr>

<h2><strong>15. DISCLAIMER</strong></h2>
<div class="section-content">
<p>Services are provided “AS IS” and use is at your own risk.</p>
</div>

<hr>

<h2><strong>16. LIMITATION OF LIABILITY</strong></h2>
<div class="section-content">
<p>OOHAPP shall not be liable for indirect losses or disputes.</p>
</div>

<hr>

<h2><strong>17. INDEMNIFICATION</strong></h2>
<div class="section-content">
<p>You agree to indemnify OOHAPP against claims arising from misuse.</p>
</div>

<hr>

<h2><strong>18. TERMINATION</strong></h2>
<div class="section-content">
<p>OOHAPP may terminate access without notice.</p>
</div>

<hr>

<h2><strong>19. MODIFICATIONS</strong></h2>
<div class="section-content">
<p>Services may be modified or discontinued at any time.</p>
</div>

<hr>

<h2><strong>20. GOVERNING LAW & DISPUTE RESOLUTION</strong></h2>
<div class="section-content">
<p>
Governing Law: India<br>
Seat of Arbitration: Lucknow, Uttar Pradesh<br>
Language: English
</p>
</div>

<hr>

<h2><strong>21. ELECTRONIC COMMUNICATIONS</strong></h2>
<div class="section-content">
<p>You consent to electronic communications.</p>
</div>

<hr>

<h2><strong>22. SEVERABILITY & ENTIRE AGREEMENT</strong></h2>
<div class="section-content">
<p>These terms constitute the entire agreement.</p>
</div>

<hr>

<h2><strong>23. CONTACT INFORMATION</strong></h2>
<div class="section-content">
<p>
OOHAPP ADSPACE NETWORK LLP<br>
B-25, Vibhuti Khand Road, Gomti Nagar, Lucknow – 226010<br>
Email: <a href="mailto:support@oohapp.io">support@oohapp.io</a>
</p>
</div>

</div>

HTML,
            'sort_order' => 1,
            'is_active' => 1,
        ]);
    }
}
