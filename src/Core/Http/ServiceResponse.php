<?php

namespace App\Core\Http;

use App\Core\Exceptions\AppException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ServiceResponse
{
    protected bool $success;
    protected mixed $data;
    protected string $message;
    protected int $httpStatusCode;
    protected ?array $errors;
    protected ?array $pagination;
    protected ?string $errorCode;

    public function __construct(
        bool $success,
        mixed $data = null,
        string $message = '',
        int $httpStatusCode = Response::HTTP_OK,
        ?array $errors = null,
        ?array $pagination = null,
        ?string $errorCode = null
    ) {
        $this->success = $success;
        $this->data = $data;
        $this->message = $message;
        $this->httpStatusCode = $httpStatusCode;
        $this->errors = $errors;
        $this->pagination = $pagination;
        $this->errorCode = $errorCode;
    }

    public static function success(
        mixed $data = null,
        string $message = 'Success',
        int $httpStatusCode = Response::HTTP_OK,
        ?array $pagination = null
    ): self {
        return new self(true, $data, $message, $httpStatusCode, null, $pagination);
    }

    public static function error(
        string $message = 'Error',
        int $httpStatusCode = Response::HTTP_BAD_REQUEST,
        ?array $errors = null,
        ?string $errorCode = null
    ): self {
        return new self(false, null, $message, $httpStatusCode, $errors, null, $errorCode);
    }

    public static function fromException(AppException $exception): self
    {
        return new self(
            false,
            null,
            $exception->getMessage(),
            $exception->getHttpStatusCode(),
            $exception instanceof \App\Core\Exceptions\ValidationException ? $exception->getErrors() : null,
            null,
            $exception->getErrorCode()
        );
    }

    public function isSuccess(): bool
    {
        return $this->success;
    }

    public function getData(): mixed
    {
        return $this->data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getHttpStatusCode(): int
    {
        return $this->httpStatusCode;
    }

    public function getErrors(): ?array
    {
        return $this->errors;
    }

    public function getPagination(): ?array
    {
        return $this->pagination;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function setData(mixed $data): self
    {
        $this->data = $data;
        return $this;
    }

    public function toArray(): array
    {
        $response = [
            'success' => $this->success,
            'message' => $this->message,
        ];

        if ($this->data !== null) {
            $response['data'] = $this->data;
        }

        if ($this->errors !== null) {
            $response['errors'] = $this->errors;
        }

        if ($this->pagination !== null) {
            $response['pagination'] = $this->pagination;
        }

        if ($this->errorCode !== null) {
            $response['error_code'] = $this->errorCode;
        }

        return $response;
    }

    public function toJsonResponse(): JsonResponse
    {
        return response()->json($this->toArray(), $this->httpStatusCode);
    }
}
