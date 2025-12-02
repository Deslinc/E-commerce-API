<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     * This runs all our seeders in order
     */
    public function run(): void
    {
        // Call our custom seeders
        $this->call([
            AdminUserSeeder::class,
            ProductSeeder::class,
        ]);
    }
}