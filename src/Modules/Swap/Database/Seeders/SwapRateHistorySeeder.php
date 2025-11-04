<?php

namespace App\Modules\Swap\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Swap\Database\Models\SwapRateHistory;
use Illuminate\Support\Facades\Log;


class SwapRateHistorySeeder extends Seeder
{

    public function run(): void
    {


        $data = [
            [
                'from_currency_id' => 1,
                'to_currency_id' => 2,
                'rate' => 1.0,
                'spread' => 0.0,
                'source' => 'internal',
                'metadata' => json_encode([]),
            ],
            // [
            //     'from_currency_id' => 2,
            //     'to_currency_id' => 1,
            //     'rate' => 1.0,
            //     'spread' => 0.0,
            //     'source' => 'internal',
            //     'metadata' => json_encode([]),
            // ],
            // [
            //     'from_currency_id' => 3,
            //     'to_currency_id' => 1,
            //     'rate' => 1.0,
            //     'spread' => 0.0,
            //     'source' => 'internal',
            //     'metadata' => json_encode([]),
            // ],
            // [
            //     'from_currency_id' => 1,
            //     'to_currency_id' => 3,
            //     'rate' => 1.0,
            //     'spread' => 0.0,
            //     'source' => 'internal',
            //     'metadata' => json_encode([]),
            // ],
        ];

        $seededCount = 0;
        foreach ($data as $item) {
            SwapRateHistory::create($item);
            $seededCount++;
        }
        Log::info('[SwapRateHistorySeeder] Successfully seeded ' . $seededCount . ' swap rate histories');
    }
}
