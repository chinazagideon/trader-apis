<?php

namespace App\Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Client\Database\Models\Client;
use Illuminate\Support\Str;

class ClientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $this->createClients();
    }

    /**
     * Generate a WDR-formatted API key/secret
     *
     * @return string
     */
    private function generateWdrKey(): string
    {
        $segments = [];
        for ($i = 0; $i < 5; $i++) {
            $segments[] = strtoupper(Str::random(4));
        }
        return 'WDR-' . implode('-', $segments);
    }

    /**
     * Create the clients
     *
     * @return void
     */
    private function createClients(): void
    {
        $clients = [
            [
                'name' => 'sample client',
                'slug' => 'sample-client',
                'api_key' => Str::uuid(),
                'api_secret' => Str::uuid(),
                'config' => json_encode([
                    'guest_view_enabled' => false,
                    'auth_view_enabled' => true,
                    'app_url' => 'https://sample-client.com',
                    'app_name' => 'Sample Client',
                ]),
                'features' => json_encode([]),
                'is_active' => true,
            ],
            [
                'name' => 'X4 Trader',
                'slug' => 'x4trader',
                'api_key' => Str::uuid(),
                'api_secret' => Str::uuid(),
                'config' => json_encode([
                    'guest_view_enabled' => false,
                    'auth_view_enabled' => true,
                    'app_url' => 'https://app.x4trader.com',
                    'app_name' => 'X4 Trader',
                ]),
                'features' => json_encode([]),
                'is_active' => true,
            ],
            [
                'name' => 'Wordpress blog Auto Publisher Bot',
                'slug' => 'wordpress-blog-auto-publisher-bot',
                'api_key' => $this->generateWdrKey(),
                'api_secret' => $this->generateWdrKey(),
                'config' => json_encode([
                    'guest_view_enabled' => false,
                    'auth_view_enabled' => true,
                    'app_url' => '#',
                    'app_name' => 'Wordpress blog Auto Publisher Bot',
                    'max_sites' => 1,
                    'expires_at' => '2026-12-31',
                    'customer_email' => 'x4bot@vesper-a.com',
                ]),
                'features' => json_encode(['domain_analysis', 'bulk_processing', 'autopilot']),
                'is_active' => true,
            ]
        ];
        foreach ($clients as $client) {
            Client::updateOrCreate(['slug' => $client['slug']],$client);
        }
    }
}
