<?php

namespace App\Modules\Investment\Enums;

/**
 * Investment status enum
 */
enum InvestmentStatus: string
{
    case Running = 'running';
    case Cancelled = 'cancelled';
    case Completed = 'completed';
    case Failed = 'failed';
    case Expired = 'expired';
    case Pending = 'pending';

    /**
     * Get the label for the investment status
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Running => 'Running',
            self::Cancelled => 'Cancelled',
            self::Completed => 'Completed',
            self::Failed => 'Failed',
            self::Expired => 'Expired',
            self::Pending => 'Pending',
        };
    }

    /**
     * Get the running status
     * @return self
     */
    public static function running(): self
    {
        return self::Running;
    }

    /**
     * Get the cancelled status
     * @return self
     */
    public static function cancelled(): self
    {
        return self::Cancelled;
    }

    /**
     * Get the completed status
     * @return self
     */
    public static function completed(): self
    {
        return self::Completed;
    }

    /**
     * Get the failed status
     * @return self
     */
    public static function failed(): self
    {
        return self::Failed;
    }

    /**
     * Get the expired status
     * @return self
     */
    public static function expired(): self
    {
        return self::Expired;
    }

    /**
     * Get the pending status
     * @return self
     */
    public static function pending(): self
    {
        return self::Pending;
    }

    /**
     * Get the default status
     * @return string
     */
    public static function defaultStatus(): string
    {
        return self::Running->value;
    }
}
