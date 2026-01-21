<?php

namespace Database\Seeders;

use App\Models\AboutPage;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class AboutPageSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutPage::truncate();

        AboutPage::create([
            'hero_title' => 'About OOHAPP',

            // ✅ ONLY DESCRIPTION (name/designation removed)
            'hero_description' => '<em>
He has a wealth of experience in developing and executing successful campaigns in challenging environments.
He is a strong leader, with a focus on creating a collaborative and innovative culture.
He has a deep understanding of the nuances of the Outdoor Advertising industry, and is comfortable working
with clients, partners, and vendors. He is driven by results, and is committed to delivering high-quality work.
</em>',

            'section_title' => 'About OOHAPP',

            'section_content' => <<<'HTML'
<p>
Welcome to OOHAPP, your trusted partner in innovative advertising solutions. Established in 2022, we specialize in creating impactful and eye-catching advertisement hoardings that effectively connect brands with their audiences.
</p>

<p>
Our mission is to revolutionize outdoor advertising by offering high-quality, strategically placed hoardings that help businesses enhance their visibility, drive engagement, and grow their brand presence. With a focus on creativity, technology, and reliability, we ensure that every campaign we execute delivers measurable results.
</p>

<p>
At OOHAPP, we understand the power of first impressions, and we work tirelessly to craft visual experiences that leave a lasting impact. Whether you're a small startup or an established enterprise, we provide customized advertising solutions tailored to meet your specific needs and goals.
</p>

<p>
Join us in transforming the way you reach your customers, and let us help you make your message stand out where it matters most.
</p>
HTML,

            'hero_image' => '/images/about/praveen-kumar-rastogi.jpg',
            'section_image' => '/images/about/office.jpg',
        ]);
    }
}
