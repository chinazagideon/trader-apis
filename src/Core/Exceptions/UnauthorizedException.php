<?php

namespace App\Core\Exceptions;

use Illuminate\Http\Response;

class UnauthorizedException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_FORBIDDEN;
    protected string $errorCode = 'UNAUTHORIZED';

    public function __construct(
        string $message = 'Insufficient permissions',
        int $httpStatusCode = null,
        string $errorCode = null,
        array $context = [],
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }

    public static function insufficientPermissions(string $action = 'perform this action'): self
    {
        return new self("You don't have permission to {$action}", null, 'INSUFFICIENT_PERMISSIONS');
    }

    public static function accessDenied(string $resource = 'this resource'): self
    {
        return new self("Access denied to {$resource}", null, 'ACCESS_DENIED');
    }

    public static function roleRequired(string $role): self
    {
        return new self("Role '{$role}' is required", null, 'ROLE_REQUIRED');
    }
}
