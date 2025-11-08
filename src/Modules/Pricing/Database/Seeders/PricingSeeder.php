<?php

namespace App\Modules\Pricing\Database\Seeders;

use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Pricing\Database\Models\Pricing;
use Illuminate\Database\Seeder;

class PricingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $usdCurrency = Currency::where('code', 'USD')->first();

        if (!$usdCurrency) {
            $this->command->warn('USD currency not found. Please run CurrencySeeder first.');
            return;
        }

        $pricingPlans = [
            [
                'name' => 'Basic Trading',
                'min_amount' => 100.00,
                'max_amount' => 1000.00,
                'client_id' => 2,
                'contract' => 'monthly',
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'trade',
                'is_active' => true,
            ],
            [
                'name' => 'Professional Trading',
                'min_amount' => 1000.00,
                'max_amount' => 10000.00,
                'client_id' => 2,
                'contract' => 'monthly',
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'trade',
                'is_active' => true,
            ],
            [
                'name' => 'Enterprise Trading',
                'min_amount' => 10000.00,
                'max_amount' => 100000.00,
                'client_id' => 2,
                'contract' => 'yearly',
                'currency_id' => $usdCurrency->id,
                'lifespan' => 365,
                'type' => 'trade',
                'is_active' => true,
            ],
            [
                'name' => 'Basic Mining',
                'min_amount' => 500.00,
                'max_amount' => 5000.00,
                'client_id' => 2,
                'contract' => 'monthly',
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'mining',
                'is_active' => true,
            ],
        ];

        foreach ($pricingPlans as $plan) {
            Pricing::updateOrCreate(
                [
                    'name' => $plan['name'],
                    'type' => $plan['type'],
                ],
                $plan
            );
        }
    }
}
