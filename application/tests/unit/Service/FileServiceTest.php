<?php

namespace App\Tests\unit\Service;

use App\Exception\PayloadTooLargeException;
use App\Exception\UnsupportedMediaTypeException;
use App\Service\FileService;
use App\Service\FileStorageInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Messenger\MessageBusInterface;

class FileServiceTest extends TestCase
{
    /**
     * @var FileService
     */
    private FileService $fileService;

    /**
     * @var FileStorageInterface
     */
    private $fileStorage;

    /**
     * @var MessageBusInterface
     */
    private $messageBus;

    public function testBytesToMegaBytes()
    {
        $result = $this->fileService->bytesToMegaBytes(255);
        $this->assertEquals(0, $result);

        $result = $this->fileService->bytesToMegaBytes(255, 10);
        $this->assertEquals(0.000243187, $result);

        $result = $this->fileService->bytesToMegaBytes(1255000);
        $this->assertEquals(1.2, $result);
    }

    public function testGetUploadedFileExtensionShouldReturnFast()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtension', 'getClientOriginalName'])
            ->getMock();

        $uploadedFile
            ->expects($this->exactly(2))
            ->method('getExtension')
            ->willReturn('csv');

        $uploadedFile
            ->expects($this->never())
            ->method('getClientOriginalName');

        $result = $this->fileService->getUploadedFileExtension($uploadedFile);

        $this->assertSame('csv', $result);
    }

    public function tesGetUploadedFileExtensionShouldRetrieveExtensionFromFileName()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtension', 'getClientOriginalName'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getExtension')
            ->willReturn('');

        $uploadedFile
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename.csv');

        $result = $this->fileService->getUploadedFileExtension($uploadedFile);
        $this->assertSame('csv', $result);
    }

    public function testGetUploadedFileExtensionShouldReturnNull()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getExtension', 'getClientOriginalName'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getExtension')
            ->willReturn('');

        $uploadedFile
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename');

        $result = $this->fileService->getUploadedFileExtension($uploadedFile);
        $this->assertNull($result);
    }

    public function testValidateMaxSizeShouldFailOnMaxSizeExceeded()
    {
        $this->expectException(PayloadTooLargeException::class);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSize', 'getClientOriginalName'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(1255000);

        $uploadedFile
            ->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn('filename.csv');

        $this->fileService->validateMaxSize($uploadedFile);
    }

    public function testValidateMaxSizeShouldPass()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getSize', 'getClientOriginalName'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getSize')
            ->willReturn(252);

        $uploadedFile
            ->expects($this->never())
            ->method('getClientOriginalName');

        $this->fileService->validateMaxSize($uploadedFile);
    }

    public function testValidateTypeShouldFailOnInvalidMimeType()
    {
        $this->expectException(UnsupportedMediaTypeException::class);

        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMimeType', 'getClientOriginalName', 'getExtension', 'getClientMimeType'])
            ->getMock();

        $uploadedFile
            ->method('getMimeType')
            ->willReturn('application/json');

        $uploadedFile
            ->method('getClientOriginalName')
            ->willReturn('fileName');

        $uploadedFile
            ->method('getExtension')
            ->willReturn('');

        $uploadedFile
            ->method('getClientMimeType')
            ->willReturn('');

        $this->fileService->validateType($uploadedFile);
    }

    public function testValidateTypeShouldAcceptServerCalculatedMimeTypeAsSourceOfTrue()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMimeType', 'getClientOriginalName', 'getExtension', 'getClientMimeType'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('text/csv');

        $uploadedFile
            ->expects($this->never())
            ->method('getClientOriginalName');

        $uploadedFile
            ->expects($this->never())
            ->method('getExtension');

        $uploadedFile
            ->expects($this->never())
            ->method('getClientMimeType');

        $this->fileService->validateType($uploadedFile);
    }

    public function testValidateTypeShouldValidateByExtension()
    {
        $uploadedFile = $this->getMockBuilder(UploadedFile::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getMimeType', 'getClientOriginalName', 'getExtension', 'getClientMimeType'])
            ->getMock();

        $uploadedFile
            ->expects($this->once())
            ->method('getMimeType')
            ->willReturn('');

        $uploadedFile
            ->method('getClientMimeType')
            ->willReturn('text/csv');

        $uploadedFile
            ->method('getExtension')
            ->willReturn('csv');

        $this->fileService->validateType($uploadedFile);
    }

    protected function setUp(): void
    {
        $this->fileStorage = $this->getMockForAbstractClass(FileStorageInterface::class);
        $this->messageBus = $this->getMockForAbstractClass(MessageBusInterface::class);

        $this->fileService = new FileService(
            1,
            $this->fileStorage,
            $this->messageBus
        );
    }
}
