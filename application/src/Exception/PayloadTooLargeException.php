<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class PayloadTooLargeException extends HttpException
{
    public function __construct(string $uploadedFileSizeInMB, string $maxSizeInMB, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        $message = "Max file size supported is {$maxSizeInMB}MB, provided file contains {$uploadedFileSizeInMB}MB";
        parent::__construct(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $message, $previous, $headers, $code);
    }
}
