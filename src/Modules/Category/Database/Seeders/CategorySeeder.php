<?php

namespace App\Modules\Category\Database\Seeders;

use App\Modules\Category\Database\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // Investment Categories
            [
                'name' => 'investment_basic',
                'description' => 'Basic investment transactions',
                'type' => 'income',
                'entity_types' => ['investment'],
                'operations' => ['create'],
                'metadata' => ['pricing_type' => 'Basic Trading']
            ],


            // User Categories
            [
                'name' => 'user_registration',
                'description' => 'User registration transactions',
                'type' => 'income',
                'entity_types' => ['user'],
                'operations' => ['create'],
                'metadata' => ['action' => 'registration']
            ],
            [
                'name' => 'user_update',
                'description' => 'User profile update transactions',
                'type' => 'income',
                'entity_types' => ['user'],
                'operations' => ['update'],
                'metadata' => ['action' => 'update']
            ],

            // Payment Categories
            [
                'name' => 'payment_deposit',
                'description' => 'Payment deposit transactions',
                'type' => 'income',
                'entity_types' => ['payment'],
                'operations' => ['create'],
                'metadata' => ['payment_type' => 'deposit']
            ],
            [
                'name' => 'payment_withdrawal',
                'description' => 'Payment withdrawal transactions',
                'type' => 'expense',
                'entity_types' => ['payment'],
                'operations' => ['create'],
                'metadata' => ['payment_type' => 'withdrawal']
            ],
            [
                'name' => 'payment_transfer',
                'description' => 'Payment transfer transactions',
                'type' => 'expense',
                'entity_types' => ['payment'],
                'operations' => ['create'],
                'metadata' => ['payment_type' => 'transfer']
            ],
            [
                'name' => 'payment_fee',
                'description' => 'Payment fee transactions',
                'type' => 'expense',
                'entity_types' => ['payment'],
                'operations' => ['create'],
                'metadata' => ['payment_type' => 'fee']
            ],

            // General Categories
            [
                'name' => 'general',
                'description' => 'General transactions',
                'type' => 'income',
                'entity_types' => null, // Supports all entity types
                'operations' => null,    // Supports all operations
                'metadata' => ['fallback' => true]
            ],
            [
                'name' => 'create',
                'description' => 'Generic create operations',
                'type' => 'income',
                'entity_types' => null,
                'operations' => ['create'],
                'metadata' => ['generic' => true]
            ],
            [
                'name' => 'update',
                'description' => 'Generic update operations',
                'type' => 'income',
                'entity_types' => null,
                'operations' => ['update'],
                'metadata' => ['generic' => true]
            ],
        ];

        foreach ($categories as $categoryData) {
            Category::create(array_merge($categoryData, [
                'created_by' => $this->getSystemUserIdOrNull(),
                'status' => 'active',
                'color' => $this->getColorForType($categoryData['type']),
                'icon' => $this->getIconForCategory($categoryData['name']),
            ]));
        }
    }

    /**
     * Get color for category type
     */
    private function getColorForType(string $type): string
    {
        return $type === 'income' ? '#10B981' : '#EF4444'; // Green for income, red for expense
    }

    /**
     * Get icon for category
     */
    private function getIconForCategory(string $name): string
    {
        $icons = [
            'investment' => 'trending-up',
            'user' => 'user',
            'payment' => 'credit-card',
            'general' => 'circle',
            'create' => 'plus',
            'update' => 'edit',
        ];

        foreach ($icons as $key => $icon) {
            if (str_contains($name, $key)) {
                return $icon;
            }
        }

        return 'circle';
    }

    /**
     * Get system user ID or null if no users exist
     */
    private function getSystemUserIdOrNull(): ?int
    {
        try {
            $systemUser = \App\Modules\User\Database\Models\User::firstOrCreate(
                ['email' => 'system@trader-apis.com'],
                [
                    'name' => 'System User',
                    'password' => bcrypt('system'),
                    'email_verified_at' => now(),
                ]
            );

            return $systemUser->id;
        } catch (\Exception $e) {
            // If users table doesn't exist or no users, return null
            return null;
        }
    }
}
