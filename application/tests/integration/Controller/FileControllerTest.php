<?php

namespace App\Tests\integration\Controller;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBus;

class FileControllerTest extends WebTestCase
{
    /**
     * @var KernelBrowser
     */
    private KernelBrowser $testClient;

    public function testIndexActionShouldIgnoreNotPostRequests()
    {
        $this->testClient->request(Request::METHOD_GET, '/files');
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->testClient->getResponse()->getStatusCode());

        $this->testClient->request(Request::METHOD_PUT, '/files');
        $this->assertEquals(Response::HTTP_METHOD_NOT_ALLOWED, $this->testClient->getResponse()->getStatusCode());
    }

    public function testIndexActionShouldFailIfNoFileUploaded()
    {
        $this->testClient->request(Request::METHOD_POST, '/files');;
        $this->assertEquals(Response::HTTP_UNPROCESSABLE_ENTITY, $this->testClient->getResponse()->getStatusCode());
        $response = json_decode($this->testClient->getResponse()->getContent(), true);

        $this->assertSame([
            'message' => 'Invalid request params',
            'result' => 'error',
            'errors' => [
                'file' => 'This value should not be null.'
            ]
        ], $response);
    }

    public function testIndexActionShouldFailOnWrongFileType()
    {
        $uploadedFile = new UploadedFile(__DIR__ . '/sample.txt', 'sample.txt', 'text/plain');

        $this->testClient->request(Request::METHOD_POST, '/files', [], [
            'file' => $uploadedFile
        ]);
        $this->assertEquals(Response::HTTP_UNSUPPORTED_MEDIA_TYPE, $this->testClient->getResponse()->getStatusCode());
        $response = json_decode($this->testClient->getResponse()->getContent(), true);

        $this->assertSame([
            'message' => 'Unsupported Media Type',
            'result' => 'error',
            'errors' => [
                'sample.txt' => 'Supported mimetypes are [text/csv], provided mime type is text/plain'
            ]
        ], $response);
    }

    public function testIndexActionShouldFailOnFileMaxSizeExceeded()
    {
        $uploadedFile = new UploadedFile(__DIR__ . '/users10K.csv', 'users10K.csv', 'text/csv');
        $this->testClient->request(Request::METHOD_POST, '/files', [], [
            'file' => $uploadedFile
        ]);
        $this->assertEquals(Response::HTTP_REQUEST_ENTITY_TOO_LARGE, $this->testClient->getResponse()->getStatusCode());
        $response = json_decode($this->testClient->getResponse()->getContent(), true);

        $this->assertSame([
            'message' => 'Payload too large',
            'result' => 'error',
            'errors' => [
                'users10K.csv' => 'Max file size supported is 0MB, provided file contains 1MB'
            ]
        ], $response);
    }

    public function testIndexActionShouldPass()
    {
        copy(__DIR__ . '/users20.csv', __DIR__ . '/users20-copy.csv');

        $uploadedFile = new UploadedFile(__DIR__ . '/users20-copy.csv', 'users20-copy.csv', 'text/csv');
        $this->testClient->request(Request::METHOD_POST, '/files', [], [
            'file' => $uploadedFile
        ]);
        $this->assertEquals(Response::HTTP_OK, $this->testClient->getResponse()->getStatusCode());
        $response = json_decode($this->testClient->getResponse()->getContent(), true);

        $this->assertSame([
            'message' => 'File received',
            'result' => 'ok',
            'errors' => []
        ], $response);

        $filePath = $this->testClient->getContainer()->get('kernel')->getProjectDir() . '/var/test_upload/users20-copy.csv';
        $this->assertTrue(file_exists($filePath));
        unlink($filePath);
    }

    protected function setUp(): void
    {
        $this->testClient = static::createClient();
        $this->testClient->getContainer()->set('messenger.default_bus', new MessageBus());
    }
}
