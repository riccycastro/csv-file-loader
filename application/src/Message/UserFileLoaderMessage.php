<?php

namespace App\Message;

class UserFileLoaderMessage implements MessageInterface
{
    /**
     * @var null|string
     */
    private ?string $fileExtension;

    /**
     * @var null|string
     */
    private ?string $fileName;

    /**
     * @var null|string
     */
    private ?string $origFileName;

    /**
     * UserFileLoaderMessage constructor.
     * @param string $fileName
     * @param string $origFileName
     * @param string $fileExtension
     */
    public function __construct(string $fileName, string $origFileName, string $fileExtension)
    {
        $this->fileExtension = $fileExtension;
        $this->fileName = $fileName;
        $this->origFileName = $origFileName;
    }

    /**
     * @return string|null
     */
    public function getFileExtension(): ?string
    {
        return $this->fileExtension;
    }

    /**
     * @return string|null
     */
    public function getFileName(): ?string
    {
        return $this->fileName;
    }

    /**
     * @return string|null
     */
    public function getOrigFileName(): ?string
    {
        return $this->origFileName;
    }
}
