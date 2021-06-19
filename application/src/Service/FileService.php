<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileService
{
    /**
     * @param int $bytes
     * @param int $precision
     * @return float
     */
    public function bytesToMegaBytes(int $bytes, int $precision = 1): float
    {
        return (float) number_format($bytes / 1048576, $precision);
    }

    /**
     * @param UploadedFile $uploadedFile
     * @return string|null
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
}
