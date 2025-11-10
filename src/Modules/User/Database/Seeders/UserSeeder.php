<?php

namespace App\Modules\User\Database\Seeders;

use App\Modules\User\Database\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin user
        User::updateOrCreate(
            ['email' => 'admin@x4trader.com'],
            [
                'name' => 'Admin User',
                'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'Secret2025')),
                'is_active' => true,
                'role_id' => 1,
                'email_verified_at' => now(),
                'client_id' => 1,
            ]
        );

        // Create test users
        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
                'is_active' => true,
                'email_verified_at' => now(),
                'role_id' => 2,
                'client_id' => 2,
            ]
        );

        User::updateOrCreate(
            ['email' => 'demo@example.com'],
            [
                'name' => 'Demo User',
                'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
                'is_active' => true,
                'email_verified_at' => now(),
                'role_id' => 2,
                'client_id' => 2,
            ]
        );
        User::UpdateOrCreate(
            ['email' => 'user@example.com'],
            [
                'name' => 'Demo Admin',
                'password' => Hash::make(env('DEFAULT_USER_PASSWORD', 'password')),
                'is_active' => true,
                'email_verified_at' => now(),
                'role_id' => 1,
                'client_id' => 1,
            ]
        );
    }
}
