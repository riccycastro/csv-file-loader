<?php

namespace App\Request\ParamConverter;

use App\Dto\UploadFileDto;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Component\HttpFoundation\Request;

class UploadFileParamConverter extends AbstractParamConverter
{
    /**
     * @inheritDoc
     */
    public function apply(Request $request, ParamConverter $configuration): bool
    {
        $uploadFileDto = new UploadFileDto();
        $uploadFileDto->file = $request->files->get('file');

        $this->validate($uploadFileDto);

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
