<?php

namespace App\Modules\Payment\Database\Seeders;

use App\Modules\Payment\Database\Models\Payment;
use App\Modules\Currency\Database\Models\Currency;
use App\Modules\Transaction\Database\Models\Transaction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class PaymentSeeder extends Seeder
{
    public function run(): void
    {
        try {
            // Ensure dependencies run first
            $this->call(\App\Modules\Currency\Database\Seeders\CurrencySeeder::class);
            $this->call(\App\Modules\Transaction\Database\Seeders\TransactionSeeder::class);

            $currencies = Currency::limit(1)->get();
            $transactions = Transaction::limit(2)->get();

            if ($currencies->isEmpty()) {
                $this->command?->warn('No currencies found. Please run CurrencySeeder first.');
                return;
            }

            if ($transactions->isEmpty()) {
                $this->command?->warn('No transactions found. Please run TransactionSeeder first.');
                return;
            }

            $payments = [
                [
                    'payable_type' => 'transaction',
                    'payable_id' => $transactions->first()->id,
                    'status' => 'pending',
                    'amount' => 100,
                    'currency_id' => $currencies->first()->id,
                ]
            ];

            // Add second payment if we have multiple transactions
            if ($transactions->count() > 1) {
                $payments[] = [
                    'payable_type' => 'transaction',
                    'payable_id' => $transactions->last()->id,
                    'status' => 'pending',
                    'amount' => 1400,
                    'currency_id' => $currencies->first()->id,
                ];
            }
            $seededCount = 0;
            foreach ($payments as $payment) {
                Payment::create([
                    'payable_type' => $payment['payable_type'],
                    'payable_id' => $payment['payable_id'],
                    'status' => $payment['status'],
                    'amount' => $payment['amount'],
                    'currency_id' => $payment['currency_id'],
                ]);
                $seededCount++;
            }
            Log::info('[PaymentSeeder] Successfully processed ' . $seededCount . ' payment templates');
        } catch (\Exception $e) {
            Log::error('[PaymentSeeder] Seeding failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
