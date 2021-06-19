<?php

namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\File;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;

class LocalStorageService implements FileStorageInterface
{
    /**
     * @var ParameterBagInterface
     */
    private ParameterBagInterface $params;

    /**
     * LocalStorageService constructor.
     * @param ParameterBagInterface $params
     */
    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    /**
     * @param File $file
     * @param string $extension
     * @throws Exception
     */
    public function persist(File $file, string $extension)
    {
        $filePathByExtension = $this->params->get("local_storage_{$extension}_path");

        $file->move($filePathByExtension, $file->getFilename());
    }
}
