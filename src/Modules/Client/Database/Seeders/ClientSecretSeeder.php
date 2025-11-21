<?php

namespace App\Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Client\Database\Models\ClientSecret;

class ClientSecretSeeder extends Seeder
{
    public function run(): void
    {
        $clientSecrets = $this->secrets();
        foreach ($clientSecrets as $secret) {
            ClientSecret::updateOrCreate(
                [
                    'client_id' => $secret['client_id'],
                    'module_name' => $secret['module_name'],
                    'action' => $secret['action'],
                ],
                [
                    'secrets' => $secret['secrets'] ?? null,
                    'is_active' => $secret['is_active'] ?? true,
                ]
            );
        }
    }

    protected function secrets(): array
    {
        $client = \App\Modules\Client\Database\Models\Client::first();
        return [
            // Notification module - Mail action
            [
                'client_id' => $client->id,
                'module_name' => 'notification',
                'action' => 'mail',
                'secrets' => [
                    'from_name' => $client->name ?? 'Test Client',
                    'from_email' => $client->email ?? 'noreply@clienturl.com',
                    'reply_to_email' => $client->email ?? 'support@clienturl.com',
                    'reply_to_name' => $client->name ?? 'Test Client',
                ],
                'is_active' => true,
            ],
            // Notification module - SMS action
            [
                'client_id' => $client->id,
                'module_name' => 'notification',
                'action' => 'sms',
                'secrets' => [
                    'from_name' => $client->name ?? 'Test Client',
                    'from_phone' => $client->phone ?? null,
                ],
                'is_active' => true,
            ],
            // Payment module - Gateway action
            [
                'client_id' => $client->id,
                'module_name' => 'payment',
                'action' => 'gateway',
                'secrets' => [
                    'api_key' => 'gateway_api_key',
                    'api_secret' => 'gateway_api_secret',
                ],
                'is_active' => true,
            ],
        ];
    }
}
