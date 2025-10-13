<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class NotFoundException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_NOT_FOUND;
    protected string $errorCode = 'NOT_FOUND';

    public function __construct(
        string $message = 'Resource not found',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }

    public static function resource(string $resource = 'Resource'): self
    {
        return new self("{$resource} not found");
    }
}
