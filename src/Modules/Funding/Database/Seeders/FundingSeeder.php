<?php

namespace App\Modules\Funding\Database\Seeders;

use App\Modules\Funding\Database\Models\Funding;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class FundingSeeder extends Seeder
{
    public function run(): void
    {
        $fundings = [
            [
                'uuid' => Str::uuid(),
                'fundable_id' => 1,
                'user_id' => 1,
                'fundable_type' => 'user',
                'amount' => 100,
                'currency_id' => 1,
                'status' => 'pending',
            ],
            [
                'uuid' => Str::uuid(),
                'fundable_id' => 3,
                'user_id' => 3,
                'fundable_type' => 'user',
                'amount' => 300,
                'currency_id' => 1,
                'status' => 'cancelled',
            ],
            [
                'uuid' => Str::uuid(),
                'fundable_id' => 1,
                'user_id' => 4,
                'fundable_type' => 'swap',
                'amount' => 400,
                'currency_id' => 1,
                'status' => 'completed',
            ],
            [
                'uuid' => Str::uuid(),
                'fundable_id' => 5,
                'user_id' => 2,
                'fundable_type' => 'withdrawal',
                'amount' => 500,
                'currency_id' => 1,
                'status' => 'completed',
            ],
        ];
        $seededCount = 0;
        foreach ($fundings as $funding) {
            Funding::create($funding);
            $seededCount++;
        }
        Log::info('[FundingSeeder] Successfully seeded ' . $seededCount . ' fundings');
    }
}
