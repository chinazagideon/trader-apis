<?php

namespace App\Modules\Notification\Services;

use App\Modules\Notification\Contracts\ProviderInterface;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer as SymfonyMailer;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

class SMTPMailerService implements ProviderInterface
{
    protected ?MailerInterface $mailer = null;
    protected string $host;
    protected int $port;
    protected ?string $username;
    protected ?string $password;
    protected ?string $encryption;
    protected string $fromAddress;
    protected string $fromName;

    public function __construct(
        ?string $host = null,
        ?int $port = null,
        ?string $username = null,
        ?string $password = null,
        ?string $encryption = null,
        ?string $fromAddress = null,
        ?string $fromName = null
    ) {
        $smtpConfig = config('notification.email_providers.smtp', []);
        $mailConfig = config('mail.mailers.smtp', []);

        $this->host = $host ?? $smtpConfig['host'] ?? $mailConfig['host'] ?? '127.0.0.1';
        $this->port = $port ?? $smtpConfig['port'] ?? $mailConfig['port'] ?? 1025;
        $this->username = $username ?? $smtpConfig['username'] ?? $mailConfig['username'];
        $this->password = $password ?? $smtpConfig['password'] ?? $mailConfig['password'];
        $this->encryption = $encryption ?? $smtpConfig['encryption'] ?? $mailConfig['encryption'] ?? 'tls';

        $this->fromAddress = $fromAddress ?? config('mail.from.address');
        $this->fromName = $fromName ?? config('mail.from.name');

        if ($this->host && $this->port) {
            $this->initializeMailer();
        }
    }

    /**
     * Initialize Symfony Mailer with SMTP transport
     */
    protected function initializeMailer(): void
    {
        try {
            // Build DSN string for Symfony Mailer
            $dsn = $this->buildDsn();
            $transport = Transport::fromDsn($dsn);
            $this->mailer = new SymfonyMailer($transport);
        } catch (\Exception $e) {
            Log::error('SMTP mailer initialization failed', [
                'error' => $e->getMessage(),
                'host' => $this->host,
                'port' => $this->port,
            ]);
        }
    }

    /**
     * Build DSN string for Symfony Mailer
     */
    protected function buildDsn(): string
    {
        $scheme = $this->encryption === 'tls' ? 'smtp' : ($this->encryption === 'ssl' ? 'smtps' : 'smtp');
        $dsn = "{$scheme}://";

        if ($this->username && $this->password) {
            $dsn .= urlencode($this->username) . ':' . urlencode($this->password) . '@';
        }

        $dsn .= $this->host . ':' . $this->port;

        if ($this->encryption === 'tls') {
            $dsn .= '?encryption=tls';
        } elseif ($this->encryption === 'ssl') {
            $dsn .= '?encryption=ssl';
        }

        return $dsn;
    }

    /**
     * Send email via SMTP
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
                    'message' => 'SMTP is not configured or available',
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

            // Build Symfony Email
            $email = (new Email())
                ->from(sprintf('%s <%s>', $data['from_name'] ?? $this->fromName, $data['from_email'] ?? $data['from'] ?? $this->fromAddress))
                ->to($to)
                ->subject($data['subject'] ?? 'Notification');

            // Set content (expect pre-rendered)
            if (!empty($data['html'])) {
                $email->html($data['html']);

                // Add plain text version if available
                if (!empty($data['text'])) {
                    $email->text($data['text']);
                } else {
                    // Fallback: generate plain text from HTML
                    $email->text(strip_tags($data['html']));
                }
            } elseif (!empty($data['text'])) {
                $email->text($data['text']);
            } else {
                // Last resort: use body/message if provided
                $body = $data['body'] ?? $data['message'] ?? 'Notification';
                $email->text($body);
            }

            // Send via SMTP
            $this->mailer->send($email);

            return [
                'success' => true,
                'provider' => 'smtp',
                'message' => 'Email sent successfully via SMTP',
            ];
        } catch (\Exception $e) {
            Log::error('SMTP email sending failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'data' => $data,
            ]);

            return [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check if SMTP is available
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        return $this->mailer !== null && !empty($this->host) && !empty($this->port);
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
                'message' => 'SMTP is not configured',
                'host' => $this->host,
                'port' => $this->port,
            ];
        }

        return [
            'status' => 'healthy',
            'message' => 'SMTP is configured and ready',
            'host' => $this->host,
            'port' => $this->port,
            'encryption' => $this->encryption ?? 'none',
        ];
    }

    /**
     * Get provider name
     *
     * @return string
     */
    public function getName(): string
    {
        return 'smtp';
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
