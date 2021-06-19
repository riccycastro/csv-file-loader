<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadFileDto
{
    /**
     * @Assert\NotNull
     * @Assert\File(
     *     mimeTypes={"text/csv", "text/plain"}
     * )
     * @var UploadedFile|null
     */
    public ?UploadedFile $file;

    /**
     * @Assert\NotBlank
     * @var string|null
     */
    public ?string $extension;

    /**
     * @Assert\NotBlank
     * @var string|null
     */
    public ?string $originalName;

    /**
     * @Assert\NotBlank
     * @var string|null
     */
    public ?string $name;
}
