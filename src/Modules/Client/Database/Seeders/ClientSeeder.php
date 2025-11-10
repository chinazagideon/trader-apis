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
     * Create the clients
     *
     * @return void
     */
    private function createClients(): void
    {
        $clients = [
            [
                'name' => 'Sample Client',
                'slug' => 'sample-client',
                'api_key' => Str::uuid(),
                'api_secret' => Str::uuid(),
                'config' => json_encode([
                    'guest_view_enabled' => false,
                    'auth_view_enabled' => true,
                ]),
                'features' => json_encode([]),
                'is_active' => true,
            ],
            [
                'name' => 'X Client 4 Trader',
                'slug' => 'x-client-4-trader',
                'api_key' => Str::uuid(),
                'api_secret' => Str::uuid(),
                'config' => json_encode([
                    'guest_view_enabled' => false,
                    'auth_view_enabled' => true,
                ]),
                'features' => json_encode([]),
                'is_active' => true,
            ],
        ];
        foreach ($clients as $client) {
            Client::create($client);
        }
    }
}
