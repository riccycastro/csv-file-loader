<?php

namespace App\EventListener;

use App\Exception\ErrorResponseException;
use App\Response\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener
{
    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * ExceptionListener constructor.
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $event->allowCustomResponseCode();
        $exception = $event->getThrowable();

        if ($exception instanceof ErrorResponseException) {
            $this->logger->notice(self::class, [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString()
            ]);

            $event->setResponse((new JsonResponse($exception->getStatusCode()))
                ->setMessage($exception->getMessage())
                ->setResult($exception->getResult())
                ->setErrors($exception->getErrors())
            );
        } else {
            $this->logger->critical(self::class, [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode(),
                'trace' => $exception->getTraceAsString(),
            ]);

            $statusCode = $exception instanceof HttpException ? $exception->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR;

            $event->setResponse((new JsonResponse($statusCode))
                ->setMessage(Response::$statusTexts[$statusCode])
                ->setResult(JsonResponse::RESULT_ERROR)
            );
        }
    }
}
