<?php

namespace App\Modules\Swap\Database\Seeders;

use App\Modules\Swap\Database\Models\Swap;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\Log;

class SwapSeeder extends Seeder
{

    public function run(): void
    {
        $faker = Faker::create();
        $swaps = [
            [
                'uuid' => Str::uuid(),
                'user_id' => 1,
                'from_currency_id' => 1,
                'to_currency_id' => 2,
                'from_amount' => 100,
                'to_amount' => 100,
                'fee_amount' => 10,
                'total_amount' => 110,
                'rate' => 1.1,
                'status' => 'completed',
                'notes' => $faker->sentence,
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => 2,
                'from_currency_id' => 2,
                'to_currency_id' => 1,
                'from_amount' => 100,
                'to_amount' => 100,
                'fee_amount' => 10,
                'total_amount' => 110,
                'rate' => 1.1,
                'status' => 'completed',
                'notes' => $faker->sentence,
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => 3,
                'from_currency_id' => 3,
                'to_currency_id' => 1,
                'from_amount' => 100,
                'to_amount' => 100,
                'fee_amount' => 10,
                'total_amount' => 110,
                'rate' => 1.1,
                'status' => 'completed',
                'notes' => $faker->sentence,
            ],
        ];

        $seededCount = 0;
        foreach ($swaps as $swap) {
            Swap::create($swap);
            $seededCount++;
        }
        Log::info('[SwapSeeder] Successfully seeded ' . $seededCount . ' swaps');
    }
}
