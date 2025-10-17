<?php

namespace App\Modules\Swap\Database\Seeders;

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use App\Modules\Swap\Database\Models\SwapTransaction;
use Illuminate\Support\Facades\Log;
use App\Modules\Transaction\Database\Models\Transaction;

class SwapTransactionSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $data = [
            [
                'swap_id' => 1,
                'transaction_id' => 1,
                'type' => 'debit',
            ],
            [
                'swap_id' => 2,
                'transaction_id' => 2,
                'type' => 'credit',
            ],
            [
                'swap_id' => 3,
                'transaction_id' => 3,
                'type' => 'fee',
            ]
        ];
        $seededCount = 0;
        foreach ($data as $item) {
            SwapTransaction::create($item);
            $seededCount++;
        }
        Log::info('[SwapTransactionSeeder] Successfully seeded ' . $seededCount . ' swap transactions');
    }
}
