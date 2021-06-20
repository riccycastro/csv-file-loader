<?php

namespace App\Service;

use Exception;
use RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class FileLoaderFactory
{
    /**
     * @var int
     */
    private int $fileLoadChunkSize;

    private ParameterBagInterface $params;

    public function __construct(
        int $fileLoadChunkSize,
        ParameterBagInterface $params
    )
    {
        $this->fileLoadChunkSize = $fileLoadChunkSize;
        $this->params = $params;
    }

    /**
     * @param string $fileName
     * @param string $extension
     * @return FileLoaderInterface
     * @throws Exception
     */
    public function makeFileLoader(string $fileName, string $extension): FileLoaderInterface
    {
        switch ($extension) {
            case FileLoaderInterface::FILE_EXTENSION_CSV:
                return new CsvFileLoader( $fileName, $this->fileLoadChunkSize, $this->params->get('local_storage_csv_path'));
            default:
                throw new RuntimeException("$extension is not a valid extension");
        }
    }
}
