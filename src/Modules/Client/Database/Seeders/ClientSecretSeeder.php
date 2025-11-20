<?php

namespace App\Modules\Client\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use App\Modules\Client\Database\Models\ClientSecret;

class ClientSecretSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $clientSecrets = $this->secrets();
        foreach ($clientSecrets as $secret) {
            ClientSecret::updateOrCreate(
                [
                    'client_id' => $secret['client_id'],
                    'module_name' => $secret['module_name'],
                ],
                $secret
            );
        }
    }

    /**
     * Get the client secrets
     * @return array
     */
    protected function secrets(): array
    {

        return [
            [
                'client_id' => 1,
                'module_name' => 'notification',
                'is_active' => true,
                'secrets' => json_encode([
                    'mail_identity' => [
                        'MAIL_FROM_NAME' => Str::random(10),
                        'MAIL_FROM_ADDRESS' => Str::random(10) . '@example.com',
                    ],
                ]),
            ],
        ];
    }
}
