<?php

namespace App\Service;

use Exception;
use Iterator;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\UnavailableStream;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CsvFileLoader implements FileLoaderInterface
{
    /**
     * @var int
     */
    private int $fileLoadChunkSize;

    /**
     * @var int
     */
    private int $offset;

    /**
     * @var array
     */
    private array $headers;

    /**
     * @var string
     */
    private string $uploadedFilePath;

    /**
     * @var Reader|null
     */
    private ?Reader $reader;

    /**
     * CsvFileLoader constructor.
     * @param int $fileLoadChunkSize
     * @param ParameterBagInterface $params
     */
    public function __construct(
        int $fileLoadChunkSize,
        ParameterBagInterface $params
    )
    {
        $this->fileLoadChunkSize = $fileLoadChunkSize;
        $this->uploadedFilePath = $params->get('local_storage_csv_path');
        $this->reader = null;
        $this->headers = [];
    }

    /**
     * @param string $fileName without extension
     * @throws Exception
     */
    public function loadFile(string $fileName): int
    {
        try {
            $this->reader = Reader::createFromPath($this->uploadedFilePath . $fileName);
            $this->reader->setHeaderOffset(0);
        } catch (UnavailableStream $exception) {
            throw new FileNotFoundException("File $fileName could not be found.");
        } catch (LeagueCsvException $e) {
            throw new Exception(
                $e->getMessage(),
                $e->getCode(),
                $e->getPrevious()
            );
        }

        $this->reader->skipEmptyRecords();
        $this->offset = 0;
        return $this->count();
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        if (!$this->reader) {
            return null;
        }

        return count($this->reader);
    }

    /**
     * @return array|Iterator
     * @throws Exception
     */
    public function read()
    {
        if ($this->offset > $this->count()) {
            return [];
        }

        $stmt = (new Statement())
            ->offset($this->offset)
            ->limit($this->fileLoadChunkSize);

        $this->offset += $this->fileLoadChunkSize;
        return $stmt->process($this->reader)->getRecords($this->headers);
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }
}
