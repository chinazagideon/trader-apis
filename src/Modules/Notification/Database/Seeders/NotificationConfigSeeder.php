<?php

namespace App\Modules\Notification\Database\Seeders;

use App\Modules\Notification\Database\Models\NotificationConfig;
use Illuminate\Database\Seeder;

class NotificationConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $configs = [
            // Email Providers
            [
                'type' => 'email_provider',
                'name' => 'sendgrid',
                'channel' => 'mail',
                'config' => [
                    'api_key' => env('SENDGRID_API_KEY', env('MAIL_PASSWORD')),
                    'from_address' => env('MAIL_FROM_ADDRESS'),
                    'from_name' => env('MAIL_FROM_NAME'),
                    'driver' => 'sendgrid',
                ],
                'priority' => 1,
                'is_active' => true,
                'description' => 'SendGrid HTTP API email provider',
            ],
            [
                'type' => 'email_provider',
                'name' => 'smtp',
                'channel' => 'mail',
                'config' => [
                    'host' => env('MAIL_HOST', 'smtp.mailtrap.io'),
                    'port' => env('MAIL_PORT', 2525),
                    'username' => env('MAIL_USERNAME'),
                    'password' => env('MAIL_PASSWORD'),
                    'encryption' => env('MAIL_ENCRYPTION', 'tls'),
                    'mailer' => 'smtp',
                ],
                'priority' => 2,
                'is_active' => true,
                'description' => 'SMTP email provider',
            ],
            [
                'type' => 'email_provider',
                'name' => 'log',
                'channel' => 'mail',
                'config' => [
                    'mailer' => 'log',
                ],
                'priority' => 99,
                'is_active' => true,
                'description' => 'Log email provider (fallback)',
            ],

            // SMS Providers
            [
                'type' => 'sms_provider',
                'name' => 'twilio',
                'channel' => 'sms',
                'config' => [
                    'sid' => env('TWILIO_SID'),
                    'token' => env('TWILIO_TOKEN'),
                    'from' => env('TWILIO_FROM'),
                ],
                'priority' => 1,
                'is_active' => false, // Disabled by default, enable when configured
                'description' => 'Twilio SMS provider',
            ],
            [
                'type' => 'sms_provider',
                'name' => 'log',
                'channel' => 'sms',
                'config' => [],
                'priority' => 99,
                'is_active' => true,
                'description' => 'Log SMS provider (fallback)',
            ],

            // Push Notification Providers
            [
                'type' => 'push_provider',
                'name' => 'firebase',
                'channel' => 'push',
                'config' => [
                    'credentials' => env('FIREBASE_CREDENTIALS'),
                    'project_id' => env('FIREBASE_PROJECT_ID'),
                ],
                'priority' => 1,
                'is_active' => false, // Disabled by default
                'description' => 'Firebase Cloud Messaging provider',
            ],
            [
                'type' => 'push_provider',
                'name' => 'log',
                'channel' => 'push',
                'config' => [],
                'priority' => 99,
                'is_active' => true,
                'description' => 'Log push provider (fallback)',
            ],

            // Slack Providers
            [
                'type' => 'slack_provider',
                'name' => 'webhook',
                'channel' => 'slack',
                'config' => [
                    'url' => env('SLACK_WEBHOOK_URL'),
                ],
                'priority' => 1,
                'is_active' => false, // Disabled by default
                'description' => 'Slack webhook provider',
            ],

            // Templates
            [
                'type' => 'template',
                'name' => 'investment_created',
                'channel' => null,
                'config' => [
                    'subject' => 'Investment Created Successfully',
                    'body' => 'Your investment #:id has been created successfully. Amount: :amount',
                    'sms_body' => 'Investment #:id created. Amount: :amount',
                ],
                'priority' => 0,
                'is_active' => true,
                'description' => 'Investment created notification template',
            ],
            [
                'type' => 'template',
                'name' => 'transaction_was_created',
                'channel' => null,
                'config' => [
                    'subject' => 'Transaction Completed',
                    'body' => 'Your transaction #:id has been completed successfully. Type: :type',
                    'sms_body' => 'Transaction #:id completed',
                ],
                'priority' => 0,
                'is_active' => true,
                'description' => 'Transaction completed notification template',
            ],
            [
                'type' => 'template',
                'name' => 'payment_received',
                'channel' => null,
                'config' => [
                    'subject' => 'Payment Received',
                    'body' => 'We have received your payment of :amount. Payment ID: :id',
                    'sms_body' => 'Payment received: :amount',
                ],
                'priority' => 0,
                'is_active' => true,
                'description' => 'Payment received notification template',
            ],
            [
                'type' => 'template',
                'name' => 'password_reset_requested',
                'channel' => 'mail',
                'config' => [
                    'subject' => 'Password Reset Requested',
                    'body' => 'You have requested to reset your password. Use the code below to reset your password.',
                    'sms_body' => 'You have requested to reset your password. Use the code below to reset your password. :code',
                ],
                'priority' => 0,
                'is_active' => true,
                'description' => 'Password reset requested notification template',
            ],
        ];

        foreach ($configs as $config) {
            NotificationConfig::updateOrCreate(
                [
                    'type' => $config['type'],
                    'name' => $config['name'],
                ],
                $config
            );
        }

        $this->command?->info('Notification configurations seeded successfully.');
    }
}

