<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EmailLayout;

class EmailLayoutSeeder extends Seeder
{
    public function run(): void
    {
        EmailLayout::insert([
            [
                'logo_url'      => '/images/logo.png',
                'header_html'   => '
                    <div style="background:#2563eb;padding:20px;text-align:center;">
                        <img src="/images/logo.png" alt="Logo" style="height:40px;">
                    </div>
                ',
                'footer_html'   => '
                    <div style="background:#f3f4f6;padding:16px;text-align:center;font-size:12px;color:#6b7280;">
                        &copy; ' . date('Y') . ' Your Company. All rights reserved.
                    </div>
                ',
                'primary_color' => '#2563eb',
                'font_family'   => 'Arial',
                'is_active'     => true,
                'created_at'    => now(),
                'updated_at'    => now(),
            ],
        ]);
    }
}