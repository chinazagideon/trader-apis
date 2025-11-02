<?php

namespace App\Modules\Market\Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Run seeders in the correct order
        $this->call([
            MarketSeeder::class,        // Create markets first
            MarketPriceSeeder::class,   // Then create market prices
        ]);
    }
}
