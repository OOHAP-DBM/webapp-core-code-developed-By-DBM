<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class HoardingAttributesSeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Billboard', 'slug' => 'billboard'],
            ['name' => 'Unipole', 'slug' => 'unipole'],
            ['name' => 'Gantries', 'slug' => 'gantries'],
            ['name' => 'Bus Shelter', 'slug' => 'bus-shelter'],
            ['name' => 'Bridge Panel', 'slug' => 'bridge-panel'],
            ['name' => 'Airport', 'slug' => 'airport'],
            ['name' => 'Mall', 'slug' => 'mall'],
            ['name' => 'Metro', 'slug' => 'metro'],
            ['name' => 'Other', 'slug' => 'other'],
        ];

        foreach ($categories as $cat) {
            DB::table('hoarding_attributes')->updateOrInsert(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'slug' => $cat['slug'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
