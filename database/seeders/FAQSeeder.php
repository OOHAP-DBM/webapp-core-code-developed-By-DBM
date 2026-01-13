<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FAQSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('faqs')->truncate();

        DB::table('faqs')->insert([
            [
                'question' => 'What is Out of Home Media?',
                'answer' => '<p>Out of home, or OOH, advertising is any form of advertising that reaches consumers outside of their homes. This includes billboards, transit advertising, outdoor displays, and digital screens in public spaces. OOH advertising is a powerful way to reach a large audience in high-traffic locations and create brand awareness.</p>',
                'is_active' => 1,
                'sort_order' => 1,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'What are the different types of Out of Home Advertising?',
                'answer' => '<p>There are several types of OOH advertising, including:</p>
<ul>
    <li><strong>Billboards:</strong> Large format displays in high-traffic areas</li>
    <li><strong>Transit Advertising:</strong> Ads on buses, trains, and taxis</li>
    <li><strong>Digital Displays:</strong> LED and LCD screens in public spaces</li>
    <li><strong>Street Furniture:</strong> Ads on bus shelters, kiosks, and phone booths</li>
    <li><strong>Outdoor Banners:</strong> Temporary or permanent banners on buildings</li>
</ul>',
                'is_active' => 1,
                'sort_order' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Why is OOH effective?',
                'answer' => '<p>OOH advertising is effective because it reaches consumers in their daily lives, often when they are in a receptive mindset. Studies show that OOH ads reach a large, diverse audience and create high brand recall. Additionally, OOH advertising is cost-effective compared to other forms of media and can target specific geographic locations.</p>',
                'is_active' => 1,
                'sort_order' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Which Brands Use OOH Media?',
                'answer' => '<p>OOH media is used by brands across industries including consumer goods, automotive, technology, healthcare, hospitality, and entertainment. Major brands like Coca-Cola, McDonald\'s, Nike, Apple, and Airbnb regularly use OOH advertising to reach consumers and build brand awareness on a large scale.</p>',
                'is_active' => 1,
                'sort_order' => 4,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Who Installs and Manages OOH Ads?',
                'answer' => '<p>OOH advertisements are installed and managed by specialized vendors and media companies. Property owners lease their space to these vendors, who handle the design, installation, and maintenance of the advertisements. Many vendors now offer digital management tools to make the process easier and more transparent for advertisers.</p>',
                'is_active' => 1,
                'sort_order' => 5,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'Where are most OOH ads placed?',
                'answer' => '<p>OOH ads are commonly placed in high-traffic locations such as highways, busy streets, shopping centers, transit hubs, and city centers. These locations ensure maximum visibility and exposure to a large number of people. Strategic placement is key to maximizing the effectiveness of OOH advertising campaigns.</p>',
                'is_active' => 1,
                'sort_order' => 6,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'question' => 'How much does OOH advertising cost?',
                'answer' => '<p>The cost of OOH advertising depends on factors such as location, size of the display, duration of the campaign, and type of medium. High-traffic locations and premium placements command higher rates. Costs can range from a few hundred rupees per month for small displays to several lakhs for prime billboard locations. OOHAPP helps you find affordable options that fit your budget.</p>',
                'is_active' => 1,
                'sort_order' => 7,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
