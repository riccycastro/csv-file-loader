<?php

namespace App\Service;

use App\Exception\PayloadTooLargeException;
use App\Exception\UnsupportedMediaTypeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService implements FileServiceInterface
{
    /**
     * @var int
     */
    private int $fileUploadedMaxSize;

    /**
     * RequestListener constructor.
     * @param int $fileUploadedMaxSize
     */
    public function __construct(
        int $fileUploadedMaxSize
    )
    {
        $this->fileUploadedMaxSize = $fileUploadedMaxSize;
    }

    /**
     * @inheritDoc
     */
    public function bytesToMegaBytes(int $bytes, int $precision = 1): float
    {
        return (float) number_format($bytes / 1048576, $precision);
    }

    /**
     * @inheritDoc
     */
    public function getUploadedFileExtension(UploadedFile $uploadedFile): ?string
    {
        if ($uploadedFile->getExtension()) {
            return $uploadedFile->getExtension();
        }

        $fileNameSections = explode('.', $uploadedFile->getClientOriginalName());

        if (count($fileNameSections) > 1) {
            return end($fileNameSections);
        }

        return null;
    }

    /**
     * @inheritDoc
     */
    public function validateMaxSize(int $fileSize): void
    {
        if (($fileSize = $this->bytesToMegaBytes($fileSize)) > $this->fileUploadedMaxSize) {
            throw new PayloadTooLargeException($fileSize, $this->fileUploadedMaxSize);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateType(UploadedFile $file): void
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
            in_array($this->getUploadedFileExtension($file), FileLoaderInterface::FILE_EXTENSION_SUPPORTED);
    }
}
