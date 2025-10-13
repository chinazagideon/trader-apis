<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class BusinessLogicException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_CONFLICT;
    protected string $errorCode = 'BUSINESS_LOGIC_ERROR';

    public function __construct(
        string $message = 'Business logic violation',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }
}
