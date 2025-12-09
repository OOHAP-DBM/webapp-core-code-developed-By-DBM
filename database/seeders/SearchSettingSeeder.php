<?php

namespace Database\Seeders;

use App\Models\SearchRankingSetting;
use Illuminate\Database\Seeder;

class SearchSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default search ranking settings if not exists
        if (SearchRankingSetting::count() === 0) {
            SearchRankingSetting::create(SearchRankingSetting::getDefaults());
            
            $this->command->info('✓ Default search ranking settings created');
        } else {
            $this->command->info('• Search ranking settings already exist, skipping...');
        }
    }
}
