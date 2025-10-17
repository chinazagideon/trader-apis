<?php

namespace App\Modules\Currency\Database\Seeders;

use App\Modules\Currency\Database\Models\Currency;
use Illuminate\Database\Seeder;

class CurrencySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $currencies = [
            [
                'name' => 'US Dollar',
                'symbol' => '$',
                'code' => 'USD',
                'type' => 'fiat',
                'is_default' => true,
            ],
            [
                'name' => 'Euro',
                'symbol' => '€',
                'code' => 'EUR',
                'type' => 'fiat',
                'is_default' => false,
            ],
            [
                'name' => 'British Pound',
                'symbol' => '£',
                'code' => 'GBP',
                'type' => 'fiat',
                'is_default' => false,
            ],
            [
                'name' => 'Japanese Yen',
                'symbol' => '¥',
                'code' => 'JPY',
                'type' => 'fiat',
                'is_default' => false,
            ],
            [
                'name' => 'Bitcoin',
                'symbol' => '₿',
                'code' => 'BTC',
                'type' => 'crypto',
                'is_default' => false,
            ],
        ];

        foreach ($currencies as $currency) {
            Currency::updateOrCreate(
                ['code' => $currency['code']],
                $currency
            );
        }
    }
}
