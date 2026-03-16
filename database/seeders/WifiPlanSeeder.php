<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WifiPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \App\Models\WifiPlan::updateOrCreate(
            ['name' => 'Free Plan'],
            [
                'price' => 0.00,
                'duration_minutes' => 30,
                'upload_limit' => '512K',
                'download_limit' => '1M',
                'is_active' => true
            ]
        );
    }
}
