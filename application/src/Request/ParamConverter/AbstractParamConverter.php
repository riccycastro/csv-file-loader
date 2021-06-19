<?php

namespace App\Request\ParamConverter;

use App\Exception\InvalidParamsException;
use Sensio\Bundle\FrameworkExtraBundle\Request\ParamConverter\ParamConverterInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class AbstractParamConverter implements ParamConverterInterface
{
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * UploadFileParamConverter constructor.
     * @param ValidatorInterface $validator
     */
    public function __construct(ValidatorInterface $validator)
    {
        $this->validator = $validator;
    }

    /**
     * @param object $dto
     * @param string|null $group
     */
    protected function validate(object $dto, string $group = null)
    {
        $violations = $this->validator->validate($dto, null, $group);

        if ($violations->count()) {

            $errors = [];

            /** @var ConstraintViolation $violation */
            foreach ($violations as $violation) {
                $errors[$violation->getPropertyPath()] = $violation->getMessage();
            }

            throw new InvalidParamsException($errors);
        }
    }
}
