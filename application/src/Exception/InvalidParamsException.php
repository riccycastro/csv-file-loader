<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class InvalidParamsException extends ErrorResponseException
{
    public function __construct(array $errors, Throwable $previous = null)
    {
        $message = 'Invalid request params';
        parent::__construct(Response::HTTP_UNPROCESSABLE_ENTITY, $message, $previous);
        $this->errors = $errors;
    }
}
