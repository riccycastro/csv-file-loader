<?php

namespace App\Dto;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraints as Assert;

class UploadFileDto
{
    /**
     * @Assert\NotNull(
     *     groups={"file", "all"}
     * )
     * @Assert\File(
     *     mimeTypes={"text/csv", "text/plain"},
     *     groups={"file", "all"}
     * )
     * @var UploadedFile|null
     */
    public ?UploadedFile $file;

    /**
     * @Assert\NotBlank(
     *     groups={"all"}
     * )
     * @var string|null
     */
    public ?string $extension;

    /**
     * @Assert\NotBlank(
     *     groups={"all"}
     * )
     * @var string|null
     */
    public ?string $originalName;

    /**
     * @Assert\NotBlank(
     *     groups={"all"}
     * )
     * @var string|null
     */
    public ?string $name;
}
