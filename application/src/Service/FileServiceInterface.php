<?php

namespace App\Service;

use App\Exception\PayloadTooLargeException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

interface FileServiceInterface
{
    /**
     * @param int $bytes
     * @param int $precision
     * @return float
     */
    public function bytesToMegaBytes(int $bytes, int $precision = 1): float;

    /**
     * @param UploadedFile $uploadedFile
     * @return string|null
     */
    public function getUploadedFileExtension(UploadedFile $uploadedFile): ?string;

    /**
     * @param UploadedFile $file
     * @return void
     * @throws PayloadTooLargeException
     */
    public function validateMaxSize(UploadedFile $file): void;

    /**
     * @param UploadedFile $file
     * @return void
     */
    public function validateType(UploadedFile $file): void;
}