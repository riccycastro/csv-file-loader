<?php

namespace App\Message\Handler;

use App\Dto\UserFileLoadedDto;
use App\Message\UserFileLoaderMessage;
use App\Repository\UserRepository;
use App\Service\FileLoaderInterface;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use Exception;
use Symfony\Component\Messenger\Handler\MessageHandlerInterface;
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
     * @var FileLoaderInterface
     */
    private FileLoaderInterface $fileLoader;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * FileLoaderMessageHandler constructor.
     * @param FileLoaderInterface $fileLoader
     * @param UserRepository $userRepository
     * @param ValidatorInterface $validator
     */
    public function __construct(
        FileLoaderInterface $fileLoader,
        UserRepository $userRepository,
        ValidatorInterface $validator
    )
    {
        $this->fileLoader = $fileLoader;
        $this->userRepository = $userRepository;
        $this->validator = $validator;
    }

    /**
     * @param UserFileLoaderMessage $message
     * @throws DbalDriverException
     * @throws DbalException
     * @throws Exception
     */
    public function __invoke(UserFileLoaderMessage $message)
    {
        $this->fileLoader->setHeaders(self::USER_FILE_HEADER);

        if (!$this->fileLoader->loadFile($message->getFileName())) {
            // The file has no data to read
            return;
        }

        $userFileLoadedDtoList = [];
        while ($records = $this->fileLoader->read()) {
            foreach ($records as $record) {
                $userFileLoadedDto = new UserFileLoadedDto();

                $userFileLoadedDto->email = !empty($record['email']) ? $record['email'] : null;
                $userFileLoadedDto->firstName = !empty($record['firstname']) ? $record['firstname'] : null;
                $userFileLoadedDto->lastName = !empty($record['lastname']) ? $record['lastname'] : null;
                $userFileLoadedDto->fiscalCode = !empty($record['fiscal_code']) ? $record['fiscal_code'] : null;
                $userFileLoadedDto->description = !empty($record['description']) ? $record['description'] : null;
                $userFileLoadedDto->lastAccessDate = !empty($record['last_access_date']) ? $record['last_access_date'] : null;

                if ($this->isValidDto($userFileLoadedDto)) {
                    $userFileLoadedDtoList[] = $userFileLoadedDto;
                }
            }
        }

        $this->userRepository->insertBulk($userFileLoadedDtoList);
    }

    /**
     * @param UserFileLoadedDto $userFileLoadedDto
     * @return bool
     */
    private function isValidDto(UserFileLoadedDto $userFileLoadedDto): bool
    {
        $violations = $this->validator->validate($userFileLoadedDto);
        return count($violations) === 0;
    }
}
