<?php

namespace App\Controller;

use App\Dto\UploadFileDto;
use App\Service\FileServiceInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileService;

    /**
     * FileController constructor.
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        FileServiceInterface $fileService
    )
    {
        $this->fileService = $fileService;
    }

    /**
     * @ParamConverter("uploadFileDto")
     * @Route("/files", name="file", methods={"POST"})
     * @throws Exception
     */
    public function index(UploadFileDto $uploadFileDto): Response
    {
        $this->fileService->saveFile($uploadFileDto);

        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FileController.php',
        ]);
    }
}
