<?php

namespace App\Service;

use Exception;
use Iterator;
use League\Csv\AbstractCsv;
use League\Csv\Exception as LeagueCsvException;
use League\Csv\Reader;
use League\Csv\Statement;
use League\Csv\UnavailableStream;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;

class CsvFileLoader implements FileLoaderInterface
{
    /**
     * @var int
     */
    private int $fileLoadChunkSize = self::DEFAULT_FILE_CHUNK_SIZE;

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
     * @param string $fileName
     * @param string $uploadedFilePath
     * @throws Exception
     */
    public function __construct(
        string $fileName,
        int $fileLoadChunkSize,
        string $uploadedFilePath
    )
    {
        $this->fileLoadChunkSize = $fileLoadChunkSize;
        $this->uploadedFilePath = $uploadedFilePath;
        $this->headers = [];
        $this->offset = 0;
        $this->loadFile($fileName);
    }

    /**
     * @param string $fileName without extension
     * @throws Exception
     */
    public function loadFile(string $fileName): int
    {
        try {
            $this->reader = $this->createReader($fileName);
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
        $this->headers = [];
        return $this->count();
    }

    /**
     * @return int|null
     */
    public function count(): ?int
    {
        if (!isset($this->reader)) {
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
        return $this->processStatement($stmt);
    }

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void
    {
        $this->headers = $headers;
    }

    /**
     * @param string $fileName
     * @return Reader|AbstractCsv
     */
    protected function createReader(string $fileName) {
        return Reader::createFromPath($this->uploadedFilePath . $fileName);
    }

    /**
     * @param Statement $statement
     * @return Iterator
     */
    protected function processStatement(Statement $statement): Iterator
    {
        return $statement->process($this->reader)->getRecords($this->headers);
    }
}
