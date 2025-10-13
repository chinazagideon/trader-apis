<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class UnauthenticatedException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_UNAUTHORIZED;
    protected string $errorCode = 'UNAUTHENTICATED';

    public function __construct(
        string $message = 'Authentication required',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }

    public static function invalidToken(): self
    {
        return new self('Invalid or expired token', null, 'INVALID_TOKEN');
    }

    public static function tokenMissing(): self
    {
        return new self('Authorization token is required', null, 'TOKEN_MISSING');
    }

    public static function tokenExpired(): self
    {
        return new self('Token has expired', null, 'TOKEN_EXPIRED');
    }

    public static function invalidCredentials(): self
    {
        return new self('Invalid credentials provided', null, 'INVALID_CREDENTIALS');
    }
}
