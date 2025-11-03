<?php

namespace App\Modules\Payment\Database\Seeders;

use Illuminate\Database\Seeder;
use App\Modules\Payment\Database\Models\PaymentGateway;
use Illuminate\Support\Facades\Log;

class PaymentGatewaySeeder extends Seeder
{
    public function run(): void
    {
        try {
            $paymentGateways = [
                [
                    'name' => 'PayPal',
                    'slug' => 'paypal',
                    'description' => 'PayPal is a payment gateway that allows you to accept payments online.',
                    'mode' => 'live',
                    'type' => 'fiat',
                    'is_traditional' => false,
                    'instructions' => json_encode([]),
                    'supported_currencies' => json_encode([]),
                    'credentials' => json_encode([
                        'api_key' => '1234567890',
                        'api_secret' => '1234567890',
                        'api_url' => 'https://api.paypal.com',
                        'api_version' => 'v1',
                        'api_mode' => 'live',
                        'api_sandbox_url' => 'https://api.sandbox.paypal.com',
                        'api_sandbox_version' => 'v1',
                        'api_sandbox_mode' => 'sandbox',
                        'api_sandbox_credentials' => json_encode([
                            'api_key' => '1234567890',
                            'api_secret' => '1234567890',
                        ]),
                    ]),
                    'is_active' => true,
                ],
                [
                    "name" => "Bitcoin Payment",
                    "slug" => "BTC",
                    "description" => "Traditional payment is a payment gateway that allows you to accept payments offline.",
                    "mode" => "live",
                    "type" => "crypto",
                    "is_traditional" => true,
                    "instructions" => json_encode([
                        "title" => "Please send your payment to the following address: <address>",
                        "address" => "1A1zP1eP5QGefi2DMPTfTL5SLmv7DivfNa",
                        "network" => "bitcoin",
                        "price" => 10900.00,
                        "min_amount" => 0.0001,
                        "max_amount" => 100,
                        "fee" => 0.01,
                    ]),
                    "supported_currencies" => json_encode([
                        "BTC",
                    ]),
                    "credentials" => json_encode([]),
                    "is_active" => true,
                ],
                [
                    "name" => "Ethereum Payment",
                    "slug" => "ETH",
                    "description" => "Ethereum is a payment gateway that allows you to accept payments online.",
                    "mode" => "live",
                    "type" => "crypto",
                    "is_traditional" => true,
                    "instructions" => json_encode([
                        "title" => "Please send your payment to the following address: <address>",
                        "address" => "0x0000000000000000000000000000000000000000",
                        "network" => "ethereum",
                        "price" => 4000.00,
                        "min_amount" => 0.0001,
                        "max_amount" => 100,
                        "fee" => 0.01,
                    ]),
                    "supported_currencies" => json_encode([
                        "ETH",
                    ]),
                    "credentials" => json_encode([]),
                    "is_active" => true,
                ],
                [
                    "name" => "Tether Payment",
                    "slug" => "USDT",
                    "description" => "Tether is a payment gateway that allows you to accept payments online.",
                    "mode" => "live",
                    "type" => "crypto",
                    "is_traditional" => true,
                    "instructions" => json_encode([
                        "title" => "Please send your payment to the following address: <address>",
                        "address" => "0x0000000000000000000000000000000000000000",
                        "network" => "tether",
                        "price" => 1.00,
                        "min_amount" => 0.0001,
                        "max_amount" => 100,
                        "fee" => 0.01,
                    ]),
                    "supported_currencies" => json_encode([
                        "USDT",
                    ]),
                    "credentials" => json_encode([]),
                    "is_active" => true,
                ]
            ];

            $seededCount = 0;
            foreach ($paymentGateways as $paymentGateway) {
                PaymentGateway::create($paymentGateway);
                $seededCount++;
            }
            Log::info('[PaymentGatewaySeeder] Successfully seeded ' . $seededCount . ' payment gateways');
        } catch (\Exception $e) {
            Log::error('[PaymentGatewaySeeder] Seeding failed', [
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
