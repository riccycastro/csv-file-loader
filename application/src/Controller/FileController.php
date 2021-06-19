<?php

namespace App\Controller;

use App\Dto\UploadFileDto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class FileController extends AbstractController
{
    /**
     * @ParamConverter("uploadFileDto")
     * @Route("/files", name="file", methods={"POST"})
     */
    public function index(UploadFileDto $uploadFileDto): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/FileController.php',
        ]);
    }
}
