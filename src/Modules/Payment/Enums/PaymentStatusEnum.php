<?php

namespace App\Modules\Payment\Enums;

enum PaymentStatusEnum: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case FAILED = 'failed';

    /**
     * Get the label for the payment status
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::FAILED => 'Failed',
            default => 'Unknown',
        };
    }

    /**
     * Get the default status
     * @return string
     */
    public static function defaultStatus(): string
    {
        return self::PENDING->value;
    }
}
