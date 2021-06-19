<?php

namespace App\Exception;

use App\Service\FileLoaderInterface;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class UnsupportedMediaTypeException extends ErrorResponseException
{
    public function __construct(string $uploadedFileName, string $uploadedFileMimeType, Throwable $previous = null)
    {
        $message = 'Unsupported Media Type';
        parent::__construct(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $previous);

        $this->errors[$uploadedFileName] = "Supported mimetypes are [" . implode(', ', FileLoaderInterface::MIME_TYPE_SUPPORTED) . "], provided mime type is $uploadedFileMimeType";
    }
}
