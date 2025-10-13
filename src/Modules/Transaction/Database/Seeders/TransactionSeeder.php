<?php

namespace App\Modules\Transaction\Database\Seeders;

use App\Modules\Category\Database\Models\Category;
use App\Modules\Transaction\Database\Models\TransactionCategory;
use App\Modules\Transaction\Database\Models\Transaction;
use App\Modules\User\Database\Models\User;
use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Modules\Currency\Database\Models\Currency;

class TransactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Log::info('[TransactionSeeder] Starting transaction seeding');

        try {
            // Get existing users and investments for relationships
            $users = User::limit(10)->get();
            $investments = Investment::limit(5)->get();
            if ($users->isEmpty()) {
                Log::warning('[TransactionSeeder] No users found. Creating sample user first');
                $user = User::create([
                    'name' => 'Test User',
                    'email' => 'test@example.com',
                    'password' => bcrypt('password'),
                    'is_active' => true,
                ]);
                $users = collect([$user]);
            }

            Log::info('[TransactionSeeder] Found ' . $users->count() . ' users for transaction seeding');

            $transactionTypes = ['withdrawal', 'deposit', 'transfer', 'fee', 'interest'];
            $entryTypes = ['credit', 'debit'];
            $statuses = ['pending', 'completed', 'failed'];

            $seededCount = 0;

            // Create transactions for users
            foreach ($users as $user) {
                for ($i = 0; $i < 3; $i++) {
                    $transaction = Transaction::create([
                        'transactable_id' => $user->id,
                        'transactable_type' => 'user',
                        'narration' => 'Sample transaction #' . ($i + 1) . ' for ' . $user->name,
                        'entry_type' => $entryTypes[array_rand($entryTypes)],
                        'total_amount' => rand(100, 10000) / 100,
                        'status' => $statuses[array_rand($statuses)],
                    ]);

                    // Create transaction category
                    TransactionCategory::create([
                        'transaction_id' => $transaction->id,
                        'category_id' => rand(1, 5),
                    ]);

                    $seededCount++;
                }
            }

            // Create transactions for investments if available
            if ($investments->isNotEmpty()) {
                foreach ($investments as $investment) {
                    $transaction = Transaction::create([
                        'transactable_id' => $investment->id,
                        'transactable_type' => 'investment',
                        'narration' => 'Investment transaction for investment #' . $investment->id,
                        'entry_type' => $entryTypes[array_rand($entryTypes)],
                        'total_amount' => rand(1000, 50000) / 100,
                        'status' => $statuses[array_rand($statuses)],
                    ]);


                    TransactionCategory::create([
                        'transaction_id' => $transaction->id,
                        'category_id' => rand(1, 5),
                    ]);



                    $seededCount++;
                }
            }

            Log::info('[TransactionSeeder] Successfully seeded ' . $seededCount . ' transactions');

        } catch (\Exception $e) {
            Log::error('[TransactionSeeder] Seeding failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}

