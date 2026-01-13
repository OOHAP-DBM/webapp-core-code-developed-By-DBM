<?php

namespace Database\Seeders;

use App\Models\AboutLeader;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class AboutLeaderSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AboutLeader::truncate();

        AboutLeader::create([
            'name' => 'Sushil Kumar Gautam',
            'designation' => 'Chief Executive Officer, OOHAPP',
            'bio' => 'Sushil is the CEO of an internationally recognized outdoor advertising firm. He has a strong background in business development, marketing, and operations, and has successfully navigated the complex landscape of the outdoor advertising industry. He is a strategic thinker, with a passion for driving efficiency, and a commitment to delivering on the company\'s mission. He has a successful track record of developing and executing innovative strategies.',
            'image' => '/images/about/leader.jpg',
            'sort_order' => 1,
        ]);
    }
}
