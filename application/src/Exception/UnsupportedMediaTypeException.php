<?php

namespace App\Exception;

use App\Service\FileLoaderInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

class UnsupportedMediaTypeException extends HttpException
{
    public function __construct(string $uploadedFileMimeType, \Throwable $previous = null, array $headers = [], ?int $code = 0)
    {
        $message = "Supported mimetypes are [" . implode(', ', FileLoaderInterface::MIME_TYPE_ACCEPT) . "], provided mime type is $uploadedFileMimeType";
        parent::__construct(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $message, $previous, $headers, $code);
    }
}
