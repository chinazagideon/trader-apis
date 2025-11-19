<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\ProviderInterface;
use App\Modules\Notification\Database\Models\NotificationConfig;
use App\Modules\Notification\Services\SendGridMailerService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Http;
use App\Modules\Notification\Contracts\ProviderResolverInterface;

class ProviderManager
{
    protected array $providers = [];
    protected array $healthStatus = [];

    /**
     * ProviderManager constructor
     *
     * @param ProviderResolver|null $providerResolver
     */
    public function __construct(
        private ?ProviderResolverInterface $providerResolver = null
    ) {}

    /**
     * Get active providers for a channel with failover support
     *
     * @param string $channel
     * @return array
     */
    public function getProvidersForChannel(string $channel): array
    {
        // Try to load from database first
        $dbProviders = $this->loadProvidersFromDatabase($channel);

        if (!empty($dbProviders)) {
            return $dbProviders;
        }

        // Fallback to config
        return $this->loadProvidersFromConfig($channel);
    }

    /**
     * Load providers from database
     *
     * @param string $channel
     * @return array
     */
    protected function loadProvidersFromDatabase(string $channel): array
    {
        $type = $this->getProviderType($channel);

        $configs = NotificationConfig::active()
            ->byType($type)
            ->orderedByPriority()
            ->get();

        return $configs->map(function ($config) {
            return [
                'name' => $config->name,
                'config' => $config->config,
                'priority' => $config->priority,
            ];
        })->toArray();
    }

    /**
     * Load providers from config file
     *
     * @param string $channel
     * @return array
     */
    protected function loadProvidersFromConfig(string $channel): array
    {
        $channelKey = $this->getChannelKey($channel);
        return config("notification.providers.{$channelKey}", []);
    }

    /**
     * Send using provider with failover
     *
     * @param string $channel
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    public function sendWithFailover(string $channel, $notifiable, array $data): array
    {
        $providers = $this->getProvidersForChannel($channel);
        $maxRetries = config('notification.failover.max_retries', 3);
        $retryDelay = config('notification.failover.retry_delay', 5);
        $failedProviders = [];

        foreach ($providers as $provider) {
            $attempts = 0;

            while ($attempts < $maxRetries) {
                try {
                    $result = $this->sendViaProvider($channel, $provider, $notifiable, $data);

                    if ($result['success']) {
                        $this->logProviderSuccess($channel, $provider['name'], $data);
                        return [
                            'success' => true,
                            'provider' => $provider['name'],
                            'result' => $result,
                        ];
                    }

                    $failedProviders[] = [
                        'provider' => $provider['name'],
                        'error' => $result['message'] ?? 'Unknown error',
                        'attempt' => $attempts + 1,
                    ];

                    $attempts++;
                    if ($attempts < $maxRetries) {
                        sleep($retryDelay);
                    }
                } catch (\Exception $e) {
                    $failedProviders[] = [
                        'provider' => $provider['name'],
                        'error' => $e->getMessage(),
                        'attempt' => $attempts + 1,
                    ];

                    $attempts++;
                    if ($attempts < $maxRetries) {
                        sleep($retryDelay);
                    }
                }
            }
        }

        $this->logProviderFailure($channel, $failedProviders, $data);

        return [
            'success' => false,
            'failed_providers' => $failedProviders,
            'message' => 'All providers failed',
        ];
    }

    /**
     * Send via specific provider
     *
     * @param string $channel
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendViaProvider(string $channel, array $provider, $notifiable, array $data): array
    {
        switch ($channel) {
            case 'mail':
                return $this->sendEmail($provider, $notifiable, $data);
            case 'sms':
                return $this->sendSms($provider, $notifiable, $data);
            case 'push':
                return $this->sendPush($provider, $notifiable, $data);
            case 'slack':
                return $this->sendSlack($provider, $notifiable, $data);
            default:
                return ['success' => false, 'message' => 'Unknown channel'];
        }
    }

    /**
     * Send email
     *
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendEmail(array $provider, $notifiable, array $data): array
    {
        try {
            $providerName = $provider['name'] ?? '';
            $providerConfig = $provider['config'] ?? [];

            // Try to resolve provider instance
            $providerInstance = $this->providerResolver->resolve($providerName, $providerConfig, 'mail');

            if ($providerInstance && $providerInstance->isAvailable()) {
                return $providerInstance->send($notifiable, $data);
            }

            // Fallback to Laravel's Mail facade for standard providers
            return $this->sendViaLaravelMail($provider, $notifiable, $data);
        } catch (\Exception $e) {
            Log::error('Email sending failed', [
                'provider' => $provider['name'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Send email via Laravel's Mail facade (for SMTP, log, etc.)
     *
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendViaLaravelMail(array $provider, $notifiable, array $data): array
    {
        $mailer = $provider['config']['mailer'] ?? $provider['name'];

        if (isset($data['view']) && isset($data['viewData'])) {
            Mail::mailer($mailer)->send(
                $data['view'],
                $data['viewData'],
                function ($message) use ($notifiable, $data) {
                    $to = $data['email'] ?? ($notifiable->email ?? null);
                    if ($to) {
                        $message->to($to)
                            ->subject($data['subject'] ?? 'Notification');
                    }
                }
            );
        } else {
            Mail::mailer($mailer)->send(
                $data['view'] ?? 'emails.notification',
                $data,
                function ($message) use ($notifiable, $data) {
                    $to = $data['email'] ?? ($notifiable->email ?? null);
                    if ($to) {
                        $message->to($to)
                            ->subject($data['subject'] ?? 'Notification');
                    }
                }
            );
        }

        return ['success' => true, 'message' => 'Email sent successfully'];
    }

    /**
     * Send SMS
     *
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendSms(array $provider, $notifiable, array $data): array
    {
        try {
            $phone = $data['phone'] ?? ($notifiable->phone ?? null);
            $message = $data['message'] ?? $data['body'] ?? '';

            if (!$phone) {
                return ['success' => false, 'message' => 'Phone number not provided'];
            }

            // Implementation depends on provider
            switch ($provider['name']) {
                case 'twilio':
                    return $this->sendViaTwilio($provider, $phone, $message);
                case 'vonage':
                    return $this->sendViaVonage($provider, $phone, $message);
                case 'log':
                    Log::info("SMS Notification", ['phone' => $phone, 'message' => $message]);
                    return ['success' => true, 'message' => 'Logged SMS'];
                default:
                    return ['success' => false, 'message' => 'Unknown SMS provider'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send push notification
     *
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendPush(array $provider, $notifiable, array $data): array
    {
        try {
            $deviceToken = $data['device_token'] ?? ($notifiable->device_token ?? null);

            if (!$deviceToken) {
                return ['success' => false, 'message' => 'Device token not provided'];
            }

            switch ($provider['name']) {
                case 'firebase':
                    return $this->sendViaFirebase($provider, $deviceToken, $data);
                case 'onesignal':
                    return $this->sendViaOneSignal($provider, $deviceToken, $data);
                case 'log':
                    Log::info("Push Notification", ['token' => $deviceToken, 'data' => $data]);
                    return ['success' => true, 'message' => 'Logged push notification'];
                default:
                    return ['success' => false, 'message' => 'Unknown push provider'];
            }
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send Slack notification
     *
     * @param array $provider
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    protected function sendSlack(array $provider, $notifiable, array $data): array
    {
        try {
            $webhookUrl = $provider['config']['url'] ?? config('notification.slack_providers.webhook.url');

            if (!$webhookUrl) {
                return ['success' => false, 'message' => 'Slack webhook URL not configured'];
            }

            $response = Http::post($webhookUrl, [
                'text' => $data['message'] ?? $data['body'] ?? '',
                'blocks' => $data['blocks'] ?? null,
            ]);

            if ($response->successful()) {
                return ['success' => true, 'message' => 'Slack notification sent'];
            }

            return ['success' => false, 'message' => 'Slack API error: ' . $response->body()];
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Send via Twilio
     */
    protected function sendViaTwilio(array $provider, string $phone, string $message): array
    {
        // Placeholder - implement actual Twilio integration
        Log::info("Twilio SMS", ['phone' => $phone, 'message' => $message]);
        return ['success' => true, 'message' => 'SMS sent via Twilio (simulated)'];
    }

