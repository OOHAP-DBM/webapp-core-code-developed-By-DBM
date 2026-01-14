<?php

namespace Database\Seeders;

use App\Models\TermsAndCondition;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TermsAndConditionsSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        TermsAndCondition::truncate();

        TermsAndCondition::create([
            'section_title' => 'Terms And Conditions',
            'content' => <<<'HTML'
<h2>Welcome OOHAPP</h2>

<p>
These terms and conditions outline the rules and regulations for the use of OOHAPP.
By using this app we assume you accept these terms and conditions.
</p>

<p>
We are <strong>OOHAPP</strong> ("Company", "we", "us", "our"), a company registered in India at
B25 Vibhuti Khand Road, Gomti Nagar, Lucknow, Uttar Pradesh.
</p>

<h3><strong>License</strong></h3>
<ul>
    <li>Republish Material from OOHAPP</li>
    <li>Sell, rent or sub-license material</li>
    <li>Reproduce or duplicate content</li>
    <li>Redistribute content</li>
</ul>

<h3><strong>Governing Law</strong></h3>
<p>
These Legal Terms shall be governed by the laws of India.
</p>

<h3><strong>Contact Us</strong></h3>
<p>
Email: <a href="mailto:support@oohapp.io">support@oohapp.io</a>
</p>
HTML,
            'sort_order' => 1,
            'is_active' => 1,
        ]);
    }
}
