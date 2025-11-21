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
            // Expect resolved email address
            $to = $data['to'] ?? $data['email'] ?? null;
            if (!$to) {
                return [
                    'success' => false,
                    'message' => 'Recipient email address is required',
                ];
            }

            // Build SendGrid Mail object
            $email = new SendGridMail();

            // Set from (use from_email from preparedData, fallback to from, then config)
            $from = $data['from_email'] ?? $data['from'] ?? $this->fromAddress;
            $fromName = $data['from_name'] ?? $this->fromName;
            $email->setFrom($from, $fromName);

            // Set recipient and subject
            $email->addTo($to);
            $email->setSubject($data['subject'] ?? 'Notification');

            // Expect pre-rendered content
            if (!empty($data['html'])) {
                $email->addContent("text/html", $data['html']);
            }

            if (!empty($data['text'])) {
                $email->addContent("text/plain", $data['text']);
            } elseif (!empty($data['html'])) {
                // Fallback: generate plain text from HTML if only HTML provided
                $email->addContent("text/plain", strip_tags($data['html']));
            } else {
                // Last resort: use body/message if provided
                $body = $data['body'] ?? $data['message'] ?? 'Notification';
                $email->addContent("text/plain", $body);
            }

            // Send via SendGrid API
            $response = $this->sendGrid->send($email);

            if ($response->statusCode() >= 200 && $response->statusCode() < 300) {
                return [
                    'success' => true,
                    'provider' => 'sendgrid-api',
                    'message' => 'Email sent successfully via SendGrid HTTP API',
                    'status_code' => $response->statusCode(),
                ];
            }

            return [
                'success' => false,
                'message' => 'SendGrid API error: ' . $response->statusCode() . ' - ' . $response->body(),
                'status_code' => $response->statusCode(),
            ];
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
}

