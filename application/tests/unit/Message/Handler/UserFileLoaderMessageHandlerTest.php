<?php

namespace App\Tests\unit\Message\Handler;

use App\Message\Handler\UserFileLoaderMessageHandler;
use App\Message\UserFileLoaderMessage;
use App\Repository\UserRepository;
use App\Service\FileLoaderFactory;
use App\Service\FileLoaderInterface;
use Doctrine\DBAL\Driver\Exception as DbalDriverException;
use Doctrine\DBAL\Exception as DbalException;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class UserFileLoaderMessageHandlerTest extends TestCase
{
    private UserFileLoaderMessageHandler $fileLoaderMessageHandler;

    /**
     * @var FileLoaderInterface|MockObject
     */
    private $fileLoader;

    /**
     * @var ValidatorInterface|MockObject
     */
    private $validator;

    /**
     * @var UserRepository|MockObject
     */
    private $userRepository;

    /**
     * @throws DbalDriverException
     * @throws DbalException
     */
    public function testInvokeShouldPass()
    {
        $this->fileLoader->method('read')->willReturn(
            $this->onConsecutiveCalls(
                //first time
                [
                    $this->getRecord(),
                    $this->getRecord()
                ],
                //next one, is empty to stop the while cycle
                []
            )
        );

        $message = new UserFileLoaderMessage('filename', 'users', 'csv');

        $violations = $this->getMockForAbstractClass(ConstraintViolationListInterface::class);

        $violations->method('count')->willReturn(0);

        $this->validator
            ->expects($this->exactly(2))
            ->method('validate')->willReturn($violations);

        $this->userRepository
            ->expects($this->exactly(1))
            ->method('insertBulk')->willReturn(1);

        ($this->fileLoaderMessageHandler)($message);
    }

    protected function setUp(): void
    {
        $fileLoaderFactory = $this->getMockBuilder(FileLoaderFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['makeFileLoader'])
            ->getMock();

        $this->fileLoader = $this->getMockForAbstractClass(FileLoaderInterface::class);

        $fileLoaderFactory->method('makeFileLoader')->willReturn($this->fileLoader);

        $this->userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['insertBulk'])
            ->getMock();

        $this->validator = $this->getMockForAbstractClass(ValidatorInterface::class);

        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->fileLoaderMessageHandler = new UserFileLoaderMessageHandler(
            $fileLoaderFactory,
            $this->userRepository,
            $this->validator,
            $logger
        );
    }

    private function getRecord(): array
    {
        return [
            'email' => 'test@test.cv',
            'firstname' => 'djon',
            'lastname' => 'da cruz',
            'fiscal_code' => '86716875545',
            'description' => 'lorem ipsum',
            'last_access_date' => '2021-05-07 14:50:20',
        ];
    }
}
