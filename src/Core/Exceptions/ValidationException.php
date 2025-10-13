<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class ValidationException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_UNPROCESSABLE_ENTITY;
    protected string $errorCode = 'VALIDATION_ERROR';
    protected array $errors = [];

    public function __construct(
        string $message = 'Validation failed',
        array $errors = [],
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public static function fromValidator(\Illuminate\Validation\Validator $validator): self
    {
        return new self(
            'Validation failed',
            $validator->errors()->toArray(),
            Response::HTTP_UNPROCESSABLE_ENTITY,
            'VALIDATION_ERROR'
        );
    }

    public function toArray(): array
    {
        return array_merge(parent::toArray(), [
            'errors' => $this->getErrors(),
        ]);
    }
}
