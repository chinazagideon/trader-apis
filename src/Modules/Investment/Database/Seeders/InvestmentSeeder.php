<?php

namespace App\Modules\Investment\Database\Seeders;

use App\Modules\Investment\Database\Models\Investment;
use Illuminate\Database\Seeder;

class InvestmentSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure dependencies run first
        $this->call(\App\Modules\Pricing\Database\Seeders\PricingSeeder::class);
        $this->call(\App\Modules\User\Database\Seeders\UserSeeder::class);

        // Get the first available pricing plan
        $pricing = \App\Modules\Pricing\Database\Models\Pricing::first();

        if (!$pricing) {
            $this->command?->warn('No pricing plans found after running PricingSeeder.');
            return;
        }

        // Get available users
        $users = \App\Modules\User\Database\Models\User::all();

        if ($users->isEmpty()) {
            $this->command?->warn('No users found after running UserSeeder.');
            return;
        }

        $investments = [
            [
                'notes' => 'Investment',
                'user_id' => $users->first()->id,
                'pricing_id' => $pricing->id,
                'amount' => 1000,
                'status' => 'running',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ],
        ];

        // Add more investments if we have multiple users
        if ($users->count() > 1) {
            $investments[] = [
                'notes' => 'Investment',
                'user_id' => $users->last()->id,
                'pricing_id' => $pricing->id,
                'amount' => 1000,
                'status' => 'running',
                'start_date' => now(),
                'end_date' => now()->addDays(30),
            ];
        }

        foreach ($investments as $investment) {
            Investment::create($investment);
        }
    }
}