    /**
     * Send via Vonage
     */
    protected function sendViaVonage(array $provider, string $phone, string $message): array
    {
        // Placeholder - implement actual Vonage integration
        Log::info("Vonage SMS", ['phone' => $phone, 'message' => $message]);
        return ['success' => true, 'message' => 'SMS sent via Vonage (simulated)'];
    }

    /**
     * Send via Firebase
     */
    protected function sendViaFirebase(array $provider, string $token, array $data): array
    {
        // Placeholder - implement actual Firebase integration
        Log::info("Firebase Push", ['token' => $token, 'data' => $data]);
        return ['success' => true, 'message' => 'Push sent via Firebase (simulated)'];
    }

    /**
     * Send via OneSignal
     */
    protected function sendViaOneSignal(array $provider, string $token, array $data): array
    {
        // Placeholder - implement actual OneSignal integration
        Log::info("OneSignal Push", ['token' => $token, 'data' => $data]);
        return ['success' => true, 'message' => 'Push sent via OneSignal (simulated)'];
    }

    /**
     * Get provider type from channel
     */
    protected function getProviderType(string $channel): string
    {
        return match ($channel) {
            'mail' => 'email_provider',
            'sms' => 'sms_provider',
            'push' => 'push_provider',
            'slack' => 'slack_provider',
            default => $channel . '_provider',
        };
    }

    /**
     * Get channel key for config
     */
    protected function getChannelKey(string $channel): string
    {
        return match ($channel) {
            'mail' => 'email',
            default => $channel,
        };
    }

    /**
     * Log provider success
     */
    protected function logProviderSuccess(string $channel, string $provider, array $data): void
    {
        if (config('notification.logging.log_sent', true)) {
            Log::info("Notification sent successfully", [
                'channel' => $channel,
                'provider' => $provider,
                'data' => $data,
            ]);
        }
    }

    /**
     * Log provider failure
     */
    protected function logProviderFailure(string $channel, array $failedProviders, array $data): void
    {
        if (config('notification.logging.log_failed', true)) {
            Log::error("All notification providers failed", [
                'channel' => $channel,
                'failed_providers' => $failedProviders,
                'data' => $data,
            ]);
        }
    }
}
