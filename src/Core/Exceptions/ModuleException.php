<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class ModuleException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_BAD_REQUEST;
    protected string $errorCode = 'MODULE_ERROR';

    public function __construct(
        string $message = 'Module error occurred',
        int $httpStatusCode = 0,
        string $errorCode = '',
        array $context = [],
        int $code = 0,
        \Exception $previous = new \Exception()
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }
}
