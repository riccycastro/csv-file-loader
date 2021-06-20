<?php

namespace App\Dto;
use Symfony\Component\Validator\Constraints as Assert;

class UserFileLoadedDto
{
    /**
     * @Assert\Email()
     * @var string|null
     */
    public ?string $email = null;

    /**
     * @var string|null
     */
    public ?string $lastName = null;

    /**
     * @var string|null
     */
    public ?string $firstName = null;

    /**
     * @var string|null
     */
    public ?string $fiscalCode = null;

    /**
     * @var string|null
     */
    public ?string $description = null;

    /**
     * @Assert\DateTime()
     * @var string|null
     */
    public ?string $lastAccessDate = null;

}
