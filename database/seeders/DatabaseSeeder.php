<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // ✅ Official Admin Caste Credentials
        User::factory()->create([
            'name' => 'Admin Boss',
            'email' => 'admin@1209.com',
            'password' => bcrypt('admin@123')
        ]);
    }
}
