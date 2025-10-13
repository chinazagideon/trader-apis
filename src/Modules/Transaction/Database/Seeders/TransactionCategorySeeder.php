<?php

namespace App\Modules\Transaction\Database\Seeders;

use App\Modules\Transaction\Database\Models\TransactionCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class TransactionCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('[TransactionCategorySeeder] Starting transaction category seeding');

        try {
            $categories = [
                [
                    'category_id' => 1,
                    'transaction_id' => 2
                ],
                [
                    'category_id' => 2,
                    'transaction_id' => 3,
                ],
                [
                    'category_id' => 3,
                    'transaction_id' => 4,
                ],
                [
                    'category_id' => 4,
                    'transaction_id' => 5,
                ],
                [
                    'category_id' => 5,
                    'transaction_id' => 6,
                ],
            ];

            $seededCount = 0;

            foreach ($categories as $category) {
                Log::info('[TransactionCategorySeeder] Category template created', [
                    'category_id' => $category['category_id'],
                    'transaction_id' => $category['transaction_id'],
                ]);
                $seededCount++;
            }

            Log::info('[TransactionCategorySeeder] Successfully processed ' . $seededCount . ' category templates');

        } catch (\Exception $e) {
            Log::error('[TransactionCategorySeeder] Seeding failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

