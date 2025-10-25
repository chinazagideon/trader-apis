<?php

namespace App\Modules\Market\Database\Seeders;

use App\Modules\Market\Database\Models\Market;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class MarketSeeder extends Seeder
{
    public function run(): void
    {
        $markets = [
            [
                'name' => 'Bitcoin',
                'description' => 'Bitcoin is a digital currency that is created and distributed through a peer-to-peer network. It is based on the blockchain technology and is not controlled by any central authority.',
                'image' => 'https://assets.coingecko.com/coins/images/1/large/bitcoin.png?1696501628',
                'url' => 'https://bitcoin.org',
                'slug' => 'bitcoin',
                'status' => 'active',
                'type' => 'spot',
                'category' => 'crypto',
                'subcategory' => 'bitcoin',
            ],
            [
                'name' => 'Ethereum',
                'description' => 'Ethereum is a digital currency that is created and distributed through a peer-to-peer network. It is based on the blockchain technology and is not controlled by any central authority.',
                'image' => 'https://assets.coingecko.com/coins/images/279/large/ethereum.png?1696501628',
                'url' => 'https://ethereum.org',
                'slug' => 'ethereum',
                'status' => 'active',
                'type' => 'spot',
                'category' => 'crypto',
                'subcategory' => 'ethereum',
            ],
            [
                'name' => 'Tether',
                'description' => 'Tether is a digital currency that is created and distributed through a peer-to-peer network. It is based on the blockchain technology and is not controlled by any central authority.',
                'image' => 'https://assets.coingecko.com/coins/images/325/large/tether.png?1696501628',
                'url' => 'https://tether.to',
                'slug' => 'tether',
                'status' => 'active',
                'type' => 'spot',
                'category' => 'crypto',
                'subcategory' => 'tether',
            ],
        ];
        $seededCount = 0;
        foreach ($markets as $market) {
            Market::create($market);
            $seededCount++;
        }
        if ($this->command) {
            $this->command->info('[MarketSeeder] Successfully seeded ' . $seededCount . ' markets');
        }
    }
}
