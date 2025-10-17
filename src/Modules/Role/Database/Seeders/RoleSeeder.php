<?php

namespace App\Modules\Role\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;
use App\Modules\Role\Database\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'admin',
                'description' => 'Admin role',
                'is_active' => true,
            ],
            [
                'name' => 'user',
                'description' => 'User role',
                'is_active' => true,
            ],
            [
                'name' => 'moderator',
                'description' => 'Moderator role',
                'is_active' => true,
            ],
            [
                'name' => 'banned',
                'description' => 'Banned role',
                'is_active' => true,
            ],
        ];
        $seededCount = 0;
        foreach ($roles as $role) {
            Role::create($role);
            $seededCount++;
        }
        Log::info('[RoleSeeder] Successfully seeded ' . $seededCount . ' roles');
    }
}
