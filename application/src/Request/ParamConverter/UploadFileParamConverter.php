<?php

namespace App\Request\ParamConverter;

use App\Dto\UploadFileDto;
use App\Service\FileServiceInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UploadFileParamConverter extends AbstractParamConverter
{
    /**
     * @var FileServiceInterface
     */
    private FileServiceInterface $fileService;

    /**
     * UploadFileParamConverter constructor.
     * @param ValidatorInterface $validator
     * @param FileServiceInterface $fileService
     */
    public function __construct(
        ValidatorInterface $validator,
        FileServiceInterface $fileService
    )
    {
        parent::__construct($validator);

        $this->fileService = $fileService;
    }

    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $uploadFileDto = new UploadFileDto();

        /** @var UploadedFile $file */
        $file = $request->files->get('file');
        $uploadFileDto->file = $file;
        $uploadFileDto->extension = $this->fileService->getUploadedFileExtension($file);
        $uploadFileDto->originalName = $file->getClientOriginalName();
        $uploadFileDto->name = $file->getFilename();

        $this->validate($uploadFileDto);

        $request->attributes->set($configuration->getName(), $uploadFileDto);

        return true;
    }

    /**
     * @inheritDoc
     */
    public function supports(ParamConverter $configuration): bool
    {
        return $configuration->getClass() === UploadFileDto::class;
    }
}
