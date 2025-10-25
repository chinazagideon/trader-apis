<?php

namespace App\Modules\Market\Database\Seeders;

use App\Modules\Market\Database\Models\MarketPrice;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MarketPriceSeeder extends Seeder
{
    public function run(): void
    {
        $marketPrices = [
            [
                'market_id' => 1,
                'currency_id' => 1,
                'price' => 109500.00,
                'market_cap' => 2190000000000.00,
                'total_supply' => 20000000.00,
                'max_supply' => 21000000.00,
                'circulating_supply' => 11000000.00,
                'total_volume' => 1000000000000.00,
                'total_volume_24h' => 1000000000000.00,
                'total_volume_7d' => 1000000000000.00,
                'total_volume_30d' => 1000000000000.00,
                'total_volume_90d' => 1000000000000.00,
                'total_volume_180d' => 1000000000000.00,
            ],
            [
                'market_id' => 2,
                'currency_id' => 2,
                'price' => 4500,
                'market_cap' => 8000000000000.00,
                'total_supply' => 2000000000.00,
                'max_supply' => 12000000000.00,
                'circulating_supply' => 6000000000.00,
                'total_volume' => 1000000000000.00,
                'total_volume_24h' => 1000000000000.00,
                'total_volume_7d' => 1000000000000.00,
                'total_volume_30d' => 1000000000000.00,
                'total_volume_90d' => 1000000000000.00,
                'total_volume_180d' => 1000000000000.00,
            ],
            [
                'market_id' => 3,
                'currency_id' => 3,
                'price' => 1.00,
                'market_cap' => 1000000000.00,
                'total_supply' => 1000000000.00,
                'max_supply' => 1000000000.00,
                'circulating_supply' => 1000000000.00,
                'total_volume' => 1000000000.00,
                'total_volume_24h' => 1000000000.00,
                'total_volume_7d' => 1000000000.00,
                'total_volume_30d' => 1000000000.00,
                'total_volume_90d' => 1000000000.00,
                'total_volume_180d' => 1000000000.00,
            ],
        ];

        $seededCount = 0;
        foreach ($marketPrices as $marketPrice) {
            MarketPrice::create($marketPrice);
            $seededCount++;
        }
        if ($this->command) {
            $this->command->info('[MarketPriceSeeder] Successfully seeded ' . $seededCount . ' market prices');
        }
    }
}
