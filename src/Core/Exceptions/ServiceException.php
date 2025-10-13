<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class ServiceException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_BAD_REQUEST;
    protected string $errorCode = 'SERVICE_ERROR';

    public function __construct(
        string $message = 'Service error occurred',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }
}
