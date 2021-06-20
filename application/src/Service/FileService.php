<?php

namespace App\Service;

use App\Dto\UploadFileDto;
use App\Exception\PayloadTooLargeException;
use App\Exception\UnsupportedMediaTypeException;
use App\Message\UserFileLoaderMessage;
use Exception;
use Symfony\Component\DependencyInjection\Exception\ParameterNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class FileService implements FileServiceInterface
{
    /**
     * @var int
     */
    private int $fileUploadedMaxSize;

    /**
     * @var FileStorageInterface
     */
    private FileStorageInterface $fileStorage;

    /**
     * @var MessageBusInterface
     */
    private MessageBusInterface $messageBus;

    /**
     * RequestListener constructor.
     * @param int $fileUploadedMaxSize
     * @param FileStorageInterface $fileStorage
     * @param MessageBusInterface $messageBus
     */
    public function __construct(
        int $fileUploadedMaxSize,
        FileStorageInterface $fileStorage,
        MessageBusInterface $messageBus
    )
    {
        $this->fileUploadedMaxSize = $fileUploadedMaxSize;
        $this->fileStorage = $fileStorage;
        $this->messageBus = $messageBus;
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
    public function validateMaxSize(UploadedFile $file): void
    {
        if (($fileSize = $this->bytesToMegaBytes($file->getSize())) > $this->fileUploadedMaxSize) {
            throw new PayloadTooLargeException($file->getClientOriginalName(), $fileSize, $this->fileUploadedMaxSize);
        }
    }

    /**
     * @inheritDoc
     */
    public function validateType(UploadedFile $file): void
    {
        // if the server calculate mimeType is valid we accept it as true
        // else we validate the client data by validating the client mimeType and
        // the file extension
        if (!($this->isValidMimeType($file->getMimeType()) || $this->isValidExtension($file))) {
            throw new UnsupportedMediaTypeException($file->getClientOriginalName(), $file->getMimeType());
        }
    }

    /**
     * @param UploadFileDto $uploadFileDto
     * @throws ParameterNotFoundException
     * @throws Exception
     */
    public function saveFile(UploadFileDto $uploadFileDto)
    {
        $this->fileStorage->persist($uploadFileDto->file, $uploadFileDto->extension);

        $this->messageBus->dispatch(new UserFileLoaderMessage(
            $uploadFileDto->name,
            $uploadFileDto->originalName,
            $uploadFileDto->extension
        ));
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
