<?php

namespace App\Exception;

use Symfony\Component\HttpFoundation\Response;
use Throwable;

class PayloadTooLargeException extends ErrorResponseException
{
    /**
     * PayloadTooLargeException constructor.
     * @param string $uploadedFileName
     * @param string $uploadedFileSize
     * @param string $maxSizeInMB
     * @param Throwable|null $previous
     */
    public function __construct(string $uploadedFileName, string $uploadedFileSize, string $maxSizeInMB, Throwable $previous = null)
    {
        $message = 'Payload too large';
        parent::__construct(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $message, $previous);

        $this->errors[$uploadedFileName] = "Max file size supported is {$maxSizeInMB}MB, provided file contains {$uploadedFileSize}MB";
    }
}
