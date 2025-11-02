<?php

namespace App\Modules\Withdrawal\Database\Seeders;

use App\Modules\Withdrawal\Database\Models\Withdrawal;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Faker\Factory as Faker;

class WithdrawalSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $withdrawals = [
            [
                'uuid' => Str::uuid(),
                'user_id' => 1,
                'withdrawable_id' => 1,
                'withdrawable_type' => 'user',
                'amount' => 100,
                'currency_id' => 1,
                'status' => 'pending',
                'notes' => $faker->sentence,

            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => 2,
                'withdrawable_id' => 1,
                'withdrawable_type' => 'user',
                'amount' => 200,
                'currency_id' => 1,
                'status' => 'completed',
                'notes' => $faker->sentence,
            ],
            [
                'uuid' => Str::uuid(),
                'user_id' => 3,
                'withdrawable_id' => 1,
                'withdrawable_type' => 'user',
                'amount' => 300,
                'currency_id' => 1,
                'status' => 'cancelled',
                'notes' => $faker->sentence,
            ],
        ];

        $seededCount = 0;
        foreach ($withdrawals as $withdrawal) {
            Withdrawal::create($withdrawal);
            $seededCount++;
        }
        Log::info('[WithdrawalSeeder] Successfully seeded ' . $seededCount . ' withdrawals');
    }
}
