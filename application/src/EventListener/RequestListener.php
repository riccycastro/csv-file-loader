<?php

namespace App\EventListener;

use App\Exception\PayloadTooLargeException;
use App\Exception\UnsupportedMediaTypeException;
use App\Service\FileLoaderInterface;
use App\Service\FileService;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    /**
     * @var int
     */
    private int $fileUploadedMaxSize;

    /**
     * @var FileService
     */
    private FileService $fileService;

    /**
     * RequestListener constructor.
     * @param int $fileUploadedMaxSize
     * @param FileService $fileService
     */
    public function __construct(
        int $fileUploadedMaxSize,
        FileService $fileService
    )
    {
        $this->fileUploadedMaxSize = $fileUploadedMaxSize;
        $this->fileService = $fileService;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $request = $event->getRequest();

        if (!$request->files) {
            return;
        }

        /** @var UploadedFile $file */
        foreach ($request->files->all() as $file) {
            $this->validateMaxSize($file->getSize());
            $this->validateType($file);
        }
    }

    /**
     * @param int $fileSize
     * @throws PayloadTooLargeException
     */
    private function validateMaxSize(int $fileSize)
    {
        if (($fileSize = $this->fileService->bytesToMegaBytes($fileSize)) > $this->fileUploadedMaxSize) {
            throw new PayloadTooLargeException($fileSize, $this->fileUploadedMaxSize);
        }
    }

    /**
     * @param UploadedFile $file
     * @return void
     */
    private function validateType(UploadedFile $file)
    {
        // if the server calculated mimeType is valid we accept it as true
        // else we validate the client data by validating the client mimeType and
        // the file extension
        if (!($this->isValidMimeType($file->getMimeType()) || $this->isValidExtension($file))) {
            throw new UnsupportedMediaTypeException($file->getMimeType());
        }
    }

    /**
     * @param string $mimeType
     * @return bool
     */
    private function isValidMimeType(string $mimeType): bool
    {
        return in_array($mimeType, FileLoaderInterface::MIME_TYPE_SUPPORTED);
    }

    /**
     * @param UploadedFile $file
     * @return bool
     */
    private function isValidExtension(UploadedFile $file): bool
    {
        return $this->isValidMimeType($file->getClientMimeType()) &&
            in_array($this->fileService->getUploadedFileExtension($file), FileLoaderInterface::FILE_EXTENSION_SUPPORTED);
    }
}
