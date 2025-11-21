<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\ProviderInterface;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class LaravelMailService implements ProviderInterface
{
    protected string $mailer;

    public function __construct(?string $mailer = null)
    {
        $this->mailer = $mailer ?? config('mail.default', 'smtp');
    }

    /**
     * Send email via Laravel's Mail facade
     *
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    public function send($notifiable, array $data): array
    {
        try {
            $to = $data['to'] ?? $data['email'] ?? ($notifiable->email ?? null);

            if (!$to) {
                return [
                    'success' => false,
                    'message' => 'Recipient email address is required',
                ];
            }

            $mailer = $data['mailer'] ?? $this->mailer;

            // Prepare view and view data
            $view = $data['view'] ?? 'emails.notification';
            $viewData = isset($data['view']) && isset($data['viewData'])
                ? $data['viewData']
                : $data;

            // Prepare message configuration values
            $messageConfig = $this->prepareMessageConfig($to, $data);

            // send
            Mail::mailer($mailer)->send(
                $view,
                $viewData,
                function ($message) use ($messageConfig) {
                    $this->configureMessage($message, $messageConfig);
                }
            );

            return [
                'success' => true,
                'provider' => 'laravel-mail',
                'message' => 'Email sent successfully via Laravel Mail',
            ];
        } catch (\Exception $e) {
            Log::error('Laravel Mail sending failed', [
                'error' => $e->getMessage(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Prepare message configuration values
     *
     * @param string $to
     * @param array $data
     * @return array
     */
    protected function prepareMessageConfig(string $to, array $data): array
    {
        return [
            'to' => $to,
            'subject' => $data['subject'] ?? 'Notification',
            'from_email' => $data['from_email'] ?? null,
            'from_name' => $data['from_name'] ?? null,
            'reply_to_email' => $data['reply_to_email'] ?? null,
            'reply_to_name' => $data['reply_to_name'] ?? null,
        ];
    }

    /**
     * Configure message with prepared values
     *
     * @param \Illuminate\Mail\Message $message
     * @param array $config
     * @return void
     */
    protected function configureMessage($message, array $config): void
    {
        $message->to($config['to'])
            ->subject($config['subject']);

        $message->when($config['from_email'], function ($msg) use ($config) {
            $msg->from($config['from_email'], $config['from_name']);
        });

        $message->when($config['reply_to_email'], function ($msg) use ($config) {
            $msg->replyTo($config['reply_to_email'], $config['reply_to_name']);
        });
    }

    /**
     * Check if Laravel Mail is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return true; // Laravel Mail is always available
    }

    /**
     * Get provider health status
     *
     * @return array
     */
    public function getHealth(): array
    {
        return [
            'status' => 'healthy',
            'message' => 'Laravel Mail is available',
            'mailer' => $this->mailer,
        ];
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'default';
    }

    /**
     * Get provider type/channel
     *
     * @return string
     */
    public function getType(): string
    {
        return 'mail';
    }
}
