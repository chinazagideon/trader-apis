<?php

namespace App\Core\Exceptions;

use Exception;
use Illuminate\Http\Response;

class AppException extends Exception
{
    protected int $httpStatusCode = Response::HTTP_INTERNAL_SERVER_ERROR;
    protected string $errorCode = 'APP_ERROR';
    protected array $context = [];

    public function __construct(
        string $message = 'Application error occurred',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->httpStatusCode = $httpStatusCode ?? $this->httpStatusCode;
        $this->errorCode = $errorCode ?? $this->errorCode;
        $this->context = $context;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    public function getContext(): array
    {
        return $this->context;
    }

    public function toArray(): array
    {
        return [
            'error_code' => $this->getErrorCode(),
            'message' => $this->getMessage(),
            'context' => $this->getContext(),
            'file' => $this->getFile(),
            'line' => $this->getLine(),
        ];
    }
}
