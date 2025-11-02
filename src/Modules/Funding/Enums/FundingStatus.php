<?php

namespace App\Modules\Funding\Enums;

/**
 * Funding status enum
 */
enum FundingStatus: string
{
    case Pending = 'pending';
    case Successful = 'successful';
    case Initiated = 'initiated';

    /**
     * Get the label for the funding status
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::Pending => 'Pending',
            self::Successful => 'Successful',
            self::Initiated => 'Initiated',
            default => 'Unknown',
        };
    }

    /**
     * get default status
     *
     * @return string
     */
    public static function getDefaultStatus(): string{
        return self::Pending->value;
    }



}
