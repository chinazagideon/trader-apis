<?php

namespace App\Modules\Auth\Events;

use App\Core\Events\BaseNotificationEvent;
use App\Modules\User\Database\Models\User;
use App\Modules\Auth\Database\Models\PasswordReset;
use Illuminate\Contracts\Events\ShouldDispatchAfterCommit;

class PasswordResetRequestedEvent extends BaseNotificationEvent implements ShouldDispatchAfterCommit
{
    public function __construct(
        public User $user,
        public PasswordReset $passwordReset,
        public string $plainToken
    ) {}

    /**
     * Get the entity
     *
     * @return PasswordReset
     */
    public function getEntity(): PasswordReset
    {
        return $this->passwordReset;
    }

    /**
     * Get the notifiable
     *
     * @return User
     */
    public function getNotifiable(): User
    {
        return $this->user;
    }

    /**
     * Get the event type
     *
     * @return string
     */
    public function getEventType(): string
    {
        return 'password_reset_requested';
    }

    /**
     * Get the channels
     *
     * @return array
     */
    public function getChannels(): array
    {
        return ['mail']; // Only email for password reset
    }

    /**
     * Get the title
     *
     * @return string
     */
    public function getTitle(): string
    {
        return 'Password Reset Requested';
    }

    /**
     * Get the message
     *
     * @return string
     */
    public function getMessage(): string
    {
        return 'You have requested to reset your password. Use the code below to reset your password.';
    }

    /**
     * Get the action url
     *
     * @return string
     */
    public function getActionUrl(): string
    {
        // Frontend reset password page with token
        return config('app.frontend_url') . '/reset-password?token=' . $this->plainToken . '&email=' . urlencode($this->user->email);
    }

    /**
     * Get the action text
     *
     * @return string
     */
    public function getActionText(): string
    {
        return 'Reset Password';
    }

    /**
     * Get the metadata
     *
     * @return array
     */
    public function getMetadata(): array
    {
        return [
            'token' => $this->plainToken,
            'expires_at' => $this->passwordReset->expires_at?->toIso8601String(),
            'user_name' => $this->user->name ?? $this->user->email,
            'reset_url' => $this->getActionUrl(),
        ];
    }
}
