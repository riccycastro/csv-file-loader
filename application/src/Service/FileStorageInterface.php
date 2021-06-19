<?php

namespace App\Service;

use Exception;
use Symfony\Component\HttpFoundation\File\File;

interface FileStorageInterface
{
    /**
     * @param File $file
     * @param string $extension
     * @throws Exception
     */
    public function persist(File $file, string $extension);
}
