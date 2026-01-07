<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class HoardingAttributesSeeder extends Seeder
{
    public function run()
    {
        $attributes = [
            // Type: category
            ['type' => 'category', 'label' => 'Billboard', 'value' => 'billboard', 'is_active' => true],
            ['type' => 'category', 'label' => 'Unipole', 'value' => 'unipole', 'is_active' => true],
            ['type' => 'category', 'label' => 'Gantries', 'value' => 'gantries', 'is_active' => true],
            ['type' => 'category', 'label' => 'Bus Shelter', 'value' => 'bus-shelter', 'is_active' => true],
            ['type' => 'category', 'label' => 'Bridge Panel', 'value' => 'bridge-panel', 'is_active' => true],
            ['type' => 'category', 'label' => 'Airport', 'value' => 'airport', 'is_active' => true],
            ['type' => 'category', 'label' => 'Mall', 'value' => 'mall', 'is_active' => true],
            ['type' => 'category', 'label' => 'Metro', 'value' => 'metro', 'is_active' => true],
            ['type' => 'category', 'label' => 'Other', 'value' => 'other', 'is_active' => true],
            // Add more types (material, lighting, etc.) as needed
        ];

        foreach ($attributes as $attr) {
            DB::table('hoarding_attributes')->updateOrInsert(
                [
                    'type' => $attr['type'],
                    'value' => $attr['value'],
                ],
                [
                    'type' => $attr['type'],
                    'label' => $attr['label'],
                    'value' => $attr['value'],
                    'is_active' => $attr['is_active'],
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]
            );
        }
    }
}
