<?php

namespace App\EventListener;

use App\Service\FileServiceInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Event\RequestEvent;

class RequestListener
{
    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileService;

    /**
     * RequestListener constructor.
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        FileServiceInterface $fileService
    )
    {
        $this->fileService = $fileService;
    }

    /**
     * @param RequestEvent $event
     */
    public function onKernelRequest(RequestEvent $event)
    {
        if (!$event->isMainRequest()) {
            // don't do anything if it's not the main request
            return;
        }

        $request = $event->getRequest();

        if (!$request->files) {
            return;
        }

        /** @var UploadedFile $file */
        foreach ($request->files->all() as $file) {
            $this->fileService->validateMaxSize($file);
            $this->fileService->validateType($file);
        }
    }
}
