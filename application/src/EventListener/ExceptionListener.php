<?php

namespace App\EventListener;

use App\Exception\ErrorResponseException;
use App\Response\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;

class ExceptionListener
{
    /**
     * @param ExceptionEvent $event
     */
    public function onKernelException(ExceptionEvent $event)
    {
        $event->allowCustomResponseCode();
        $exception = $event->getThrowable();

        if ($exception instanceof ErrorResponseException) {
            $event->setResponse((new JsonResponse($exception->getStatusCode()))
                ->setMessage($exception->getMessage())
                ->setResult($exception->getResult())
                ->setErrors($exception->getErrors())
            );
        } else {
            $event->setResponse((new JsonResponse(Response::HTTP_INTERNAL_SERVER_ERROR))
                ->setMessage('An unexpected error occurred, please try again.')
                ->setResult(JsonResponse::RESULT_ERROR)
            );
        }
    }
}
