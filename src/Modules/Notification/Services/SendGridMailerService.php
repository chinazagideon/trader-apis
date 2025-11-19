<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\ProviderInterface;
use Illuminate\Support\Facades\Log;
use SendGrid\Mail\Mail as SendGridMail;

class SendGridMailerService implements ProviderInterface
{
    protected ?\SendGrid $sendGrid = null;
    protected string $apiKey;
    protected string $fromAddress;
    protected string $fromName;

    public function __construct(?string $apiKey = null, ?string $fromAddress = null, ?string $fromName = null)
    {
        $this->apiKey = $apiKey ?? config('notification.email_providers.sendgrid.api_key') ?? config('mail.mailers.smtp.password');
        $this->fromAddress = $fromAddress ?? config('mail.from.address');
        $this->fromName = $fromName ?? config('mail.from.name');

        if ($this->apiKey) {
            $this->sendGrid = new \SendGrid($this->apiKey);
        }
    }

    /**
     * Send email via SendGrid HTTP API
     *
     * @param mixed $notifiable
     * @param array $data
     * @return array
     */
    public function send($notifiable, array $data): array
    {

        try {
            if (!$this->isAvailable()) {
                return [
                    'success' => false,
                    'message' => 'SendGrid is not configured or available',
                ];
            }

            Log::info('SendGridMailerService send', [
                'notifiable' => $notifiable,
                'data' => $data,
            ]);

            $email = new SendGridMail();
            $getNameFromData = $this->getFromName($data);

            // Set from address
            $email->setFrom($this->fromAddress, $getNameFromData ?? $this->fromName);

            // Get recipient email
            $to = $data['email'] ?? ($notifiable->email ?? null);
            if (!$to) {
                return [
                    'success' => false,
                    'message' => 'No email address found for notifiable',
                ];
            }

            $email->addTo($to);
            $email->setSubject($data['subject'] ?? 'Notification');

            // Handle view-based emails (HTML)
            if (isset($data['view']) && isset($data['viewData'])) {
                $htmlContent = view($data['view'], $data['viewData'])->render();
                $email->addContent("text/html", $htmlContent);

                // Also add plain text version for better deliverability
                $plainText = strip_tags($htmlContent);
                $email->addContent("text/plain", $plainText);
            } else {
                // Plain text fallback
                $body = $data['body'] ?? $data['message'] ?? 'Notification';
                $email->addContent("text/plain", $body);
            }

            // Send via HTTP API (HTTPS - port 443)
            $response = $this->sendGrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return [
                    'success' => true,
                    'provider' => 'sendgrid-api',
                    'message' => 'Email sent successfully via SendGrid HTTP API',
                    'status_code' => $response->statusCode(),
                ];
            } else {
                return [
                    'success' => false,
                    'message' => 'SendGrid API error: ' . $response->statusCode() . ' - ' . $response->body(),
                    'status_code' => $response->statusCode(),
                ];
            }
        } catch (\Exception $e) {
            Log::error('SendGrid email sending failed', [
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
     * Check if SendGrid is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->sendGrid !== null && !empty($this->apiKey);
    }

    /**
     * Get provider health status
     *
     * @return array
     */
    public function getHealth(): array
    {
        if (!$this->isAvailable()) {
            return [
                'status' => 'down',
                'message' => 'SendGrid API key not configured',
            ];
        }

        // Could add actual health check here (ping SendGrid API)
        return [
            'status' => 'healthy',
            'message' => 'SendGrid is configured and ready',
        ];
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'sendgrid';
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

    /**
     * Get the from name
     *
     * @param array $data
     * @return string|null
     */
    private function getFromName(array $data): ?string
    {
        if (isset($data['notifiable_client_name'])) {
            return ucwords($data['notifiable_client_name']);
        }
        return null;
    }
}

