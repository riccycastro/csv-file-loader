<?php

namespace App\EventListener;

use App\Exception\ErrorResponseException;
use App\Response\JsonResponse;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

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

            $event->setResponse((new JsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR))
                ->setMessage('An unexpected error occurred, please try again.')
                ->setResult(JsonResponse::RESULT_ERROR)
            );
        }
    }
}
