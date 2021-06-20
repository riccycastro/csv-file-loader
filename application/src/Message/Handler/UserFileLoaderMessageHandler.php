<?php

namespace App\Message\Handler;

use App\Dto\UserFileLoadedDto;
use App\Message\UserFileLoaderMessage;
use App\Repository\UserRepository;
use App\Service\FileLoaderFactory;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFileLoaderMessageHandler implements MessageHandlerInterface
{
    private const USER_FILE_HEADER = [
        'email',
        'lastname',
        'firstname',
        'fiscal_code',
        'description',
        'last_access_date'
    ];

    /**
     * @var FileLoaderFactory
     */
    private FileLoaderFactory $fileLoaderFactory;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * FileLoaderMessageHandler constructor.
     * @param FileLoaderFactory $fileLoaderFactory
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     * @param LoggerInterface $logger
     */
    public function __construct(
        FileLoaderFactory $fileLoaderFactory,
        UserRepository $userRepository,
        ValidatorInterface $validator,
        LoggerInterface $logger
    )
    {
        $this->fileLoaderFactory = $fileLoaderFactory;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
        $this->logger = $logger;
    }

    /**
     * @param UserFileLoaderMessage $message
     * @throws DbalDriverException
     * @throws DbalException
     * @throws Exception
     */
    public function __invoke(UserFileLoaderMessage $message)
    {
        $fileLoader = $this->fileLoaderFactory->makeFileLoader($message->getFileName(), $message->getFileExtension());

        $fileLoader->setHeaders(self::USER_FILE_HEADER);

        $userFileLoadedDtoList = [];
        while ($records = $fileLoader->read()) {

            foreach ($records as $offset => $record) {
                $userFileLoadedDto = new UserFileLoadedDto();

                $userFileLoadedDto->email = !empty($record['email']) ? $record['email'] : null;
                $userFileLoadedDto->firstName = !empty($record['firstname']) ? $record['firstname'] : null;
                $userFileLoadedDto->lastName = !empty($record['lastname']) ? $record['lastname'] : null;
                $userFileLoadedDto->fiscalCode = !empty($record['fiscal_code']) ? $record['fiscal_code'] : null;
                $userFileLoadedDto->description = !empty($record['description']) ? $record['description'] : null;
                $userFileLoadedDto->lastAccessDate = !empty($record['last_access_date']) ? $record['last_access_date'] : null;

                $violations = $this->validateDto($userFileLoadedDto);
                if ($violations->count()) {
                    $this->logViolations($violations, $offset, $message->getFileName());
                    continue;
                }

                $userFileLoadedDtoList[] = $userFileLoadedDto;
            }
        }

        $this->userRepository->insertBulk($userFileLoadedDtoList);
    }

    /**
     * @param UserFileLoadedDto $userFileLoadedDto
     * @return ConstraintViolationListInterface
     */
    private function validateDto(UserFileLoadedDto $userFileLoadedDto): ConstraintViolationListInterface
    {
        return $this->validator->validate($userFileLoadedDto);
    }

    /**
     * @param ConstraintViolationListInterface $violations
     * @param int $offset
     * @param string $fileName
     */
    private function logViolations(ConstraintViolationListInterface $violations, int $offset, string $fileName)
    {
        $errors = [];

        /** @var ConstraintViolation $violation */
        foreach ($violations as $violation) {
            $errors[$violation->getPropertyPath()] = $violation->getMessage();
        }

        $this->logger->alert(self::class . " :: $fileName (line $offset)", $errors);
    }
}
