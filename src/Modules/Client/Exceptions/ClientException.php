<?php

namespace App\Modules\Client\Exceptions;

use App\Core\Exceptions\AppException;
use Illuminate\Http\Response;

class ClientException extends AppException
{
    protected int $httpStatusCode = Response::HTTP_BAD_REQUEST;
    protected string $errorCode = 'CLIENT_ERROR';

    public function __construct(
        string $message = 'Client error occurred',
        int $httpStatusCode = 0,
        string $errorCode = '',
        array $context = [],
        int $code = 0,
        \Exception $previous = new \Exception()
    ) {
        parent::__construct($message, $httpStatusCode, $errorCode, $context, $code, $previous);
    }

}
