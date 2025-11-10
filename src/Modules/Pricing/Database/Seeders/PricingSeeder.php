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
                'name' => 'Starter',
                'min_amount' => 1000.00,
                'max_amount' => 99999.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'trade',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '9.0%',
                    'play_category' => 'Ideal for Beginners',
                    'risk_tolerance' => 'Low-Risk Introduction',
                ]),
                'roi' => 9.00,
            ],
            [
                'name' => 'Pro',
                'min_amount' => 100000.00,
                'max_amount' => 999999.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'trade',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '18.5%',
                    'play_category' => 'Medium-Term Growth',
                    'risk_tolerance' => 'For Experienced Investors',
                ]),
                'roi' => 18.50,
            ],
            [
                'name' => 'Enterprise',
                'min_amount' => 1000000.00,
                'max_amount' => 49999999.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 90,
                'type' => 'trade',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '28.0%',
                    'play_category' => 'Long-Term Growth',
                    'risk_tolerance' => 'High-Risk High-Reward',
                ]),
                'roi' => 28.00,
            ],
            [
                'name' => 'Ultimate',
                'min_amount' => 50000000.00,
                'max_amount' => 100000000.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 365,
                'type' => 'trade',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '40.0%',
                    'play_category' => 'Moderate Risk and Return',
                    'risk_tolerance' => 'Ideal for Conservative Investors',
                ]),
                'roi' => 40.00,
            ],
            [
                'name' => 'Staking Starter',
                'min_amount' => 100.00,
                'max_amount' => 1999.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 15,
                'type' => 'staking',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '7.0%',
                ]),
                'roi' => 7.00,
            ],
            [
                'name' => 'Staking Pro',
                'min_amount' => 2000.00,
                'max_amount' => 10000.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 30,
                'type' => 'staking',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '12.0%',
                ]),
                'roi' => 12.00,
            ],
            [
                'name' => 'Staking Enterprise',
                'min_amount' => 15000.00,
                'max_amount' => 199999.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 45,
                'type' => 'staking',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '18.0%',
                ]),
                'roi' => 18.00,
            ],
            [
                'name' => 'Staking Ultimate',
                'min_amount' => 200000.00,
                'max_amount' => 1000000.00,
                'client_id' => 1,
                'currency_id' => $usdCurrency->id,
                'lifespan' => 60,
                'type' => 'staking',
                'is_active' => true,
                'benefits' => json_encode([
                    'returns'=> '20.0%',
                ]),
                'roi' => 20.00,
            ],
        ];

        foreach ($pricingPlans as $plan) {
            Pricing::create(
                $plan
            );
        }
    }
}
