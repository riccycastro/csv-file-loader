<?php

namespace App\Exception;

use App\Response\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

abstract class ErrorResponseException extends HttpException
{
    /**
     * @var array
     */
    protected array $errors = [];

    /**
     * @var string
     */
    private string $result = JsonResponse::RESULT_ERROR;

    /**
     * ErrorResponseException constructor.
     * @param int $statusCode
     * @param string $message
     * @param Throwable|null $previous
     */
    public function __construct(int $statusCode = 0, string $message = '', Throwable $previous = null)
    {
        parent::__construct($statusCode, $message, $previous);
    }

    /**
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * @return string
     */
    public function getResult(): string
    {
        return $this->result;
    }
}
