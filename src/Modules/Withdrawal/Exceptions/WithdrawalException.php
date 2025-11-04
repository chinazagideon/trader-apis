<?php

namespace App\Modules\Withdrawal\Exceptions;

use App\Core\Exceptions\BusinessLogicException;

class WithdrawalException extends BusinessLogicException
{
    /**
     * Constructor
     */
    public function __construct(string $message = 'Withdrawal exception occurred', int $httpStatusCode = null, string $errorCode = null, array $context = [], int $code = 0, \Exception $previous = null)
    {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }
}
