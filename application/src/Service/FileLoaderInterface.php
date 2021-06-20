<?php

namespace App\Service;

use Exception;
use Iterator;

interface FileLoaderInterface
{
    public const MIME_TYPE_CSV = 'text/csv';

    public const MIME_TYPE_SUPPORTED = [
        self::MIME_TYPE_CSV,
    ];

    public const FILE_EXTENSION_CSV = 'csv';

    public const FILE_EXTENSION_SUPPORTED = [
        self::FILE_EXTENSION_CSV,
    ];

    public const DEFAULT_FILE_CHUNK_SIZE = 500;

    /**
     * @param string $fileName without extension
     * @throws Exception
     */
    public function loadFile(string $fileName): int;

    /**
     * @return int|null
     */
    public function count(): ?int;

    /**
     * @return Iterator|array
     */
    public function read();

    /**
     * @param array $headers
     */
    public function setHeaders(array $headers): void;
}
