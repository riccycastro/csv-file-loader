<?php

namespace App\Tests\unit\EventListener;

use App\EventListener\ExceptionListener;
use App\Exception\InvalidParamsException;
use App\Response\JsonResponse;
use Exception;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Throwable;

class ExceptionListenerTest extends TestCase
{
    /**
     * @var ExceptionListener
     */
    private ExceptionListener $exceptionListener;

    public function testOnKernelExceptionShouldExpectedStatusCode()
    {
        $exceptionEvent = $this->buildExceptionEvent(new InvalidParamsException([
            'fieldName' => 'error message'
        ]));

        $this->exceptionListener->onKernelException($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_UNPROCESSABLE_ENTITY, $response->getStatusCode());
    }

    public function testOnKernelExceptionShouldInternalServerErrorOnGeneralError()
    {
        $exceptionEvent = $this->buildExceptionEvent(new Exception());

        $this->exceptionListener->onKernelException($exceptionEvent);

        $response = $exceptionEvent->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(Response::HTTP_INTERNAL_SERVER_ERROR, $response->getStatusCode());
    }

    protected function setUp(): void
    {
        $logger = $this->getMockForAbstractClass(LoggerInterface::class);

        $this->exceptionListener = new ExceptionListener($logger);
    }

    /**
     * @param Throwable $throwable
     * @return ExceptionEvent
     */
    private function buildExceptionEvent(Throwable $throwable): ExceptionEvent
    {
        $httpKernel = $this->getMockForAbstractClass(HttpKernelInterface::class);

        $request = $this->getMockBuilder(Request::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getClientIp'])
            ->getMock();

        return new ExceptionEvent($httpKernel, $request, HttpKernelInterface::MAIN_REQUEST, $throwable);
    }
}
